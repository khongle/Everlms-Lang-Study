<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Grading\Strategy;

use Drupal\lms_question\Entity\Question;
use Drupal\lms_quiz\Grading\QuestionGradingInterface;

/**
 * Grading Strategy for Multiple Choice questions.
 */
class MultipleChoiceStrategy implements QuestionGradingInterface {

  public function grade(Question $question, mixed $user_submission): array {
    $max_score = $this->getMaxScore($question);
    
    // Checkboxes send arrays.
    $submitted_ids = is_array($user_submission) ? array_map('intval', $user_submission) : [];
    
    // Gather all correct answers configured for this question.
    $correct_answer_ids = [];
    foreach ($question->get('field_choices') as $ref) {
      $answer = $ref->entity;
      if ($answer && (bool) $answer->get('field_correct')->value) {
        $correct_answer_ids[] = (int) $answer->id();
      }
    }

    // Determine correctness (Strict Mode: Must match exactly all correct answers and no wrong answers).
    $is_correct = FALSE;
    $score = 0.0;
    
    if (!empty($correct_answer_ids) && empty(array_diff($correct_answer_ids, $submitted_ids)) && empty(array_diff($submitted_ids, $correct_answer_ids))) {
      $is_correct = TRUE;
      $score = $max_score;
    }

    // Optionally: We could implement partial grading here if configured, but strict is standard.

    return [
      'is_correct' => $is_correct,
      'score' => $score,
      'text_answer' => '',
      'answer_ids' => $submitted_ids,
      'pending_review' => FALSE,
    ];
  }

  public function getMaxScore(Question $question): float {
    return (float) $question->get('field_points')->value;
  }

}
