<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an AI Provider item annotation object.
 *
 * @Annotation
 */
class AiProvider extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the provider.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * A brief description of the provider.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;

}
