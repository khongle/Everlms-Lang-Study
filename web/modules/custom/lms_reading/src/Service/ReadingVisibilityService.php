<?php

declare(strict_types=1);

namespace Drupal\lms_reading\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_reading\Entity\ReadingPassage;

/**
 * Decides which reading passages a given student may see.
 *
 * A passage's own visibility rule mirrors StudyQueueBuilder::
 * isDeckVisibleTo() (published + own it, or owner is staff) — a passage is
 * ADDITIONALLY required to have its source deck visible to the same user,
 * so a passage can never leak vocabulary from a deck the student isn't
 * allowed to study in the first place. This only reads lms_flashcard_deck
 * through the entity API; it never touches lms_flashcard's own code.
 */
final class ReadingVisibilityService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Whether a passage should be visible/readable for a given user.
   */
  public function isPassageVisibleTo(ReadingPassage $passage, int $uid): bool {
    if (!$this->isOwnedOrPublishedByStaff($passage, $uid)) {
      return FALSE;
    }

    $deck = $passage->get('field_deck')->entity;
    if (!$deck) {
      // Dangling reference (deck deleted) — nothing to study, hide it.
      return FALSE;
    }

    return $this->isDeckVisibleTo($deck, $uid);
  }

  /**
   * Same shape as ReadingPassage's own check, kept private since only
   * isPassageVisibleTo() (which also checks the deck) should be called
   * from outside this service.
   */
  private function isOwnedOrPublishedByStaff(ReadingPassage $passage, int $uid): bool {
    if ((int) $passage->getOwnerId() === $uid) {
      return TRUE;
    }
    if (!$passage->get('status')->value) {
      return FALSE;
    }
    $owner = $passage->getOwner();
    if (!$owner) {
      return TRUE;
    }
    return $owner->hasPermission('view lms_course') || $owner->hasPermission('administer lms_course');
  }

  /**
   * Re-implements lms_flashcard's Deck visibility rule locally (rather than
   * calling StudyQueueBuilder::isDeckVisibleTo() directly) so lms_reading
   * only ever depends on lms_flashcard's *entities*, never its services —
   * a smaller, more stable surface to depend on.
   */
  private function isDeckVisibleTo(\Drupal\Core\Entity\EntityInterface $deck, int $uid): bool {
    if (!$deck instanceof \Drupal\user\EntityOwnerInterface) {
      return TRUE;
    }
    if ((int) $deck->getOwnerId() === $uid) {
      return TRUE;
    }
    if (!$deck->get('status')->value) {
      return FALSE;
    }
    $owner = $deck->getOwner();
    if (!$owner) {
      return TRUE;
    }
    return $owner->hasPermission('view lms_course') || $owner->hasPermission('administer lms_course');
  }

}
