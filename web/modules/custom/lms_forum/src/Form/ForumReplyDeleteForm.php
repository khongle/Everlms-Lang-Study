<?php

namespace Drupal\lms_forum\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting a Forum Reply.
 */
class ForumReplyDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this reply?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    /** @var \Drupal\lms_forum\Entity\ForumReply $entity */
    $entity = $this->entity;
    $topic_id = $entity->get('field_topic')->target_id;
    
    // Redirect back to the topic.
    if ($topic_id) {
      return Url::fromUserInput('/forum/' . $topic_id);
    }
    
    return Url::fromRoute('lms_forum.index');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRedirectUrl() {
    return $this->getCancelUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    
    // Wire up the submit button to use AJAX so it works smoothly in the modal.
    if (isset($form['actions']['submit'])) {
      $form['actions']['submit']['#ajax'] = [
        'callback' => '::ajaxDeleteCallback',
      ];
      $form['actions']['submit']['#attributes']['class'][] = 'button--danger';
    }
    
    // Wire the cancel link to simply close the dialog.
    if (isset($form['actions']['cancel'])) {
      $form['actions']['cancel']['#attributes']['class'][] = 'button';
      $form['actions']['cancel']['#attributes']['class'][] = 'dialog-cancel';
    }
    
    return $form;
  }

  /**
   * AJAX callback for the delete form.
   */
  public function ajaxDeleteCallback(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    if ($form_state->hasAnyErrors()) {
      return $form;
    }
    
    $response->addCommand(new \Drupal\Core\Ajax\CloseModalDialogCommand());
    
    // Dynamically remove the reply from the DOM without reloading!
    $deleted_id = $form_state->get('deleted_reply_id') ?: $this->getEntity()->id();
    $response->addCommand(new \Drupal\Core\Ajax\RemoveCommand('#forum-reply-' . $deleted_id));
    
    return $response;
  }

}
