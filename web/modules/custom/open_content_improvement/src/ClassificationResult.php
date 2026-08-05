<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement;

/**
 * Value object: the surface a field resolved to, why, and advisory annotations.
 */
final class ClassificationResult {

  public function __construct(
    public readonly FieldSurface $surface,
    public readonly string $reason,
    public readonly array $annotations = [],
    public readonly array $columnSurfaces = [],
  ) {}

  public function isEditable(): bool {
    return $this->surface === FieldSurface::Content;
  }

  public function withAnnotations(array $annotations): self {
    return new self($this->surface, $this->reason, $annotations + $this->annotations, $this->columnSurfaces);
  }
}
