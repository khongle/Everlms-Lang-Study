<?php

declare(strict_types=1);

namespace Drupal\lms_course\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for Course entity add/edit.
 */
final class CourseForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();
    $message_args = ['%label' => $entity->label()];

    $this->messenger()->addStatus(
      $result === SAVED_NEW
        ? $this->t('Course %label has been created.', $message_args)
        : $this->t('Course %label has been updated.', $message_args)
    );

    $form_state->setRedirectUrl($entity->toUrl('collection'));

    return $result;
  }

}
