<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for the Listening Passage entity.
 *
 * Manual editing is a fallback for one-off fixes, same as
 * ReadingPassageForm — the JSON importer (ListeningImportForm) is the
 * primary authoring flow, especially for field_questions, which references
 * entities with no add-form of their own.
 */
final class ListeningPassageForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);
    $entity = $this->getEntity();
    $this->messenger()->addStatus(
      $result === SAVED_NEW
        ? $this->t('Listening passage @label created.', ['@label' => $entity->label()])
        : $this->t('Listening passage @label updated.', ['@label' => $entity->label()])
    );
    $form_state->setRedirect('lms_listening.my_passages');
    return $result;
  }

}
