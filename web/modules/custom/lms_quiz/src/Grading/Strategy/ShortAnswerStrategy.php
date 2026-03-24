<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Grading\Strategy;

use Drupal\lms_question\Entity\Question;
use Drupal\lms_quiz\Grading\QuestionGradingInterface;

/**
 * Grading Strategy for Short Answer questions.
 * Logic is similar to FillBlank but can be extended for keyword matching.
 */
class ShortAnswerStrategy implements QuestionGradingInterface {

  public function grade(Question $question, mixed $user_submission): array {
    $max_score = $this->getMaxScore($question);
    
    $correct_answers = [];
    foreach ($question->get('field_correct_answers') as $val) {
      $correct_answers[] = $val->value;
    }
    
    $case_sensitive = (bool) $question->get('field_case_sensitive')->value;
    $ans = $user_submission ? trim((string) $user_submission) : '';
    
    $is_correct = FALSE;
    foreach ($correct_answers as $ca) {
      $ca = trim($ca);
      if ($case_sensitive) {
        if ($ca === $ans) {
          $is_correct = TRUE;
          break;
        }
      } else {
        if (strtolower($ca) === strtolower($ans)) {
          $is_correct = TRUE;
          break;
        }
      }
    }

    $score = $is_correct ? $max_score : 0.0;

    return [
      'is_correct' => $is_correct,
      'score' => $score,
      'text_answer' => $ans,
      'answer_ids' => [],
      'pending_review' => FALSE,
    ];
  }

  public function getMaxScore(Question $question): float {
    return (float) $question->get('field_points')->value;
  }

}
