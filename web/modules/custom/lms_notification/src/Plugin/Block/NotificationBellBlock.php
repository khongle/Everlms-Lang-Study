<?php

declare(strict_types=1);

namespace Drupal\lms_notification\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Notification Bell' block.
 *
 * @Block(
 *   id = "lms_notification_bell_block",
 *   admin_label = @Translation("Notification Bell"),
 *   category = @Translation("LMS")
 * )
 */
class NotificationBellBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs the NotificationBellBlock.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected AccountProxyInterface $currentUser,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $uid = $this->currentUser->id();
    
    if (empty($uid)) {
      return [];
    }

    $storage = $this->entityTypeManager->getStorage('lms_notification');
    $query = $storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_read', FALSE)
      ->condition('field_channel', 'in_app')
      ->accessCheck(FALSE);
    $count = $query->count()->execute();

    // Fetch top 5 latest notifications for the dropdown.
    $latest_query = $storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_channel', 'in_app')
      ->sort('created', 'DESC')
      ->range(0, 5)
      ->accessCheck(FALSE);
    $latest_ids = $latest_query->execute();
    
    $latest_notifications = [];
    if (!empty($latest_ids)) {
      $entities = $storage->loadMultiple($latest_ids);
      foreach ($entities as $notif) {
        $latest_notifications[] = [
          'id' => $notif->id(),
          'type' => $notif->get('field_type')->value,
          'title' => $notif->get('field_title')->value,
          'message' => $notif->get('field_message')->value ?? '',
          'link' => $notif->get('field_link')->value ?? '',
          'read' => (bool) $notif->get('field_read')->value,
          'created' => $notif->get('created')->value,
        ];
      }
    }

    return [
      '#theme' => 'lms_notification_bell',
      '#unread_count' => $count,
      '#notifications_url' => Url::fromRoute('lms_notification.list')->toString(),
      '#latest_notifications' => $latest_notifications,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['lms_notification_list'],
      ],
    ];
  }

}
