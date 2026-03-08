<?php

declare(strict_types=1);

namespace Drupal\lms_question\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for Question entity.
 */
final class QuestionForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);
    $entity = $this->getEntity();
    $this->messenger()->addStatus(
      $result === SAVED_NEW
        ? $this->t('Question %label created.', ['%label' => $entity->label()])
        : $this->t('Question %label updated.', ['%label' => $entity->label()])
    );
    $form_state->setRedirectUrl($entity->toUrl('collection'));
    return $result;
  }

}
