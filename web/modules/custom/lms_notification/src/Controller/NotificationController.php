<?php

declare(strict_types=1);

namespace Drupal\lms_notification\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    $ids = $this->entityTypeManager->getStorage('lms_notification')
      ->getQuery()
      ->condition('uid', $uid)
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $notifications = $ids
      ? $this->entityTypeManager->getStorage('lms_notification')->loadMultiple($ids)
      : [];

    $items = [];
    foreach ($notifications as $notif) {
      $items[] = [
        'id' => $notif->id(),
        'type' => $notif->get('field_type')->value,
        'title' => $notif->get('field_title')->value,
        'message' => $notif->get('field_message')->value ?? '',
        'link' => $notif->get('field_link')->value ?? '',
        'read' => (bool) $notif->get('field_read')->value,
        'created' => $notif->get('created')->value,
      ];
    }

    return [
      '#theme' => 'lms_notifications',
      '#notifications' => $items,
    ];
  }

}
