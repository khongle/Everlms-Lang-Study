<?php

declare(strict_types=1);

namespace Drupal\open_content_improvement;

/**
 * The internal, fine-grained classification result of a field.
 *
 * Content and System map to the editable block layout; File places into the
 * _files block; Reference places into _system as {label, ref}; Skip is dropped.
 */
enum FieldSurface: string {
  case Content = 'content';
  case System = 'system';
  case File = 'file';
  case Reference = 'reference';
  case Skip = 'skip';

  /**
   * The coarse block-level surface an operator may force via config.
   */
  public function blockSurface(): string {
    return match ($this) {
      self::Content => 'content',
      self::File, self::Reference, self::System => 'system',
      self::Skip => 'skip',
    };
  }
}
