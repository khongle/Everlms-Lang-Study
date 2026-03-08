<?php

declare(strict_types=1);

namespace Drupal\lms_question\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Question Type (bundle) config entity.
 */
#[ConfigEntityType(
  id: 'lms_question_type',
  label: new TranslatableMarkup('Question Type'),
  admin_permission: 'administer lms_course',
  config_prefix: 'question_type',
  bundle_of: 'lms_question',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
  ],
  config_export: [
    'id',
    'label',
    'description',
  ],
)]
class QuestionType extends ConfigEntityBundleBase {

  /**
   * The question type ID.
   */
  protected string $id;

  /**
   * The question type label.
   */
  protected string $label;

  /**
   * Description.
   */
  protected string $description = '';

}
