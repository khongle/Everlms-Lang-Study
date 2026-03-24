<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Grading;

use Drupal\lms_question\Entity\Question;

/**
 * Interface QuestionGradingInterface.
 *
 * Defines the strict contract for grading a submitted answer to a LMS Question.
 */
interface QuestionGradingInterface {

  /**
   * Grades the user's submission for a question.
   *
   * @param \Drupal\lms_question\Entity\Question $question
   *   The question entity.
   * @param mixed $user_submission
   *   The raw submitted payload from the standard HTTP POST array.
   *
   * @return array
   *   A standardized array containing:
   *   - 'is_correct' : bool
   *   - 'score' : float
   *   - 'text_answer' : string (What the caller will save to field_text_answer)
   *   - 'answer_ids' : array (Array of referenced Answer IDs, if applicable)
   *   - 'pending_review' : bool (True if requires human grading)
   */
  public function grade(Question $question, mixed $user_submission): array;

  /**
   * Calculates the maximum possible score for the question.
   *
   * @param \Drupal\lms_question\Entity\Question $question
   *   The question entity.
   *
   * @return float
   *   The absolute maximum score.
   */
  public function getMaxScore(Question $question): float;

}
