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
