<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement;

/**
 * Immutable-ish scope + options for a single import run.
 */
final class ImportContext {

  public function __construct(
    public string $rootDir,
    public bool $apply = FALSE,
    public ?array $entityTypes = NULL,
    public string $runId = '',
    public array $forceUuids = [],
    public string $reason = '',
    public string $actor = '',
    public ?int $confirmLargeChange = NULL,
    public bool $allowForeignSite = FALSE,
    public bool $acceptStaleTree = FALSE,
    public bool $bypassEntityAccess = FALSE,
    public ?string $resume = NULL,
    // When set, this run is the undo of run R: staleness is judged against
    // {oci_journal}.applied_hash of R (§11.1), not the snapshot source_hash.
    public string $rollbackOfRun = '',
  ) {}
}
