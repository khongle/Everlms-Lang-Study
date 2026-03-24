<?php

declare(strict_types=1);

namespace Drupal\lms_notification\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Notification pages.
 */
final class NotificationController extends ControllerBase {

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
   * List notifications for current user.
   */
  public function listNotifications(): array {
    $uid = (int) $this->currentUser->id();
    $request = \Drupal::request();
    $type_filter = $request->query->get('type', 'all');

    $query = $this->entityTypeManager->getStorage('lms_notification')
      ->getQuery()
      ->condition('uid', $uid)
      ->sort('created', 'DESC')
      ->pager(15)
      ->accessCheck(FALSE);

    if ($type_filter !== 'all') {
      $query->condition('field_type', $type_filter);
    }

    $ids = $query->execute();
    $notifications = $ids
      ? $this->entityTypeManager->getStorage('lms_notification')->loadMultiple($ids)
      : [];

    $items = [];
    foreach ($notifications as $notif) {
      $is_unread = empty($notif->get('field_read')->value);
      
      $items[] = [
        'id' => $notif->id(),
        'type' => $notif->get('field_type')->value,
        'title' => $notif->get('field_title')->value,
        'message' => $notif->get('field_message')->value ?? '',
        'link' => $notif->get('field_link')->value ?? '',
        'read' => !$is_unread, // Preserve the unread visual state for this immediate page load.
        'created' => $notif->get('created')->value,
      ];

      // Mark as read so the bell counter resets immediately.
      if ($is_unread) {
        $notif->set('field_read', TRUE);
        $notif->save();
      }
    }

    return [
      '#theme' => 'lms_notifications',
      '#notifications' => $items,
      '#current_type' => $type_filter,
      '#pager' => ['#type' => 'pager'],
      '#cache' => [
        'contexts' => ['user', 'url.query_args'],
        'tags' => ['lms_notification_list'],
        'max-age' => 0,
      ],
    ];
  }

  /**
   * API: Get unread count.
   */
  public function apiUnreadCount(): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (empty($uid)) {
      return new JsonResponse(['count' => 0]);
    }

    $count = $this->entityTypeManager->getStorage('lms_notification')
      ->getQuery()
      ->condition('uid', $uid)
      ->condition('field_read', FALSE)
      ->condition('field_channel', 'in_app')
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return new JsonResponse(['count' => (int) $count]);
  }

}
