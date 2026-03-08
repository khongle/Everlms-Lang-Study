<?php

declare(strict_types=1);

namespace Drupal\lms_base\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Shared helper service for EverLMS modules.
 */
final class LmsHelper {

  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    private AccountProxyInterface $currentUser,
  ) {}

  /**
   * Get current user ID.
   */
  public function getCurrentUserId(): int {
    return (int) $this->currentUser->id();
  }

  /**
   * Check if current user has a specific role.
   */
  public function currentUserHasRole(string $role): bool {
    return in_array($role, $this->currentUser->getRoles(), TRUE);
  }

  /**
   * Load taxonomy terms by vocabulary.
   *
   * @return \Drupal\taxonomy\TermInterface[]
   */
  public function getTermsByVocabulary(string $vid): array {
    /** @var \Drupal\taxonomy\TermStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    return $storage->loadTree($vid, 0, NULL, TRUE);
  }

  /**
   * Get term name by ID.
   */
  public function getTermName(int $tid): string {
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
    return $term instanceof TermInterface ? $term->getName() : '';
  }

  /**
   * Format duration in minutes to human-readable string.
   */
  public function formatDuration(int $minutes): string {
    if ($minutes < 60) {
      return $minutes . 'm';
    }
    $hours = intdiv($minutes, 60);
    $remaining = $minutes % 60;
    return $remaining > 0 ? "{$hours}h {$remaining}m" : "{$hours}h";
  }

  /**
   * Generate a URL-safe slug from a string.
   */
  public function generateSlug(string $string): string {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
  }

}
