<?php

declare(strict_types=1);

namespace Drupal\lms_forum\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lms_forum\Entity\ForumTopic;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\lms_forum\Entity\ForumReply;
use Drupal\Core\Url;

/**
 * Forum pages: listing, topic detail, forms.
 */
final class LmsForumController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * Helper to get user display info.
   */
  private function getUserInfo($user): array {
    if (!$user || $user->isAnonymous()) {
      return ['name' => 'Anonymous', 'avatar' => ''];
    }
    
    $first_name = $user->hasField('field_first_name') ? $user->get('field_first_name')->value : '';
    $last_name = $user->hasField('field_last_name') ? $user->get('field_last_name')->value : '';
    $name = trim("$first_name $last_name");
    if (empty($name)) {
      $name = $user->getDisplayName();
    }
    
    $avatar_url = '';
    if ($user->hasField('user_picture') && !$user->get('user_picture')->isEmpty()) {
      $file = $user->get('user_picture')->entity;
      if ($file) {
        $avatar_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
      }
    }
    
    return ['name' => $name, 'avatar' => $avatar_url];
  }

  /**
   * Topic creation form.
   */
  public function addTopic(): array {
    $topic = $this->entityTypeManager->getStorage('lms_forum_topic')->create([]);
    $form = \Drupal::service('entity.form_builder')->getForm($topic, 'default');

    return [
      '#theme' => 'lms_forum_new_topic_page',
      '#form' => $form,
      '#title' => $this->t('Create New Topic'),
    ];
  }

  /**
   * Forum index — list topics.
   */
  public function index(): array {
    $ids = $this->entityTypeManager->getStorage('lms_forum_topic')
      ->getQuery()
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $topics = $ids
      ? $this->entityTypeManager->getStorage('lms_forum_topic')->loadMultiple($ids)
      : [];

    $items = [];
    foreach ($topics as $topic) {
      $author = $topic->getOwner();
      $author_info = $this->getUserInfo($author);
      
      // We can use field_replies_count if populated, otherwise fallback
      $reply_count = (int) $topic->get('field_replies_count')->value;
      if (!$reply_count) {
        $reply_count = (int) $this->entityTypeManager->getStorage('lms_forum_reply')
          ->getQuery()
          ->condition('field_topic', $topic->id())
          ->accessCheck(FALSE)
          ->count()
          ->execute();
      }

      $items[] = [
        'id' => $topic->id(),
        'title' => $topic->label(),
        'body' => mb_substr(strip_tags($topic->get('field_body')->value ?? ''), 0, 200),
        'author' => $author_info['name'],
        'author_avatar' => $author_info['avatar'],
        'created' => $topic->get('created')->value,
        'reply_count' => $reply_count,
        'views' => (int) $topic->get('field_views')->value,
        'resolved' => (bool) $topic->get('field_resolved')->value,
        'category' => $topic->get('field_category')->entity ? $topic->get('field_category')->entity->label() : '',
        'url' => $topic->toUrl()->toString(),
      ];
    }

    $new_topic_form = NULL;
    if ($this->currentUser->isAuthenticated()) {
      $new_topic = $this->entityTypeManager->getStorage('lms_forum_topic')->create([]);
      $new_topic_form = \Drupal::service('entity.form_builder')->getForm($new_topic, 'default');
    }

    return [
      '#theme' => 'lms_forum_index',
      '#topics' => $items,
      '#new_topic_form' => $new_topic_form,
    ];
  }

  /**
   * Topic detail with replies.
   */
  public function viewTopic(ForumTopic $lms_forum_topic): array {

    $views = (int) $lms_forum_topic->get('field_views')->value + 1;
    $lms_forum_topic->set('field_views', $views);
    $lms_forum_topic->save();

    $author = $lms_forum_topic->getOwner();
    $author_info = $this->getUserInfo($author);

    // Determine Teaser Mode
    $is_anonymous = $this->currentUser->isAnonymous();

    // Fast reply count for teaser
    $total_replies = (int) $lms_forum_topic->get('field_replies_count')->value;
    if (!$total_replies) {
      $total_replies = (int) $this->entityTypeManager->getStorage('lms_forum_reply')
        ->getQuery()
        ->condition('field_topic', $lms_forum_topic->id())
        ->accessCheck(FALSE)
        ->count()
        ->execute();
    }

    $replies = [];
    $reply_form = NULL;

    if (!$is_anonymous) {
      // Load replies only for authenticated users.
      $reply_ids = $this->entityTypeManager->getStorage('lms_forum_reply')
        ->getQuery()
        ->condition('field_topic', $lms_forum_topic->id())
        ->sort('created', 'ASC')
        ->accessCheck(FALSE)
        ->execute();
      $replies_entities = $reply_ids
        ? $this->entityTypeManager->getStorage('lms_forum_reply')->loadMultiple($reply_ids)
        : [];

      foreach ($replies_entities as $reply) {
        $reply_author = $reply->getOwner();
        $reply_author_info = $this->getUserInfo($reply_author);
        $replies[] = [
          'id' => $reply->id(),
          'body' => $reply->get('field_body')->value ?? '',
          'author' => $reply_author_info['name'],
          'author_avatar' => $reply_author_info['avatar'],
          'created' => $reply->get('created')->value,
          'is_answer' => (bool) $reply->get('field_best_answer')->value,
          'mark_best_url' => Url::fromRoute('lms_forum.reply.best_answer', [
             'lms_forum_topic' => $lms_forum_topic->id(),
             'lms_forum_reply' => $reply->id(),
          ])->toString(),
          'edit_url' => $reply->access('update', $this->currentUser) ? Url::fromRoute('lms_forum.reply.edit', ['lms_forum_reply' => $reply->id()])->toString() : '',
          'delete_url' => $reply->access('delete', $this->currentUser) ? $reply->toUrl('delete-form')->toString() : '',
        ];
      }

      $new_reply = $this->entityTypeManager->getStorage('lms_forum_reply')->create([
        'field_topic' => $lms_forum_topic->id(),
      ]);

      // Force explicit form build
      $reply_form = \Drupal::service('entity.form_builder')->getForm($new_reply, 'default');
    }

    \Drupal::messenger()->addMessage("FRESH CONTROLLER LOADED! Found Title: " . $lms_forum_topic->label());

    return [
      '#theme' => 'lms_forum_topic',
      '#topic' => [
        'id' => $lms_forum_topic->id(),
        'title' => $lms_forum_topic->label(),
        'body' => $lms_forum_topic->get('field_body')->value ?? '',
        'author' => $author_info['name'],
        'author_avatar' => $author_info['avatar'],
        'owner_id' => $author ? $author->id() : 0,
        'created' => $lms_forum_topic->get('created')->value,
        'resolved' => (bool) $lms_forum_topic->get('field_resolved')->value,
      ],
      '#replies' => $replies,
      '#reply_form' => $reply_form,
      '#is_teaser_mode' => $is_anonymous,
      '#total_replies' => $total_replies,
    ];
  }

  /**
   * Standalone reply page.
   */
  public function reply(ForumTopic $lms_forum_topic) {
    $reply = $this->entityTypeManager->getStorage('lms_forum_reply')->create([
      'field_topic' => $lms_forum_topic->id(),
    ]);
    return \Drupal::service('entity.form_builder')->getForm($reply, 'default');
  }

  /**
   * Mark best answer.
   */
  public function markBestAnswer(ForumTopic $lms_forum_topic, ForumReply $lms_forum_reply) {
    if ($lms_forum_topic->getOwnerId() == $this->currentUser->id() || $this->currentUser->hasPermission('administer lms_course')) {
      $lms_forum_reply->set('field_best_answer', TRUE);
      $lms_forum_reply->save();

      $lms_forum_topic->set('field_resolved', TRUE);
      $lms_forum_topic->save();

      $this->messenger()->addMessage($this->t('Marked as best answer.'));
    }
    else {
      $this->messenger()->addError($this->t('You do not have permission to do this.'));
    }

    return $this->redirect('entity.lms_forum_topic.canonical', ['lms_forum_topic' => $lms_forum_topic->id()]);
  }

  /**
   * Edit reply form wrapper.
   */
  public function editReply(\Drupal\lms_forum\Entity\ForumReply $lms_forum_reply, \Symfony\Component\HttpFoundation\Request $request) {
    $form = \Drupal::service('entity.form_builder')->getForm($lms_forum_reply, 'default');
    
    // If requested via AJAX modal, just return the form array.
    if ($request->query->get('_wrapper_format') === 'drupal_modal') {
      return $form;
    }

    return [
      '#theme' => 'lms_forum_edit_reply_page',
      '#form' => $form,
      '#title' => $this->t('Edit Reply'),
    ];
  }

}
