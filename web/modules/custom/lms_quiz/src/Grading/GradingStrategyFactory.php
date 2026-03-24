<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Grading;

use Drupal\lms_quiz\Grading\Strategy\SingleChoiceStrategy;
use Drupal\lms_quiz\Grading\Strategy\MultipleChoiceStrategy;
use Drupal\lms_quiz\Grading\Strategy\TrueFalseStrategy;
use Drupal\lms_quiz\Grading\Strategy\FillBlankStrategy;
use Drupal\lms_quiz\Grading\Strategy\ShortAnswerStrategy;
use Drupal\lms_quiz\Grading\Strategy\OrderingStrategy;
use Drupal\lms_quiz\Grading\Strategy\MatchingStrategy;
use Drupal\lms_quiz\Grading\Strategy\EssayStrategy;

/**
 * Factory to map a Question Bundle to its specific Grading Strategy.
 */
final class GradingStrategyFactory {

  /**
   * Returns the strategy instance for a specific question type.
   *
   * @param string $bundle
   *   The machine name of the lms_question bundle.
   */
  public static function getStrategy(string $bundle): ?QuestionGradingInterface {
    return match ($bundle) {
      'single_choice' => new SingleChoiceStrategy(),
      'multiple_choice' => new MultipleChoiceStrategy(),
      'true_false' => new TrueFalseStrategy(),
      'fill_blank' => new FillBlankStrategy(),
      'short_answer' => new ShortAnswerStrategy(),
      'ordering' => new OrderingStrategy(),
      'matching' => new MatchingStrategy(),
      'essay' => new EssayStrategy(),
      default => null,
    };
  }

}
