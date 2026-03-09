<?php

declare(strict_types=1);

namespace Drupal\lms_forum\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lms_forum\Entity\ForumTopic;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Forum pages: listing, topic detail.
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
      // Count replies.
      $reply_count = (int) $this->entityTypeManager->getStorage('lms_forum_reply')
        ->getQuery()
        ->condition('field_topic', $topic->id())
        ->accessCheck(FALSE)
        ->count()
        ->execute();

      $items[] = [
        'id' => $topic->id(),
        'title' => $topic->label(),
        'body' => mb_substr(strip_tags($topic->get('field_body')->value ?? ''), 0, 200),
        'author' => $author?->getDisplayName() ?? 'Anonymous',
        'created' => $topic->get('created')->value,
        'reply_count' => $reply_count,
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
        'body' => $reply->get('field_body')->value ?? '',
        'author' => $reply_author?->getDisplayName() ?? 'Anonymous',
        'created' => $reply->get('created')->value,
      ];
    }

    return [
      '#theme' => 'lms_forum_topic',
      '#topic' => [
        'title' => $lms_forum_topic->label(),
        'body' => $lms_forum_topic->get('field_body')->value ?? '',
        'author' => $author?->getDisplayName() ?? 'Anonymous',
        'created' => $lms_forum_topic->get('created')->value,
      ],
      '#replies' => $replies,
    ];
  }

}
