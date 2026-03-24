<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Grading\Strategy;

use Drupal\lms_question\Entity\Question;
use Drupal\lms_quiz\Grading\QuestionGradingInterface;

/**
 * Grading Strategy for Single Choice questions.
 */
class SingleChoiceStrategy implements QuestionGradingInterface {

  public function grade(Question $question, mixed $user_submission): array {
    $max_score = $this->getMaxScore($question);
    
    // User submission should be a single scalar integer/string representing lms_answer ID.
    $submitted_id = (int) $user_submission;

    $is_correct = FALSE;
    $score = 0.0;
    
    if ($submitted_id > 0) {
      // Find out if the selected answer is actually correct.
      foreach ($question->get('field_choices') as $ref) {
        $answer = $ref->entity;
        if ($answer && $answer->id() == $submitted_id) {
          if ((bool) $answer->get('field_correct')->value) {
            $is_correct = TRUE;
            $score = $max_score;
          }
          break;
        }
      }
    }

    return [
      'is_correct' => $is_correct,
      'score' => $score,
      'text_answer' => '',
      'answer_ids' => $submitted_id > 0 ? [$submitted_id] : [],
      'pending_review' => FALSE,
    ];
  }

  public function getMaxScore(Question $question): float {
    return (float) $question->get('field_points')->value;
  }

}
