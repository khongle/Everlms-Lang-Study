<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Grading\Strategy;

use Drupal\lms_question\Entity\Question;
use Drupal\lms_quiz\Grading\QuestionGradingInterface;

/**
 * Grading Strategy for True/False questions.
 */
class TrueFalseStrategy implements QuestionGradingInterface {

  public function grade(Question $question, mixed $user_submission): array {
    $max_score = $this->getMaxScore($question);
    
    // The submission is a scalar '1' or '0' (or missing).
    $submitted_bool = ($user_submission !== NULL && $user_submission !== '') ? (bool) $user_submission : NULL;
    $actual_correct = (bool) $question->get('field_correct_answer')->value;

    $is_correct = FALSE;
    $score = 0.0;
    
    if ($submitted_bool !== NULL && $submitted_bool === $actual_correct) {
      $is_correct = TRUE;
      $score = $max_score;
    }

    return [
      'is_correct' => $is_correct,
      'score' => $score,
      'text_answer' => $submitted_bool !== NULL ? ($submitted_bool ? 'True' : 'False') : '',
      'answer_ids' => [],
      'pending_review' => FALSE,
    ];
  }

  public function getMaxScore(Question $question): float {
    return (float) $question->get('field_points')->value;
  }

}
