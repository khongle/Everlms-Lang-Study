<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\State\StateInterface;

/**
 * Per-entity import journal for accurate --resume, plus a run lock.
 */
final class RunJournal {

  public function __construct(
    private readonly Connection $database,
    private readonly StateInterface $state,
    private readonly TimeInterface $time,
  ) {}

  public function open(string $run_id): void {
    // No-op; rows are inserted per unit via mark().
  }

  public function mark(string $run_id, string $uuid, string $langcode, string $state, ?string $hash = NULL): void {
    $now = $this->time->getRequestTime();
    $existing = $this->database->select('oci_journal', 'j')
      ->fields('j', ['id'])
      ->condition('run_id', $run_id)
      ->condition('entity_uuid', $uuid)
      ->condition('langcode', $langcode)
      ->execute()
      ->fetchField();
    if ($existing) {
      $this->database->update('oci_journal')
        ->fields(['state' => $state, 'source_hash' => $hash, 'changed' => $now])
        ->condition('id', $existing)
        ->execute();
    }
    else {
      $this->database->insert('oci_journal')
        ->fields([
          'run_id' => $run_id,
          'entity_uuid' => $uuid,
          'langcode' => $langcode,
          'state' => $state,
          'source_hash' => $hash,
          'created' => $now,
          'changed' => $now,
        ])
        ->execute();
    }
  }

  public function pending(string $run_id): array {
    return $this->database->select('oci_journal', 'j')
      ->fields('j', ['entity_uuid', 'langcode'])
      ->condition('run_id', $run_id)
      ->condition('state', ['done', 'skipped'], 'NOT IN')
      ->execute()
      ->fetchAll();
  }

  /**
   * The hash actually written for a unit by a given run (state = done), or NULL.
   *
   * Used by rollback (§11.1) to compare the LIVE entity against what the run it
   * undoes truly wrote — not against the snapshot's pre-image source_hash.
   */
  public function appliedHash(string $run_id, string $uuid, string $langcode): ?string {
    $hash = $this->database->select('oci_journal', 'j')
      ->fields('j', ['source_hash'])
      ->condition('run_id', $run_id)
      ->condition('entity_uuid', $uuid)
      ->condition('langcode', $langcode)
      ->condition('state', 'done')
      ->execute()
      ->fetchField();
    return $hash === FALSE ? NULL : (string) $hash;
  }

  public function states(string $run_id): array {
    return $this->database->select('oci_journal', 'j')
      ->fields('j', ['state'])
      ->condition('run_id', $run_id)
      ->execute()
      ->fetchCol();
  }

  public function lock(int $ttl): bool {
    $current = $this->state->get('open_content_improvement.run_lock');
    $now = $this->time->getRequestTime();
    if (is_array($current) && ($now - ($current['at'] ?? 0)) < $ttl) {
      return FALSE;
    }
    $this->state->set('open_content_improvement.run_lock', ['at' => $now]);
    return TRUE;
  }

  public function release(): void {
    $this->state->delete('open_content_improvement.run_lock');
  }
}
