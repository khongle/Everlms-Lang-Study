<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Grading\Strategy;

use Drupal\lms_question\Entity\Question;
use Drupal\lms_quiz\Grading\QuestionGradingInterface;

/**
 * Grading Strategy for Matching questions.
 */
class MatchingStrategy implements QuestionGradingInterface {

  public function grade(Question $question, mixed $user_submission): array {
    $max_score = $this->getMaxScore($question);
    
    $expected_pairs = [];
    foreach ($question->get('field_matching_pairs') as $ref) {
      $pair = $ref->entity;
      if ($pair) {
        $expected_pairs[] = [
          'left' => $pair->get('field_left_item')->value,
          'right' => $pair->get('field_right_item')->value,
        ];
      }
    }
    
    // The user submission is expected to be an array with integer keys matching loop index.
    // E.g., user_submission[0] = "right_item_choice"
    $submitted_array = is_array($user_submission) ? $user_submission : [];
    
    $is_correct = TRUE;
    $text_build = [];

    foreach ($expected_pairs as $index => $expected) {
      $submitted_right = $submitted_array[$index] ?? '';
      $text_build[] = $expected['left'] . ' = ' . ($submitted_right ?: '[empty]');
      
      if ($submitted_right !== $expected['right']) {
        $is_correct = FALSE;
      }
    }

    // Must have configured pairs to be true.
    if (empty($expected_pairs)) {
      $is_correct = FALSE;
    }

    $score = $is_correct ? $max_score : 0.0;

    return [
      'is_correct' => $is_correct,
      'score' => $score,
      'text_answer' => implode(' | ', $text_build),
      'answer_ids' => [],
      'pending_review' => FALSE,
    ];
  }

  public function getMaxScore(Question $question): float {
    return (float) $question->get('field_points')->value;
  }

}
