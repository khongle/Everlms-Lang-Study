<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lms_quiz\Entity\Quiz;
use Drupal\lms_quiz\Entity\QuizResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Quiz-related pages: take quiz, view result.
 */
final class QuizController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * Take quiz — shows questions.
   */
  public function takeQuiz(Quiz $lms_quiz): array {
    $uid = (int) $this->currentUser->id();

    // 1. Check Max Attempts
    $max_attempts = (int) $lms_quiz->get('field_take_quiz_number')->value;
    if ($max_attempts > 0) {
      $attempt_count = $this->entityTypeManager->getStorage('lms_quiz_result')
        ->getQuery()
        ->condition('uid', $uid)
        ->condition('field_quiz', $lms_quiz->id())
        ->accessCheck(FALSE)
        ->count()
        ->execute();

      if ($attempt_count >= $max_attempts) {
        return [
          '#markup' => $this->t('You have reached the maximum number of attempts (@max) for this quiz.', ['@max' => $max_attempts]),
        ];
      }
    }

    // 2. Check Prerequisites
    $prereqs = $lms_quiz->get('field_prerequisite_quizzes')->referencedEntities();
    if (!empty($prereqs)) {
      $term_passed = $this->entityTypeManager->getStorage('taxonomy_term')
        ->loadByProperties(['vid' => 'quiz_results', 'name' => 'Passed']);
      $term_passed = reset($term_passed);

      foreach ($prereqs as $prereq_quiz) {
        $has_passed = FALSE;
        if ($term_passed) {
          $passed_results = $this->entityTypeManager->getStorage('lms_quiz_result')
            ->getQuery()
            ->condition('uid', $uid)
            ->condition('field_quiz', $prereq_quiz->id())
            ->condition('field_result', $term_passed->id())
            ->accessCheck(FALSE)
            ->count()
            ->execute();
          if ($passed_results > 0) {
            $has_passed = TRUE;
          }
        }

        if (!$has_passed) {
          return [
            '#markup' => $this->t('You must pass the quiz "@quiz" before taking this quiz.', ['@quiz' => $prereq_quiz->label()]),
          ];
        }
      }
    }

    $question_ids = [];
    foreach ($lms_quiz->get('field_questions') as $item) {
      $question_ids[] = $item->target_id;
    }

    $questions = $question_ids
      ? $this->entityTypeManager->getStorage('lms_question')->loadMultiple($question_ids)
      : [];

    // Randomize if configured.
    $question_count = (int) $lms_quiz->get('field_question_count')->value;
    if ($question_count > 0 && count($questions) > $question_count) {
      $keys = array_rand($questions, $question_count);
      $questions = array_intersect_key($questions, array_flip((array) $keys));
    }

    // Build question view models.
    $quiz_questions = [];
    foreach ($questions as $question) {
      $q = [
        'id' => $question->id(),
        'type' => $question->bundle(),
        'name' => $question->label(),
        'body' => $question->get('field_body')->value ?? '',
        'points' => (int) $question->get('field_points')->value,
      ];

      // Load choices for single/multiple choice.
      if (in_array($question->bundle(), ['single_choice', 'multiple_choice'])) {
        $choices = [];
        foreach ($question->get('field_choices') as $ref) {
          $answer = $ref->entity;
          if ($answer) {
            $choices[] = [
              'id' => $answer->id(),
              'text' => $answer->label(),
            ];
          }
        }
        $q['choices'] = $choices;
      }

      // Matching pairs.
      if ($question->bundle() === 'matching') {
        $pairs = [];
        foreach ($question->get('field_matching_pairs') as $ref) {
          $pair = $ref->entity;
          if ($pair) {
            $pairs[] = [
              'left' => $pair->get('field_left_item')->value,
              'right' => $pair->get('field_right_item')->value,
            ];
          }
        }
        $q['pairs'] = $pairs;
      }

      // Ordering items.
      if ($question->bundle() === 'ordering') {
        $items = [];
        foreach ($question->get('field_ordered_items') as $item) {
          $items[] = $item->value;
        }
        shuffle($items);
        $q['items'] = $items;
      }

      $quiz_questions[] = $q;
    }

    return [
      '#theme' => 'lms_quiz_take',
      '#quiz' => $lms_quiz,
      '#questions' => $quiz_questions,
      '#time_limit' => (int) $lms_quiz->get('field_time_limit')->value,
    ];
  }

  /**
   * Process quiz submission.
   */
  public function submitQuiz(Quiz $lms_quiz, \Symfony\Component\HttpFoundation\Request $request) {
    if (!$request->isMethod('POST')) {
      return $this->redirect('lms_quiz.take', ['lms_quiz' => $lms_quiz->id()]);
    }

    $uid = (int) $this->currentUser->id();
    $total_score = 0.0;
    $max_score = 0.0;
    $responses = [];
    $storage = $this->entityTypeManager->getStorage('lms_question_response');
    $has_pending_review = FALSE;

    foreach ($lms_quiz->get('field_questions') as $item) {
      $question = $item->entity;
      if (!$question) continue;

      $qid = $question->id();
      $bundle = $question->bundle();
      $base_key = 'q_' . $qid;

      // Extract submission data based on UI input naming convention.
      $user_submission = NULL;
      if ($bundle === 'matching') {
        $submission = [];
        $i = 0;
        // Symfony HttpFoundation Request query/request parameters can be retrieved safely
        while ($request->request->has($base_key . '_' . $i) || $request->request->has($base_key . '_' . $i . '[]')) {
          $submission[$i] = $request->request->get($base_key . '_' . $i);
          $i++;
        }
        $user_submission = $submission;
      } else {
        $user_submission = $request->request->all()[$base_key] ?? $request->request->get($base_key);
      }

      $strategy = \Drupal\lms_quiz\Grading\GradingStrategyFactory::getStrategy($bundle);
      if ($strategy) {
        $max_score += $strategy->getMaxScore($question);
        $result = $strategy->grade($question, $user_submission);

        // Create the QuestionResponse entity.
        $response_entity = $storage->create([
          'field_question' => $qid,
          'field_quiz' => $lms_quiz->id(),
          'field_correct' => $result['is_correct'],
          'field_score' => $result['score'],
          'field_text_answer' => $result['text_answer'],
          'field_pending_review' => $result['pending_review'],
          'field_answers' => $result['answer_ids'] ?? [],
        ]);
        $response_entity->save();

        if (!empty($result['pending_review'])) {
          $has_pending_review = TRUE;
        }

        $responses[] = ['target_id' => $response_entity->id()];
        $total_score += $result['score'];
      }
    }

    // Calculate final percentage score.
    $percentage = $max_score > 0 ? ($total_score / $max_score) * 100 : 0;
    $threshold = (float) $lms_quiz->get('field_threshold')->value;
    
    // Determine Result Term (Passed/Failed/Pending)
    $vocab = 'quiz_results';
    $term_name = ($percentage >= $threshold) ? 'Passed' : 'Failed';
    if ($has_pending_review) {
      $term_name = 'Pending';
    }

    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => $vocab,
      'name' => $term_name,
    ]);
    $result_term = reset($terms);

    // Fallback: If 'Pending' term doesn't exist, create it.
    if (!$result_term && $term_name === 'Pending') {
      $result_term = $this->entityTypeManager->getStorage('taxonomy_term')->create([
        'vid' => $vocab,
        'name' => 'Pending',
      ]);
      $result_term->save();
    }

    $now = \Drupal::time()->getRequestTime();
    
    // Create the QuizResult entity.
    $quiz_result = $this->entityTypeManager->getStorage('lms_quiz_result')->create([
      'uid' => $uid,
      'field_quiz' => $lms_quiz->id(),
      'field_score' => $percentage,
      'field_responses' => $responses,
      'field_start_time' => $now - 60,
      'field_end_time' => $now,
    ]);
    
    if ($result_term) {
      $quiz_result->set('field_result', $result_term->id());
    }
    
    $quiz_result->save();

    if ($result_term && $result_term->label() === 'Passed') {
      if (class_exists('\Drupal\lms_quiz\Event\QuizPassedEvent')) {
        $event = new \Drupal\lms_quiz\Event\QuizPassedEvent($quiz_result, $lms_quiz, $this->currentUser);
        \Drupal::service('event_dispatcher')->dispatch($event, \Drupal\lms_quiz\Event\QuizPassedEvent::EVENT_NAME);
      }
    }

    return $this->redirect('lms_quiz.result', ['lms_quiz_result' => $quiz_result->id()]);
  }

  /**
   * View quiz result.
   */
  public function viewResult(QuizResult $lms_quiz_result): array {
    $quiz = $lms_quiz_result->get('field_quiz')->entity;
    $result_term = $lms_quiz_result->get('field_result')->entity;

    // Load responses.
    $responses = [];
    foreach ($lms_quiz_result->get('field_responses') as $ref) {
      $response = $ref->entity;
      if (!$response) {
        continue;
      }
      $question = $response->get('field_question')->entity;
      $responses[] = [
        'question' => $question?->label() ?? '',
        'correct' => (bool) $response->get('field_correct')->value,
        'score' => (float) $response->get('field_score')->value,
        'text_answer' => $response->get('field_text_answer')->value ?? '',
        'feedback' => $response->get('field_teacher_feedback')->value ?? '',
        'pending' => (bool) $response->get('field_pending_review')->value,
      ];
    }

    return [
      '#theme' => 'lms_quiz_result',
      '#quiz' => $quiz,
      '#result' => $lms_quiz_result,
      '#result_label' => $result_term?->label() ?? 'N/A',
      '#score' => (float) $lms_quiz_result->get('field_score')->value,
      '#responses' => $responses,
    ];
  }

  /**
   * My quiz results.
   */
  public function myResults(): array {
    $uid = (int) $this->currentUser->id();

    $ids = $this->entityTypeManager->getStorage('lms_quiz_result')
      ->getQuery()
      ->condition('uid', $uid)
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $results = $ids
      ? $this->entityTypeManager->getStorage('lms_quiz_result')->loadMultiple($ids)
      : [];

    $items = [];
    foreach ($results as $result) {
      $quiz = $result->get('field_quiz')->entity;
      $items[] = [
        'id' => $result->id(),
        'quiz_name' => $quiz?->label() ?? '',
        'score' => (float) $result->get('field_score')->value,
        'date' => $result->get('created')->value,
        'url' => $result->toUrl()->toString(),
      ];
    }

    return [
      '#theme' => 'lms_my_quiz_results',
      '#results' => $items,
    ];
  }

}
