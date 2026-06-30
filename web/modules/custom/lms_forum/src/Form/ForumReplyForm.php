<?php

declare(strict_types=1);

namespace Drupal\lms_forum\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lms_forum\Entity\ForumTopic;

/**
 * Form controller for adding/editing forum replies.
 */
class ForumReplyForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\lms_forum\Entity\ForumReply */
    $form = parent::buildForm($form, $form_state);

    // Hide internal fields.
    $form['field_upvotes']['#access'] = FALSE;
    $form['field_best_answer']['#access'] = FALSE;
    
    // Hide topic/parent target in UI because they are passed via code.
    $form['field_topic']['#access'] = FALSE;
    $form['field_parent']['#access'] = FALSE;

    $form['actions']['submit']['#value'] = $this->t('Post Reply');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Post Reply');

    // If editing or requested via modal, add AJAX behavior.
    if (!$this->entity->isNew() || \Drupal::request()->query->get('_wrapper_format') === 'drupal_modal') {
      $actions['submit']['#ajax'] = [
        'callback' => '::ajaxSaveCallback',
      ];
      if (isset($actions['delete'])) {
        unset($actions['delete']);
      }
      
      $actions['cancel'] = [
        '#type' => 'link',
        '#title' => $this->t('Cancel'),
        '#url' => \Drupal\Core\Url::fromRoute('<current>'),
        '#attributes' => [
          'class' => ['button', 'dialog-cancel'],
        ],
        '#weight' => 99,
      ];
    }

    return $actions;
  }

  /**
   * AJAX callback for saving the form inside a modal.
   */
  public function ajaxSaveCallback(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    if ($form_state->hasAnyErrors()) {
      $selector = '[data-drupal-selector="' . $form['#attributes']['data-drupal-selector'] . '"]';
      $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand($selector, $form));
      return $response;
    }

    $response->addCommand(new \Drupal\Core\Ajax\CloseModalDialogCommand());
    $entity = $form_state->getFormObject()->getEntity();
    
    // Instead of reloading or redirecting, dynamically update the text on the page!
    $body_markup = check_markup($entity->get('field_body')->value, $entity->get('field_body')->format);
    $response->addCommand(new \Drupal\Core\Ajax\HtmlCommand('#forum-reply-' . $entity->id() . ' .lms-forum-post__body', $body_markup));
    
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\lms_forum\Entity\ForumReply $entity */
    $entity = $this->entity;
    
    if ($entity->isNew() && empty($entity->getOwnerId())) {
      $entity->setOwnerId($this->currentUser()->id());
    }

    $status = parent::save($form, $form_state);

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('Reply posted successfully.'));

      /** @var \Drupal\lms_forum\Entity\ForumTopic $topic */
      $topic = $entity->get('field_topic')->entity;
      if ($topic) {
        // Update topic reply count and last reply date.
        $count = (int) $topic->get('field_replies_count')->value + 1;
        $topic->set('field_replies_count', $count);
        $topic->set('field_last_reply_date', time());
        $topic->save();
        Cache::invalidateTags([
          'lms_forum_topic_list',
          'lms_forum_reply_list',
          'lms_forum_topic:' . $topic->id(),
        ]);

        // Integrate logic for notification.
        if (\Drupal::moduleHandler()->moduleExists('lms_notification')) {
          try {
            $topic_author = $topic->getOwner();
            if ($topic_author && $topic_author->id() != $this->currentUser()->id()) {
               $dispatcher = \Drupal::service('lms_notification.manager');
               $dispatcher->dispatch(
                 'forum_reply',
                 $topic_author,
                 (string) $this->t('New reply on your topic'),
                 (string) $this->t('Someone replied to your topic: @title', ['@title' => $topic->label()]),
                 $topic->toUrl()->toString(),
                 ['in_app', 'email'],
                 $topic
               );
            }
          }
          catch (\Exception $e) {
             \Drupal::logger('lms_forum')->error('Failed to create notification: @message', ['@message' => $e->getMessage()]);
          }
        }

        $form_state->setRedirect('entity.lms_forum_topic.canonical', ['lms_forum_topic' => $topic->id()]);
      }
    }
    
    return $status;
  }

}
