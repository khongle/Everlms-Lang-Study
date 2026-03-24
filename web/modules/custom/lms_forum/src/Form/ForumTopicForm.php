<?php

declare(strict_types=1);

namespace Drupal\lms_forum\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form controller for the forum topic entity edit forms.
 */
class ForumTopicForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\lms_forum\Entity\ForumTopic */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    // Check if we are creating within a course context (e.g., from URL parm)
    // For now, if the user doesn't have course admin, we might only allow
    // them to post in the global forum or their enrolled courses.
    // Basic implementation: let the field_course handle widget access,
    // or just leave it for instructors.

    // Hide internal fields from user.
    $form['field_replies_count']['#access'] = FALSE;
    $form['field_last_reply_date']['#access'] = FALSE;
    $form['field_views']['#access'] = FALSE;
    $form['field_resolved']['#access'] = FALSE;
    
    // Only admins/instructors can lock/pin usually, but we check permissions
    if (!$this->currentUser()->hasPermission('administer lms_course')) {
      $form['field_pinned']['#access'] = FALSE;
      $form['field_locked']['#access'] = FALSE;
    }

    // After build to hide text formats
    $form['#after_build'][] = '::hideTextFormat';

    return $form;
  }

  /**
   * Hide format guidelines and selector.
   */
  public function hideTextFormat(array $form, FormStateInterface $form_state) {
    if (isset($form['field_body']['widget'][0]['format'])) {
      $form['field_body']['widget'][0]['format']['#access'] = FALSE;
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    
    // Ensure owner is set to current user if new.
    if ($entity->isNew() && empty($entity->getOwnerId())) {
      $entity->setOwnerId($this->currentUser()->id());
    }

    $status = parent::save($form, $form_state);

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('Tạo bài viết %label thành công.', [
        '%label' => $entity->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('Cập nhật bài viết %label thành công.', [
        '%label' => $entity->label(),
      ]));
    }

    $form_state->setRedirect('entity.lms_forum_topic.canonical', ['lms_forum_topic' => $entity->id()]);
    return $status;
  }

}
