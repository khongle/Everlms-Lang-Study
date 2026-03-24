<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Grading\Strategy;

use Drupal\lms_question\Entity\Question;
use Drupal\lms_quiz\Grading\QuestionGradingInterface;

/**
 * Grading Strategy for Essay questions.
 *
 * Essays always require manual review.
 */
class EssayStrategy implements QuestionGradingInterface {

  public function grade(Question $question, mixed $user_submission): array {
    $ans = $user_submission ? trim((string) $user_submission) : '';
    
    return [
      'is_correct' => FALSE,
      'score' => 0.0,
      'text_answer' => $ans,
      'answer_ids' => [],
      'pending_review' => TRUE,
    ];
  }

  public function getMaxScore(Question $question): float {
    return (float) $question->get('field_points')->value;
  }

}
