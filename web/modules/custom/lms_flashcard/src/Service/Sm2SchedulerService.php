<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Pure SM-2 spaced-repetition scheduling logic.
 *
 * No entity I/O here on purpose — callers (FlashcardApiController) read the
 * current Review entity's field values, pass them in as $currentState, and
 * write the returned array back onto the entity. That keeps this service
 * trivially testable and reusable independent of how state is stored.
 */
final class Sm2SchedulerService {

  /**
   * UI button index (0-3: Again/Hard/Good/Easy) to SM-2's 0-5 quality scale.
   */
  private const QUALITY_MAP = [
    0 => 0, // Again: complete blackout / forgot.
    1 => 3, // Hard: recalled with serious difficulty.
    2 => 4, // Good: recalled after some hesitation.
    3 => 5, // Easy: recalled perfectly and instantly.
  ];

  private const MIN_EASE_FACTOR = 1.30;

  public function __construct(
    protected ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Computes the next SM-2 state after grading a card.
   *
   * @param array $currentState
   *   ['ease_factor' => float, 'interval_days' => int, 'repetitions' => int,
   *   'status' => string].
   * @param int $buttonIndex
   *   0 = Again, 1 = Hard, 2 = Good, 3 = Easy.
   * @param int $now
   *   Request timestamp (injected for deterministic testing).
   *
   * @return array
   *   ['ease_factor' => float, 'interval_days' => int, 'repetitions' => int,
   *   'status' => string, 'next_review_at' => int, 'last_reviewed_at' => int,
   *   'lapses_delta' => int].
   */
  public function grade(array $currentState, int $buttonIndex, int $now): array {
    if (!array_key_exists($buttonIndex, self::QUALITY_MAP)) {
      throw new \InvalidArgumentException('Button index must be 0 (Again), 1 (Hard), 2 (Good) or 3 (Easy).');
    }
    $quality = self::QUALITY_MAP[$buttonIndex];

    $settings = $this->configFactory->get('lms_flashcard.settings');
    $hardFactor = (float) ($settings->get('hard_interval_factor') ?? 1.2);
    $easyBonus = (float) ($settings->get('easy_bonus') ?? 1.3);

    $easeFactor = (float) ($currentState['ease_factor'] ?? 2.5);
    $intervalDays = (int) ($currentState['interval_days'] ?? 0);
    $repetitions = (int) ($currentState['repetitions'] ?? 0);
    $previousStatus = (string) ($currentState['status'] ?? 'new');

    // Ease factor updates on every grade, including "Again" — textbook SM-2.
    $easeFactor += 0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02);
    $easeFactor = max(self::MIN_EASE_FACTOR, $easeFactor);

    $lapsesDelta = 0;

    if ($quality < 3) {
      // Again: reset progress, card goes back into the (re)learning queue.
      $repetitions = 0;
      $intervalDays = 0;
      $status = $previousStatus === 'review' ? 'relearning' : 'learning';
      $lapsesDelta = 1;
    }
    else {
      $repetitions++;

      if ($repetitions === 1) {
        $intervalDays = 1;
      }
      elseif ($repetitions === 2) {
        $intervalDays = 6;
      }
      else {
        // Good = previous interval * EF (textbook SM-2). Hard bypasses the
        // EF growth in favor of a gentler fixed factor so it doesn't grow
        // the interval as aggressively as Good/Easy despite both being
        // SM-2 "passes". Easy gets a bonus on top of the normal growth.
        $intervalDays = match ($buttonIndex) {
          1 => (int) round($intervalDays * $hardFactor),
          3 => (int) round($intervalDays * $easeFactor * $easyBonus),
          default => (int) round($intervalDays * $easeFactor),
        };
      }

      $intervalDays = max(1, $intervalDays);
      $status = 'review';
    }

    return [
      'ease_factor' => round($easeFactor, 2),
      'interval_days' => $intervalDays,
      'repetitions' => $repetitions,
      'status' => $status,
      'next_review_at' => $now + ($intervalDays * 86400),
      'last_reviewed_at' => $now,
      'lapses_delta' => $lapsesDelta,
    ];
  }

}
