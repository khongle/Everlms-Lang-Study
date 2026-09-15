<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_listening\Entity\ListeningPassage;

/**
 * Decides which listening passages a given student may see.
 *
 * Mirrors lms_reading's ReadingVisibilityService exactly: a passage is
 * visible if owned by the viewer, or published with a staff owner — AND
 * its source deck must also be visible to the same user, so a passage can
 * never leak vocabulary from a deck the student isn't allowed to study in
 * the first place. This only reads lms_flashcard_deck through the entity
 * API; it never touches lms_flashcard's own code.
 */
final class ListeningVisibilityService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Whether a passage should be visible/listenable for a given user.
   */
  public function isPassageVisibleTo(ListeningPassage $passage, int $uid): bool {
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
   * Same shape as ListeningPassage's own check, kept private since only
   * isPassageVisibleTo() (which also checks the deck) should be called
   * from outside this service.
   */
  private function isOwnedOrPublishedByStaff(ListeningPassage $passage, int $uid): bool {
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
   * calling into lms_flashcard's own services) so lms_listening only ever
   * depends on lms_flashcard's *entities*, never its services — a smaller,
   * more stable surface to depend on. Identical logic to
   * ReadingVisibilityService::isDeckVisibleTo(), kept as an independent
   * copy per this module's independence from lms_reading's own code.
   */
  private function isDeckVisibleTo(EntityInterface $deck, int $uid): bool {
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
