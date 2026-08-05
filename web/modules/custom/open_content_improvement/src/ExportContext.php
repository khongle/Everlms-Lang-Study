<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement;

/**
 * Immutable-ish scope + options for a single export run.
 */
final class ExportContext {

  public function __construct(
    public ?array $entityTypes = NULL,
    public ?array $bundles = NULL,
    public ?array $langcodes = NULL,
    public bool $includeBinaries = TRUE,
    public ?string $targetDir = NULL,
    public string $runId = '',
    public bool $acknowledgePublic = FALSE,
  ) {}
}
