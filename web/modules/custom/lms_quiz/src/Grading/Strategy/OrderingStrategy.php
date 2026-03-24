<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Grading\Strategy;

use Drupal\lms_question\Entity\Question;
use Drupal\lms_quiz\Grading\QuestionGradingInterface;

/**
 * Grading Strategy for Ordering questions.
 */
class OrderingStrategy implements QuestionGradingInterface {

  public function grade(Question $question, mixed $user_submission): array {
    $max_score = $this->getMaxScore($question);
    
    $correct_order = [];
    foreach ($question->get('field_ordered_items') as $val) {
      $correct_order[] = $val->value;
    }
    
    // User submission is an array of strings representing their sorted list.
    $user_order = is_array($user_submission) ? array_values($user_submission) : [];
    
    // Strict comparison of the two arrays. Both order and values must perfectly match.
    $is_correct = ($correct_order !== [] && $correct_order === $user_order);
    $score = $is_correct ? $max_score : 0.0;

    return [
      'is_correct' => $is_correct,
      'score' => $score,
      'text_answer' => implode(' -> ', $user_order),
      'answer_ids' => [],
      'pending_review' => FALSE,
    ];
  }

  public function getMaxScore(Question $question): float {
    return (float) $question->get('field_points')->value;
  }

}
