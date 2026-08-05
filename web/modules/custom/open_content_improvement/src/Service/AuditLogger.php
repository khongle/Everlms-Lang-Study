<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;

/**
 * Append-only audit log: one row per changed field-delta.
 */
final class AuditLogger {

  public function __construct(
    private readonly Connection $database,
    private readonly TimeInterface $time,
  ) {}

  public function log(array $row): void {
    $this->database->insert('oci_audit')
      ->fields([
        'run_id' => $row['run_id'] ?? '',
        'entity_type' => $row['entity_type'] ?? '',
        'entity_id' => $row['entity_id'] ?? NULL,
        'entity_uuid' => $row['entity_uuid'] ?? '',
        'langcode' => $row['langcode'] ?? '',
        'field_name' => $row['field_name'] ?? '',
        'delta' => (int) ($row['delta'] ?? 0),
        'action' => $row['action'] ?? 'update',
        'actor' => $row['actor'] ?? NULL,
        'reason' => $row['reason'] ?? NULL,
        'created' => $this->time->getRequestTime(),
      ])
      ->execute();
  }

  public function forRun(string $run_id): array {
    return $this->database->select('oci_audit', 'a')
      ->fields('a')
      ->condition('run_id', $run_id)
      ->orderBy('id')
      ->execute()
      ->fetchAll();
  }
}
