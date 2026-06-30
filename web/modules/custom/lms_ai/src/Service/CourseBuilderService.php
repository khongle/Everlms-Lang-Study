<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Service;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lms_ai\Plugin\AiProviderManager;
use Drupal\lms_course\Entity\Course;
use Psr\Log\LoggerInterface;

/**
 * Service to orchestrate AI generation of entire courses.
 */
class CourseBuilderService {

  public function __construct(
    private readonly AiProviderManager $aiProviderManager,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly QueueFactory $queueFactory,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly AccountProxyInterface $currentUser,
    private readonly LoggerInterface $logger
  ) {}

  /**
   * Generates a reviewable course draft without saving entities.
   */
  public function generateDraft(string $topic, string $targetAudience, string $tone, ?string $providerId = NULL): ?array {
    try {
      $providerId = $this->resolveProviderId($providerId);
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $ai */
      $ai = $this->aiProviderManager->createInstance($providerId);

      $prompt = sprintf(
        "Design a concise online course about '%s' for '%s' in a '%s' tone. Return only a strict JSON object. Include 2-3 sections, 2-4 lessons per section, and an optional short final quiz.",
        $topic,
        $targetAudience,
        $tone
      );

      $schema = '{
        "title": "A clear course title under 90 characters",
        "description": "A concise overview of the course benefits and outcomes.",
        "sections": [
          {
            "title": "Section title",
            "description": "One sentence describing this section.",
            "lessons": [
              {
                "title": "Lesson title",
                "type": "text",
                "duration": 10,
                "prompt_for_content": "A detailed one-sentence instruction for writing the full lesson later."
              }
            ]
          }
        ],
        "quiz": {
          "title": "Final knowledge check",
          "questions": [
            {
              "type": "single_choice",
              "question": "Question text",
              "choices": [
                {"text": "Correct answer", "correct": true},
                {"text": "Distractor answer", "correct": false}
              ],
              "explanation": "Why the correct answer is right."
            }
          ]
        }
      }';

      $structure = $ai->generateJson($prompt, $schema, ['temperature' => 0.4]);
      $draft = $this->normalizeDraft($structure);

      if (!$draft) {
        $this->logger->error('AI failed to return a valid course draft structure.');
      }

      return $draft;
    }
    catch (\Exception $e) {
      $this->logger->error('Course Builder Draft Error: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Saves a reviewed AI draft into real course/chapter/lesson/quiz entities.
   *
   * @return \Drupal\lms_course\Entity\Course|null
   *   The newly created draft course, or NULL on failure.
   */
  public function createCourseFromDraft(array $draft, string $tone, ?string $providerId = NULL): ?Course {
    $created = [];
    $queuedLessons = [];

    try {
      $providerId = $this->resolveProviderId($providerId);
      $ownerId = (int) $this->currentUser->id();

      $courseStorage = $this->entityTypeManager->getStorage('lms_course');
      $chapterStorage = $this->entityTypeManager->getStorage('lms_chapter');
      $lessonStorage = $this->entityTypeManager->getStorage('lms_lesson');

      $courseValues = [
        'name' => $this->truncate($draft['title'] ?? 'AI generated course', 255),
        'field_description' => [
          'value' => $draft['description'] ?? '',
          'format' => 'plain_text',
        ],
        'field_short_description' => [
          'value' => Unicode::truncate((string) ($draft['description'] ?? ''), 280, TRUE, TRUE),
          'format' => 'plain_text',
        ],
        'field_price_type' => 'free',
        'field_price' => 0,
        'status' => 0,
      ];

      if ($ownerId > 0) {
        $courseValues['uid'] = $ownerId;
        $courseValues['field_instructor'] = [['target_id' => $ownerId]];
      }

      /** @var \Drupal\lms_course\Entity\Course $course */
      $course = $courseStorage->create($courseValues);

      $chapterRefs = [];
      $lastChapter = NULL;
      $lessonWeight = 0;

      foreach ($draft['sections'] as $sectionWeight => $section) {
        $lessonRefs = [];

        foreach ($section['lessons'] as $lesson) {
          $lessonEntity = $lessonStorage->create([
            'name' => $this->truncate($lesson['title'], 255),
            'field_type' => $lesson['type'] ?? 'text',
            'field_content' => [
              'value' => 'AI content generation is queued. Review this lesson after the queue completes.',
              'format' => 'plain_text',
            ],
            'field_duration' => (int) ($lesson['duration'] ?? 10),
            'status' => 0,
            'weight' => $lessonWeight++,
          ]);
          $lessonEntity->save();
          $created[] = $lessonEntity;

          $lessonRefs[] = ['target_id' => $lessonEntity->id()];
          $queuedLessons[] = [
            'lesson_id' => (int) $lessonEntity->id(),
            'prompt' => $lesson['prompt_for_content'],
            'tone' => $tone,
            'provider_id' => $providerId,
          ];
        }

        $chapter = $chapterStorage->create([
          'name' => $this->truncate($section['title'], 255),
          'field_description' => [
            'value' => $section['description'] ?? '',
            'format' => 'plain_text',
          ],
          'field_lessons' => $lessonRefs,
          'weight' => $sectionWeight,
          'status' => 1,
        ]);
        $chapter->save();
        $created[] = $chapter;

        $chapterRefs[] = ['target_id' => $chapter->id()];
        $lastChapter = $chapter;
      }

      $course->set('field_chapters', $chapterRefs);
      $course->save();
      $created[] = $course;

      if (!empty($draft['quiz']['questions']) && $lastChapter instanceof ContentEntityInterface) {
        $this->createQuizDraft($draft['quiz'], $course, $lastChapter, $lessonWeight, $created);
      }

      $this->queueLessonGeneration($queuedLessons);
      $this->logger->info('Successfully scaffolded AI draft course @id', ['@id' => $course->id()]);

      return $course;
    }
    catch (\Exception $e) {
      $this->cleanupCreatedEntities($created);
      $this->logger->error('Course Builder Save Error: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Scaffolds the course structure based on a prompt.
   *
   * @return \Drupal\lms_course\Entity\Course|null
   *   The newly created course, or NULL on failure.
   */
  public function generateCourseStructure(string $topic, string $targetAudience, string $tone): ?Course {
    $draft = $this->generateDraft($topic, $targetAudience, $tone);
    return $draft ? $this->createCourseFromDraft($draft, $tone) : NULL;
  }

  /**
   * Creates an optional final quiz and attaches it as the last chapter lesson.
   */
  private function createQuizDraft(array $quizDraft, Course $course, ContentEntityInterface $lastChapter, int $lessonWeight, array &$created): void {
    $ownerId = (int) $this->currentUser->id();
    $answerStorage = $this->entityTypeManager->getStorage('lms_answer');
    $questionStorage = $this->entityTypeManager->getStorage('lms_question');
    $quizStorage = $this->entityTypeManager->getStorage('lms_quiz');
    $lessonStorage = $this->entityTypeManager->getStorage('lms_lesson');

    $questionRefs = [];
    foreach ($quizDraft['questions'] as $questionDraft) {
      $questionType = $questionDraft['type'] ?? 'single_choice';
      $questionValues = [
        'type' => $questionType,
        'name' => $this->truncate($questionDraft['question'], 1024),
        'field_body' => [
          'value' => $questionDraft['question'],
          'format' => 'plain_text',
        ],
        'field_points' => 1,
        'field_explanation' => [
          'value' => $questionDraft['explanation'] ?? '',
          'format' => 'plain_text',
        ],
      ];

      if ($questionType === 'true_false') {
        $questionValues['field_correct_answer'] = (bool) ($questionDraft['correct_answer'] ?? TRUE);
      }
      else {
        $answerRefs = [];
        foreach ($questionDraft['choices'] as $choice) {
          $answer = $answerStorage->create([
            'name' => $this->truncate($choice['text'], 1024),
            'field_correct' => (bool) ($choice['correct'] ?? FALSE),
          ]);
          $answer->save();
          $created[] = $answer;
          $answerRefs[] = ['target_id' => $answer->id()];
        }
        $questionValues['field_choices'] = $answerRefs;
      }

      $question = $questionStorage->create($questionValues);
      $question->save();
      $created[] = $question;
      $questionRefs[] = ['target_id' => $question->id()];
    }

    $quizValues = [
      'name' => $this->truncate($quizDraft['title'] ?? 'Final knowledge check', 255),
      'field_body' => [
        'value' => 'Review this generated quiz before publishing the course.',
        'format' => 'plain_text',
      ],
      'field_course' => ['target_id' => $course->id()],
      'field_questions' => $questionRefs,
      'field_threshold' => 70,
      'field_time_limit' => 0,
      'field_question_count' => 0,
      'status' => 0,
    ];

    if ($ownerId > 0) {
      $quizValues['uid'] = $ownerId;
    }

    $quiz = $quizStorage->create($quizValues);
    $quiz->save();
    $created[] = $quiz;

    $quizLesson = $lessonStorage->create([
      'name' => $this->truncate($quizDraft['title'] ?? 'Final knowledge check', 255),
      'field_type' => 'quiz',
      'field_content' => [
        'value' => 'Complete this knowledge check after reviewing the lessons.',
        'format' => 'plain_text',
      ],
      'field_quiz' => ['target_id' => $quiz->id()],
      'field_duration' => 10,
      'status' => 0,
      'weight' => $lessonWeight,
    ]);
    $quizLesson->save();
    $created[] = $quizLesson;

    $lastChapter->get('field_lessons')->appendItem(['target_id' => $quizLesson->id()]);
    $lastChapter->save();
  }

  /**
   * Queues asynchronous body generation for scaffolded text lessons.
   */
  private function queueLessonGeneration(array $queuedLessons): void {
    $queue = $this->queueFactory->get('lms_ai_content_generator');
    foreach ($queuedLessons as $queuedLesson) {
      try {
        $queue->createItem($queuedLesson);
      }
      catch (\Exception $e) {
        $this->logger->warning('Could not queue AI lesson generation for lesson @id: @message', [
          '@id' => $queuedLesson['lesson_id'] ?? 'unknown',
          '@message' => $e->getMessage(),
        ]);
      }
    }
  }

  /**
   * Resolves a provider ID from form input or config.
   */
  private function resolveProviderId(?string $providerId): string {
    $providerId = $providerId ?: $this->configFactory->get('lms_ai.settings')->get('default_provider') ?: 'openai';
    if (!isset($this->aiProviderManager->getDefinitions()[$providerId])) {
      return 'openai';
    }
    return $providerId;
  }

  /**
   * Normalizes provider JSON into the internal draft shape.
   */
  private function normalizeDraft(?array $structure): ?array {
    if (empty($structure) || empty($structure['sections']) || !is_array($structure['sections'])) {
      return NULL;
    }

    $draft = [
      'title' => $this->stringValue($structure['title'] ?? 'AI generated course'),
      'description' => $this->stringValue($structure['description'] ?? ''),
      'sections' => [],
    ];

    foreach (array_slice($structure['sections'], 0, 8) as $sectionIndex => $section) {
      if (empty($section['lessons']) || !is_array($section['lessons'])) {
        continue;
      }

      $normalizedLessons = [];
      foreach (array_slice($section['lessons'], 0, 12) as $lessonIndex => $lesson) {
        $title = $this->stringValue($lesson['title'] ?? ('Lesson ' . ($lessonIndex + 1)));
        $prompt = $this->stringValue($lesson['prompt_for_content'] ?? ('Write a complete lesson about ' . $title . '.'));
        $type = $this->normalizeLessonType($lesson['type'] ?? 'text');

        $normalizedLessons[] = [
          'title' => $title,
          'type' => $type,
          'duration' => max(1, min(240, (int) ($lesson['duration'] ?? 10))),
          'prompt_for_content' => $prompt,
        ];
      }

      if ($normalizedLessons) {
        $draft['sections'][] = [
          'title' => $this->stringValue($section['title'] ?? ('Section ' . ($sectionIndex + 1))),
          'description' => $this->stringValue($section['description'] ?? ''),
          'lessons' => $normalizedLessons,
        ];
      }
    }

    if (!$draft['sections']) {
      return NULL;
    }

    $quiz = $this->normalizeQuiz($structure['quiz'] ?? NULL);
    if ($quiz) {
      $draft['quiz'] = $quiz;
    }

    return $draft;
  }

  /**
   * Normalizes an optional generated quiz.
   */
  private function normalizeQuiz(mixed $quiz): ?array {
    if (empty($quiz['questions']) || !is_array($quiz['questions'])) {
      return NULL;
    }

    $questions = [];
    foreach (array_slice($quiz['questions'], 0, 8) as $question) {
      $type = $question['type'] ?? 'single_choice';
      $type = $type === 'true_false' ? 'true_false' : 'single_choice';
      $normalized = [
        'type' => $type,
        'question' => $this->stringValue($question['question'] ?? ''),
        'explanation' => $this->stringValue($question['explanation'] ?? ''),
      ];

      if ($normalized['question'] === '') {
        continue;
      }

      if ($type === 'true_false') {
        $normalized['correct_answer'] = (bool) ($question['correct_answer'] ?? TRUE);
      }
      else {
        $choices = [];
        foreach (array_slice($question['choices'] ?? [], 0, 6) as $choiceIndex => $choice) {
          $choiceText = $this->stringValue($choice['text'] ?? '');
          if ($choiceText === '') {
            continue;
          }
          $choices[] = [
            'text' => $choiceText,
            'correct' => (bool) ($choice['correct'] ?? $choiceIndex === 0),
          ];
        }

        if (count($choices) < 2) {
          continue;
        }

        if (!$this->hasCorrectChoice($choices)) {
          $choices[0]['correct'] = TRUE;
        }
        $normalized['choices'] = $choices;
      }

      $questions[] = $normalized;
    }

    if (!$questions) {
      return NULL;
    }

    return [
      'title' => $this->stringValue($quiz['title'] ?? 'Final knowledge check'),
      'questions' => $questions,
    ];
  }

  /**
   * Returns TRUE when at least one choice is marked correct.
   */
  private function hasCorrectChoice(array $choices): bool {
    foreach ($choices as $choice) {
      if (!empty($choice['correct'])) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Restricts lesson types to the existing lesson field values.
   */
  private function normalizeLessonType(mixed $type): string {
    $allowed = ['video', 'text', 'quiz', 'h5p', 'scorm', 'pdf', 'pptx', 'zoom', 'mixed'];
    return in_array($type, $allowed, TRUE) ? $type : 'text';
  }

  /**
   * Returns a trimmed scalar string.
   */
  private function stringValue(mixed $value): string {
    if (is_scalar($value)) {
      return trim((string) $value);
    }
    return '';
  }

  /**
   * Truncates text to a field-safe length.
   */
  private function truncate(string $value, int $length): string {
    return Unicode::truncate($value, $length, TRUE, TRUE);
  }

  /**
   * Removes already-created entities after a persistence failure.
   */
  private function cleanupCreatedEntities(array $created): void {
    foreach (array_reverse($created) as $entity) {
      if ($entity instanceof ContentEntityInterface && !$entity->isNew()) {
        try {
          $entity->delete();
        }
        catch (\Exception $cleanupException) {
          $this->logger->warning('Could not clean up AI-created entity @type:@id after failure: @message', [
            '@type' => $entity->getEntityTypeId(),
            '@id' => $entity->id(),
            '@message' => $cleanupException->getMessage(),
          ]);
        }
      }
    }
  }

}
