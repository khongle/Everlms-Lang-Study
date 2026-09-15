<?php

declare(strict_types=1);

namespace Drupal\lms_reading\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for the Reading Passage entity.
 *
 * Manual editing is a fallback for one-off fixes, same as CardForm/DeckForm
 * in lms_flashcard — the JSON importer (ReadingImportForm) is the primary
 * authoring flow, especially for field_questions/field_context_items,
 * which reference entities with no add-form of their own.
 */
final class ReadingPassageForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);
    $entity = $this->getEntity();
    $this->messenger()->addStatus(
      $result === SAVED_NEW
        ? $this->t('Reading passage @label created.', ['@label' => $entity->label()])
        : $this->t('Reading passage @label updated.', ['@label' => $entity->label()])
    );
    $form_state->setRedirect('lms_reading.my_passages');
    return $result;
  }

}
