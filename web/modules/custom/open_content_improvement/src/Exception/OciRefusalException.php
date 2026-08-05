<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Exception;

/**
 * Refuses one file/unit; the run continues with that unit skipped.
 */
final class OciRefusalException extends \RuntimeException {

  public function __construct(
    string $message,
    public readonly string $code_id = 'refused',
    public readonly bool $hard = TRUE,
  ) {
    parent::__construct($message);
  }
}
