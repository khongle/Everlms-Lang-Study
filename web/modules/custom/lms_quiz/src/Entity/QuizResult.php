<?php

declare(strict_types=1);

namespace Drupal\lms_quiz\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

/**
 * Defines the Quiz Result entity.
 */
#[ContentEntityType(
  id: 'lms_quiz_result',
  label: new TranslatableMarkup('Quiz Result'),
  label_collection: new TranslatableMarkup('Quiz Results'),
  base_table: 'lms_quiz_result',
  admin_permission: 'administer lms_course',
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'owner' => 'uid',
  ],
  handlers: [
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'default' => 'Drupal\Core\Entity\ContentEntityForm',
      'delete' => 'Drupal\Core\Entity\ContentEntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'access' => 'Drupal\lms_quiz\Access\QuizAccessControlHandler',
  ],
  links: [
    'canonical' => '/manage/quiz-results/{lms_quiz_result}',
    'collection' => '/manage/quiz-results',
  ],
)]
class QuizResult extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['field_quiz'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Quiz'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'lms_quiz')
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_score'] = BaseFieldDefinition::create('decimal')
      ->setLabel(new TranslatableMarkup('Score (%)'))
      ->setDefaultValue(0)
      ->setSetting('precision', 5)
      ->setSetting('scale', 2)
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_result'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Result'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['quiz_results' => 'quiz_results']])
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_responses'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Responses'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'lms_question_response')
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_start_time'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('Start Time'));

    $fields['field_end_time'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(new TranslatableMarkup('End Time'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'));

    return $fields;
  }

}
