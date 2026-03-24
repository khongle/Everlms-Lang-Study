<?php

declare(strict_types=1);

namespace Drupal\lms_forum\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lms_forum\Entity\ForumTopic;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\lms_forum\Entity\ForumReply;

/**
 * Forum pages: listing, topic detail, forms.
 */
final class ForumController extends ControllerBase {

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
        'author' => $author?->getDisplayName() ?? 'Anonymous',
        'created' => $topic->get('created')->value,
        'reply_count' => $reply_count,
        'views' => (int) $topic->get('field_views')->value,
        'resolved' => (bool) $topic->get('field_resolved')->value,
        'category' => $topic->get('field_category')->entity ? $topic->get('field_category')->entity->label() : '',
        'url' => $topic->toUrl()->toString(),
      ];
    }

    return [
      '#theme' => 'lms_forum_index',
      '#topics' => $items,
    ];
  }

  /**
   * Topic detail with replies.
   */
  public function viewTopic(ForumTopic $lms_forum_topic): array {
    throw new \Exception(
      "HARD DEBUG! \n" .
      "Entity ID: " . $lms_forum_topic->id() . "\n" .
      "Entity Label: " . var_export($lms_forum_topic->label(), true) . "\n" .
      "Entity Body: " . var_export($lms_forum_topic->get('field_body')->value, true) . "\n" .
      "Current UID: " . \Drupal::currentUser()->id() . "\n" .
      "Has Administer permission: " . (\Drupal::currentUser()->hasPermission('administer lms_course') ? 'YES' : 'NO')
    );

    $views = (int) $lms_forum_topic->get('field_views')->value + 1;
    $lms_forum_topic->set('field_views', $views);
    $lms_forum_topic->save();

    $author = $lms_forum_topic->getOwner();

    // Load replies.
    $reply_ids = $this->entityTypeManager->getStorage('lms_forum_reply')
      ->getQuery()
      ->condition('field_topic', $lms_forum_topic->id())
      ->sort('created', 'ASC')
      ->accessCheck(FALSE)
      ->execute();
    $replies_entities = $reply_ids
      ? $this->entityTypeManager->getStorage('lms_forum_reply')->loadMultiple($reply_ids)
      : [];

    $replies = [];
    foreach ($replies_entities as $reply) {
      $reply_author = $reply->getOwner();
      $replies[] = [
        'id' => $reply->id(),
        'body' => $reply->get('field_body')->value ?? '',
        'author' => $reply_author?->getDisplayName() ?? 'Anonymous',
        'created' => $reply->get('created')->value,
        'is_answer' => (bool) $reply->get('field_best_answer')->value,
        'mark_best_url' => Url::fromRoute('lms_forum.reply.best_answer', [
           'lms_forum_topic' => $lms_forum_topic->id(),
           'lms_forum_reply' => $reply->id(),
        ])->toString(),
      ];
    }

    $new_reply = $this->entityTypeManager->getStorage('lms_forum_reply')->create([
      'field_topic' => $lms_forum_topic->id(),
    ]);
    $reply_form = \Drupal::service('entity.form_builder')->getForm($new_reply, 'default');

    \Drupal::messenger()->addMessage("DEBUG Title: " . $lms_forum_topic->label());
    \Drupal::messenger()->addMessage("DEBUG Form Keys: " . implode(', ', array_keys($reply_form)));

    return [
      '#theme' => 'lms_forum_topic',
      '#topic' => [
        'id' => $lms_forum_topic->id(),
        'title' => $lms_forum_topic->label(),
        'body' => $lms_forum_topic->get('field_body')->value ?? '',
        'author' => $author?->getDisplayName() ?? 'Anonymous',
        'owner_id' => $author ? $author->id() : 0,
        'created' => $lms_forum_topic->get('created')->value,
        'resolved' => (bool) $lms_forum_topic->get('field_resolved')->value,
      ],
      '#replies' => $replies,
      '#reply_form' => $reply_form,
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

    return $this->redirect('lms_forum.topic', ['lms_forum_topic' => $lms_forum_topic->id()]);
  }
}
