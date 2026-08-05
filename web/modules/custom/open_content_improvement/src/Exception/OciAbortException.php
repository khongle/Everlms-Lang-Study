<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement\Exception;

/**
 * Aborts the whole run.
 */
final class OciAbortException extends \RuntimeException {

  public function __construct(
    string $message,
    public readonly string $code_id = 'aborted',
  ) {
    parent::__construct($message);
  }
}
