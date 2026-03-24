<?php

declare(strict_types=1);

namespace Drupal\lms_lesson\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for Lesson entity.
 */
final class LessonForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);
    $entity = $this->getEntity();
    $this->messenger()->addStatus(
      $result === SAVED_NEW
        ? $this->t('Lesson %label created.', ['%label' => $entity->label()])
        : $this->t('Lesson %label updated.', ['%label' => $entity->label()])
    );
    // Try to redirect to canonical or edit form instead of collection
    try {
      $form_state->setRedirectUrl($entity->toUrl('edit-form'));
    } catch (\Exception $e) {
      // Fallback
    }
    return $result;
  }

}
