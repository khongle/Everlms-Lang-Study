<?php

declare(strict_types=1);

namespace Drupal\lms_content_tools\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\lms_content_tools\Service\ContentToolsManager;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for EverLMS Content Tools.
 */
class ContentToolsCommands extends DrushCommands {

  protected ContentToolsManager $manager;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected FileSystemInterface $fileSystem,
  ) {
    parent::__construct();
    $this->manager = new ContentToolsManager($entityTypeManager, $fileSystem);
  }

  /**
   * Clean all LMS content from the system.
   *
   * @param array $options
   *   Command options.
   * @option types
   *   Comma-separated list of content types to clean.
   * @option include-users
   *   Also delete users (except uid=1).
   * @option dry-run
   *   Only show counts, don't delete.
   *
   * @command everlms:clean
   * @aliases lms-clean
   * @usage everlms:clean
   *   Delete all LMS content.
   * @usage everlms:clean --types=courses,quizzes
   *   Delete only courses and quizzes.
   * @usage everlms:clean --dry-run
   *   Show what would be deleted.
   */
  public function clean(array $options = [
    'types' => NULL,
    'include-users' => FALSE,
    'dry-run' => FALSE,
  ]): void {
    $groups = $this->manager->getEntityGroups();
    $selectedGroups = array_keys($groups);

    // Filter by types if specified.
    if (!empty($options['types'])) {
      $selectedGroups = array_intersect(
        explode(',', $options['types']),
        array_keys($groups)
      );
      if (empty($selectedGroups)) {
        $this->logger()->error(dt('No valid types specified. Available: @types', [
          '@types' => implode(', ', array_keys($groups)),
        ]));
        return;
      }
    }

    // Show what will be deleted.
    $this->io()->title('EverLMS Content Clean');
    $totalCount = 0;
    $rows = [];
    foreach ($selectedGroups as $groupKey) {
      $count = $this->manager->countGroup($groupKey);
      $rows[] = [$groups[$groupKey]['label'], number_format($count)];
      $totalCount += $count;
    }

    if ($options['include-users']) {
      $userCount = max(0, $this->manager->countEntities('user') - 2);
      $rows[] = ['Users (except admin)', number_format($userCount)];
      $totalCount += $userCount;
    }

    $this->io()->table(['Content Type', 'Count'], $rows);
    $this->io()->text(dt('Total: @count entities', ['@count' => number_format($totalCount)]));

    if ($options['dry-run']) {
      $this->logger()->notice(dt('Dry run — no entities deleted.'));
      return;
    }

    if ($totalCount === 0) {
      $this->logger()->notice(dt('No entities to delete.'));
      return;
    }

    // When -y/--yes is passed, Drush auto-confirms io()->confirm() but
    // io()->ask() still blocks. Detect --yes via input option.
    $autoConfirm = $this->input()->getOption('yes');

    if (!$autoConfirm) {
      // Confirm.
      if (!$this->io()->confirm(dt('Are you sure you want to delete @count entities? This cannot be undone.', [
        '@count' => number_format($totalCount),
      ]), FALSE)) {
        $this->logger()->notice(dt('Cancelled.'));
        return;
      }

      // Second confirmation: type CLEAN.
      $input = $this->io()->ask(dt('Type CLEAN to confirm'));
      if ($input !== 'CLEAN') {
        $this->logger()->notice(dt('Confirmation failed. Cancelled.'));
        return;
      }
    }

    // Execute deletion.
    $startTime = microtime(TRUE);
    $totalDeleted = 0;

    foreach ($selectedGroups as $groupKey) {
      $results = $this->manager->deleteGroup($groupKey);
      $groupTotal = array_sum($results);
      $totalDeleted += $groupTotal;
      $this->logger()->success(dt('Deleted @count @label', [
        '@count' => $groupTotal,
        '@label' => $groups[$groupKey]['label'],
      ]));
    }

    if ($options['include-users']) {
      $userCount = $this->manager->deleteUsers(1);
      $totalDeleted += $userCount;
      $this->logger()->success(dt('Deleted @count users', ['@count' => $userCount]));
    }

    $elapsed = microtime(TRUE) - $startTime;
    $this->logger()->success(dt('Done! Deleted @count entities in @time seconds.', [
      '@count' => number_format($totalDeleted),
      '@time' => number_format($elapsed, 1),
    ]));
  }

  /**
   * Manage seed input for AI content generation.
   *
   * @param array $options
   *   Command options.
   * @option show
   *   Display current seed input.
   * @option validate
   *   Validate the seed input file.
   * @option export
   *   Output seed input JSON to stdout.
   * @option import
   *   Import seed input from a file path.
   *
   * @command everlms:seed-input
   * @aliases lms-seed
   * @usage everlms:seed-input --show
   *   Display current seed input.
   * @usage everlms:seed-input --import=/path/to/file.json
   *   Import seed input from file.
   */
  public function seedInput(array $options = [
    'show' => FALSE,
    'validate' => FALSE,
    'export' => FALSE,
    'import' => NULL,
  ]): void {
    $seedPath = $this->manager->getSeedInputPath();

    if ($options['import']) {
      $importPath = $options['import'];
      if (!file_exists($importPath)) {
        $this->logger()->error(dt('File not found: @path', ['@path' => $importPath]));
        return;
      }
      $content = file_get_contents($importPath);
      $data = json_decode($content, TRUE);
      if ($data === NULL) {
        $this->logger()->error(dt('Invalid JSON in file: @path', ['@path' => $importPath]));
        return;
      }
      if ($this->manager->saveSeedInput($data)) {
        $this->logger()->success(dt('Seed input imported from @path', ['@path' => $importPath]));
      }
      else {
        $this->logger()->error(dt('Failed to save seed input.'));
      }
      return;
    }

    $data = $this->manager->loadSeedInput();

    if ($data === NULL) {
      $this->logger()->warning(dt('No seed input file found at @path. Use the form at /workspace/content-tools/seed-input to create one.', [
        '@path' => $seedPath,
      ]));
      return;
    }

    if ($options['export']) {
      $this->io()->writeln(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
      return;
    }

    if ($options['validate']) {
      $errors = $this->validateSeedInput($data);
      if (empty($errors)) {
        $this->logger()->success(dt('Seed input is valid.'));
      }
      else {
        foreach ($errors as $error) {
          $this->logger()->error($error);
        }
      }
      return;
    }

    // Default: --show behavior.
    $this->io()->title('EverLMS Seed Input');
    $this->io()->text(dt('File: @path', ['@path' => $seedPath]));
    $this->io()->text(dt('Generated at: @date', ['@date' => $data['generated_at'] ?? 'unknown']));
    $this->io()->newLine();

    $this->io()->section('Organization');
    $this->io()->text($data['organization']['name'] ?? 'N/A');

    $this->io()->section('Training Program');
    $this->io()->text($data['training_program']['name'] ?? 'N/A');

    $this->io()->section('Courses (' . count($data['courses'] ?? []) . ')');
    foreach ($data['courses'] ?? [] as $course) {
      $this->io()->text(dt('  @id. @title [@level] — @chapters chapters x @lessons lessons', [
        '@id' => $course['id'],
        '@title' => $course['title'],
        '@level' => $course['level'],
        '@chapters' => $course['chapters_count'],
        '@lessons' => $course['lessons_per_chapter'],
      ]));
    }

    $this->io()->section('Instructors (' . count($data['instructors'] ?? []) . ')');
    foreach ($data['instructors'] ?? [] as $instructor) {
      $this->io()->text(dt('  @name <@email>', [
        '@name' => $instructor['name'],
        '@email' => $instructor['email'],
      ]));
    }
  }

  /**
   * Import generated content into the system.
   *
   * @param array $options
   *   Command options.
   * @option from-manifest
   *   Import using manifest.json order.
   * @option type
   *   Import only a specific type (courses, users, quizzes, etc).
   * @option file
   *   Import a specific generated file.
   * @option dry-run
   *   Validate files without importing.
   *
   * @command everlms:import
   * @aliases lms-import
   * @usage everlms:import
   *   Import all generated content.
   * @usage everlms:import --type=courses
   *   Import only courses.
   * @usage everlms:import --dry-run
   *   Validate generated files.
   */
  public function import(array $options = [
    'from-manifest' => FALSE,
    'type' => NULL,
    'file' => NULL,
    'dry-run' => FALSE,
  ]): void {
    $generatedPath = $this->manager->getGeneratedPath();

    if (!is_dir($generatedPath)) {
      $this->logger()->error(dt('Generated content directory not found at @path. Run AI content generation first.', [
        '@path' => $generatedPath,
      ]));
      return;
    }

    $manifest = $this->manager->loadManifest();
    if (!$manifest) {
      $this->logger()->error(dt('No manifest.json found in @path. Ensure AI has generated content with a manifest.', [
        '@path' => $generatedPath,
      ]));
      return;
    }

    $this->io()->title('EverLMS Content Import');
    $this->io()->text(dt('Generated by: @by at @date', [
      '@by' => $manifest['generated_by'] ?? 'unknown',
      '@date' => $manifest['generated_at'] ?? 'unknown',
    ]));

    // Show summary.
    if (!empty($manifest['summary'])) {
      $rows = [];
      foreach ($manifest['summary'] as $key => $value) {
        $rows[] = [str_replace('_', ' ', ucfirst($key)), $value];
      }
      $this->io()->table(['Content', 'Count'], $rows);
    }

    // Determine files to import.
    $importOrder = $manifest['import_order'] ?? [];
    if ($options['file']) {
      $importOrder = [$options['file']];
    }
    elseif ($options['type']) {
      $importOrder = array_filter($importOrder, function ($file) use ($options) {
        return str_starts_with($file, $options['type'] . '/');
      });
    }

    if (empty($importOrder)) {
      $this->logger()->warning(dt('No files to import.'));
      return;
    }

    // Validate files exist.
    $validFiles = [];
    foreach ($importOrder as $file) {
      $fullPath = $generatedPath . '/' . $file;
      if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        $json = json_decode($content, TRUE);
        if ($json !== NULL) {
          $validFiles[$file] = $json;
          $this->logger()->info(dt('Valid: @file', ['@file' => $file]));
        }
        else {
          $this->logger()->error(dt('Invalid JSON: @file', ['@file' => $file]));
        }
      }
      else {
        $this->logger()->warning(dt('File not found: @file', ['@file' => $file]));
      }
    }

    if ($options['dry-run']) {
      $this->logger()->success(dt('Dry run complete. @valid/@total files are valid.', [
        '@valid' => count($validFiles),
        '@total' => count($importOrder),
      ]));
      return;
    }

    if (!$this->io()->confirm(dt('Import @count files?', ['@count' => count($validFiles)]), TRUE)) {
      $this->logger()->notice(dt('Cancelled.'));
      return;
    }

    // Import each file.
    $imported = 0;
    $errors = 0;

    foreach ($validFiles as $file => $data) {
      try {
        $count = $this->importFile($file, $data);
        $imported += $count;
        $this->logger()->success(dt('Imported @count entities from @file', [
          '@count' => $count,
          '@file' => $file,
        ]));
      }
      catch (\Exception $e) {
        $errors++;
        $this->logger()->error(dt('Error importing @file: @message', [
          '@file' => $file,
          '@message' => $e->getMessage(),
        ]));
      }
    }

    $this->logger()->success(dt('Import complete: @imported entities imported, @errors errors.', [
      '@imported' => $imported,
      '@errors' => $errors,
    ]));
  }

  /**
   * Import a single generated file.
   */
  protected function importFile(string $filename, array $data): int {
    $count = 0;

    // Determine type from filename path.
    $type = explode('/', $filename)[0] ?? '';

    switch ($type) {
      case 'users':
        $count = $this->importUsers($data);
        break;

      case 'courses':
        $count = $this->importCourse($data);
        break;

      case 'quizzes':
        $count = $this->importQuiz($data);
        break;

      case 'certificates':
        $count = $this->importCertificates($data);
        break;

      case 'commerce':
        $count = $this->importCommerce($data);
        break;

      case 'community':
        $count = $this->importCommunity($data);
        break;

      default:
        $this->logger()->warning(dt('Unknown import type: @type', ['@type' => $type]));
    }

    return $count;
  }

  /**
   * Import users from generated data.
   */
  protected function importUsers(array $data): int {
    $count = 0;
    $userStorage = $this->entityTypeManager->getStorage('user');

    $users = $data['instructors'] ?? $data['students'] ?? [];
    foreach ($users as $userData) {
      $existing = $userStorage->loadByProperties(['mail' => $userData['email']]);
      if (!empty($existing)) {
        continue;
      }

      $fields = [
        'name' => $userData['username'] ?? $userData['email'],
        'mail' => $userData['email'],
        'pass' => $userData['password'] ?? 'ChangeMe@123',
        'status' => 1,
      ];

      $user = $userStorage->create($fields);

      // Safely set display name if field exists.
      $displayName = $userData['display_name'] ?? $userData['name'] ?? '';
      if ($displayName && $user->hasField('field_display_name')) {
        $user->set('field_display_name', $displayName);
      }

      $role = $userData['role'] ?? 'student';
      if ($role !== 'authenticated') {
        // Check role exists before adding.
        $roleEntities = $this->entityTypeManager->getStorage('user_role')->loadMultiple([$role]);
        if (!empty($roleEntities)) {
          $user->addRole($role);
        }
        else {
          $this->logger()->warning(dt('Role @role does not exist, skipping role assignment for @user', [
            '@role' => $role,
            '@user' => $userData['email'],
          ]));
        }
      }

      $user->save();
      $count++;
    }

    return $count;
  }

  /**
   * Import a course with chapters and lessons.
   *
   * Relationship: Course --field_chapters--> Chapter --field_lessons--> Lesson
   * (parent references children, not the other way around)
   */
  protected function importCourse(array $data): int {
    $count = 0;
    $courseData = $data['course'] ?? [];
    if (empty($courseData['title'])) {
      return 0;
    }

    $courseStorage = $this->entityTypeManager->getStorage('lms_course');

    $existing = $courseStorage->loadByProperties(['name' => $courseData['title']]);
    if (!empty($existing)) {
      $this->logger()->warning(dt('Course already exists: @title — skipping.', ['@title' => $courseData['title']]));
      return 0;
    }

    // Find instructor by email.
    $instructorUid = 1;
    if (!empty($courseData['instructor_email'])) {
      $users = $this->entityTypeManager->getStorage('user')
        ->loadByProperties(['mail' => $courseData['instructor_email']]);
      if (!empty($users)) {
        $instructorUid = (int) reset($users)->id();
      }
    }

    // Create lessons and chapters first, then link to course.
    $chapterStorage = $this->entityTypeManager->getStorage('lms_chapter');
    $lessonStorage = $this->entityTypeManager->getStorage('lms_lesson');
    $chapterIds = [];

    foreach ($data['chapters'] ?? [] as $chapterWeight => $chapterData) {
      // Create lessons for this chapter.
      $lessonIds = [];
      foreach ($chapterData['lessons'] ?? [] as $lessonWeight => $lessonData) {
        $lessonFields = [
          'name' => $lessonData['title'],
          'status' => 1,
          'field_type' => $lessonData['type'] ?? 'text',
          'field_content' => [
            'value' => $lessonData['content'] ?? '',
            'format' => 'full_html',
          ],
          'weight' => $lessonData['weight'] ?? $lessonWeight,
          'field_duration' => (int) ($lessonData['duration'] ?? 0),
          'field_free_preview' => $lessonData['is_preview'] ?? FALSE,
        ];

        if (!empty($lessonData['video_url'])) {
          $lessonFields['field_video_url'] = $lessonData['video_url'];
        }

        $lesson = $lessonStorage->create($lessonFields);
        $lesson->save();
        $lessonIds[] = $lesson->id();
        $count++;
      }

      // Create chapter and link lessons.
      $chapter = $chapterStorage->create([
        'name' => $chapterData['title'],
        'status' => 1,
        'field_description' => [
          'value' => $chapterData['description'] ?? '',
          'format' => 'full_html',
        ],
        'field_lessons' => $lessonIds,
        'weight' => $chapterData['weight'] ?? $chapterWeight,
      ]);
      $chapter->save();
      $chapterIds[] = $chapter->id();
      $count++;
    }

    // Create course and link chapters.
    $courseFields = [
      'name' => $courseData['title'],
      'status' => ($courseData['status'] ?? 'published') === 'published' ? 1 : 0,
      'field_description' => [
        'value' => $courseData['description'] ?? '',
        'format' => 'full_html',
      ],
      'field_chapters' => $chapterIds,
      'field_price' => $courseData['price'] ?? 0,
      'field_price_type' => $courseData['pricing_type'] ?? 'free',
      'field_featured' => $courseData['featured'] ?? FALSE,
      'uid' => $instructorUid,
      'field_instructor' => [$instructorUid],
    ];

    // field_short_description is text_long, needs value+format.
    if (!empty($courseData['short_description'])) {
      $courseFields['field_short_description'] = [
        'value' => $courseData['short_description'],
        'format' => 'full_html',
      ];
    }

    $course = $courseStorage->create($courseFields);
    $course->save();
    $count++;

    return $count;
  }

  /**
   * Import a quiz with questions and answers.
   *
   * Relationship: Quiz --field_questions--> Question --field_choices--> Answer
   * (parent references children)
   */
  protected function importQuiz(array $data): int {
    $count = 0;
    $quizData = $data['quiz'] ?? [];
    if (empty($quizData['title'])) {
      return 0;
    }

    $questionStorage = $this->entityTypeManager->getStorage('lms_question');
    $answerStorage = $this->entityTypeManager->getStorage('lms_answer');
    $questionIds = [];

    // Create questions first.
    foreach ($data['questions'] ?? [] as $qWeight => $qData) {
      $questionFields = [
        'name' => $qData['text'],
        'type' => $qData['type'],
        'field_points' => $qData['points'] ?? 5,
      ];

      // Explanation is text_long.
      if (!empty($qData['explanation'])) {
        $questionFields['field_explanation'] = [
          'value' => $qData['explanation'],
          'format' => 'full_html',
        ];
      }

      // Handle type-specific fields.
      if ($qData['type'] === 'true_false' && isset($qData['correct_answer'])) {
        $questionFields['field_correct_answer'] = $qData['correct_answer'];
      }
      if (in_array($qData['type'], ['fill_blank', 'short_answer']) && !empty($qData['correct_answers'])) {
        $questionFields['field_correct_answers'] = $qData['correct_answers'];
      }
      if ($qData['type'] === 'essay') {
        if (!empty($qData['min_words'])) {
          $questionFields['field_min_words'] = (int) $qData['min_words'];
        }
      }
      if ($qData['type'] === 'ordering' && !empty($qData['items'])) {
        // Ordered items stored as string list in correct sequence.
        $orderedTexts = [];
        $items = $qData['items'];
        usort($items, fn($a, $b) => ($a['correct_position'] ?? 0) - ($b['correct_position'] ?? 0));
        foreach ($items as $item) {
          $orderedTexts[] = $item['text'];
        }
        $questionFields['field_ordered_items'] = $orderedTexts;
      }

      $question = $questionStorage->create($questionFields);
      $question->save();
      $count++;

      // Create answers for choice-type questions and link via field_choices.
      if (in_array($qData['type'], ['single_choice', 'multiple_choice']) && !empty($qData['answers'])) {
        $answerIds = [];
        foreach ($qData['answers'] as $aData) {
          $answer = $answerStorage->create([
            'name' => $aData['text'],
            'field_correct' => $aData['is_correct'] ?? FALSE,
          ]);
          $answer->save();
          $answerIds[] = $answer->id();
          $count++;
        }
        // Link answers to question.
        $question->set('field_choices', $answerIds);
        $question->save();
      }

      // Create matching pairs and link via field_matching_pairs.
      if ($qData['type'] === 'matching' && !empty($qData['pairs'])) {
        $pairStorage = $this->entityTypeManager->getStorage('lms_matching_pair');
        $pairIds = [];
        foreach ($qData['pairs'] as $pair) {
          $matchingPair = $pairStorage->create([
            'field_left_item' => $pair['left'],
            'field_right_item' => $pair['right'],
          ]);
          $matchingPair->save();
          $pairIds[] = $matchingPair->id();
          $count++;
        }
        $question->set('field_matching_pairs', $pairIds);
        $question->save();
      }

      $questionIds[] = $question->id();
    }

    // Create quiz and link questions.
    $quizStorage = $this->entityTypeManager->getStorage('lms_quiz');
    $quiz = $quizStorage->create([
      'name' => $quizData['title'],
      'field_body' => [
        'value' => $quizData['instructions'] ?? '',
        'format' => 'full_html',
      ],
      'field_threshold' => (int) ($quizData['pass_threshold'] ?? 70),
      'field_time_limit' => (int) ($quizData['time_limit'] ?? 30),
      'field_take_quiz_number' => (int) ($quizData['max_attempts'] ?? 3),
      'field_form_style' => $quizData['display_style'] ?? 'question_step',
      'field_questions' => $questionIds,
      'uid' => 1,
    ]);
    $quiz->save();
    $count++;

    return $count;
  }

  /**
   * Import certificates from generated data.
   */
  protected function importCertificates(array $data): int {
    $count = 0;
    $storage = $this->entityTypeManager->getStorage('lms_certificate_template');

    foreach ($data['templates'] ?? [] as $templateData) {
      $template = $storage->create([
        'name' => $templateData['name'] ?? 'Certificate Template',
        'status' => 1,
        'field_orientation' => $templateData['layout'] ?? 'landscape',
        'field_design' => 'modern',
      ]);
      $template->save();
      $count++;
    }

    return $count;
  }

  /**
   * Import commerce data (membership plans).
   */
  protected function importCommerce(array $data): int {
    $count = 0;

    if (!empty($data['membership_plans'])) {
      $storage = $this->entityTypeManager->getStorage('lms_membership_plan');
      foreach ($data['membership_plans'] as $planData) {
        $plan = $storage->create([
          'name' => $planData['name'],
          'status' => 1,
          'field_price' => $planData['price'] ?? 0,
          'field_duration' => $planData['duration'] ?? 'monthly',
        ]);
        // Only set fields that exist on the entity.
        if ($plan->hasField('field_tier')) {
          $plan->set('field_tier', $planData['tier'] ?? 'basic');
        }
        $plan->save();
        $count++;
      }
    }

    return $count;
  }

  /**
   * Import community data (forums, ratings, badges).
   */
  protected function importCommunity(array $data): int {
    $count = 0;

    // Forum topics.
    if (!empty($data['forum_topics'])) {
      $topicStorage = $this->entityTypeManager->getStorage('lms_forum_topic');
      $replyStorage = $this->entityTypeManager->getStorage('lms_forum_reply');

      foreach ($data['forum_topics'] as $topicData) {
        $topic = $topicStorage->create([
          'name' => $topicData['title'],
          'status' => 1,
          'field_body' => [
            'value' => $topicData['body'] ?? '',
            'format' => 'full_html',
          ],
          'uid' => 1,
        ]);
        $topic->save();
        $count++;

        foreach ($topicData['replies'] ?? [] as $replyData) {
          $reply = $replyStorage->create([
            'field_topic' => $topic->id(),
            'field_body' => [
              'value' => $replyData['body'] ?? '',
              'format' => 'full_html',
            ],
            'uid' => 1,
          ]);
          $reply->save();
          $count++;
        }
      }
    }

    // Badges.
    if (!empty($data['badges'])) {
      $badgeStorage = $this->entityTypeManager->getStorage('lms_badge');
      foreach ($data['badges'] as $badgeData) {
        $criteriaJson = json_encode([
          'type' => $badgeData['criteria_type'] ?? 'course_completion',
        ]);
        $badge = $badgeStorage->create([
          'name' => $badgeData['name'],
          'field_xp_reward' => $badgeData['points'] ?? 10,
          'field_criteria' => $criteriaJson,
        ]);
        $badge->save();
        $count++;
      }
    }

    return $count;
  }

  /**
   * Validates seed input data.
   */
  protected function validateSeedInput(array $data): array {
    $errors = [];

    if (empty($data['organization']['name'])) {
      $errors[] = 'Missing: organization.name';
    }
    if (empty($data['training_program']['name'])) {
      $errors[] = 'Missing: training_program.name';
    }
    if (empty($data['courses'])) {
      $errors[] = 'Missing: at least one course is required';
    }
    if (empty($data['instructors'])) {
      $errors[] = 'Missing: at least one instructor is required';
    }

    foreach ($data['courses'] ?? [] as $i => $course) {
      if (empty($course['title'])) {
        $errors[] = "Missing: courses[$i].title";
      }
      if (empty($course['chapters_count']) || $course['chapters_count'] < 2) {
        $errors[] = "Invalid: courses[$i].chapters_count must be >= 2";
      }
      if (empty($course['lessons_per_chapter']) || $course['lessons_per_chapter'] < 2) {
        $errors[] = "Invalid: courses[$i].lessons_per_chapter must be >= 2";
      }
    }

    foreach ($data['instructors'] ?? [] as $i => $instructor) {
      if (empty($instructor['name'])) {
        $errors[] = "Missing: instructors[$i].name";
      }
      if (empty($instructor['email'])) {
        $errors[] = "Missing: instructors[$i].email";
      }
    }

    return $errors;
  }

}
