<?php

declare(strict_types=1);

namespace Drupal\lms_notification;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserDataInterface;

/**
 * Manages dispatching notifications based on user preferences.
 */
class NotificationManager {

  /**
   * Constructs a NotificationManager object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected UserDataInterface $userData,
    protected LoggerChannelFactoryInterface $loggerFactory,
  ) {}

  /**
   * Dispatches a notification to a user.
   *
   * @param string $type
   *   The type of notification (e.g. 'enrollment', 'quiz_result').
   * @param \Drupal\Core\Session\AccountInterface $recipient
   *   The user receiving the notification.
   * @param string $title
   *   The title of the notification.
   * @param string $message
   *   The long message body.
   * @param string $link
   *   Optional URL link.
   * @param array $default_channels
   *   Default channels if user preference is not set.
   * @param \Drupal\Core\Entity\EntityInterface|null $reference
   *   Optional related entity.
   */
  public function dispatch(
    string $type,
    AccountInterface $recipient,
    string $title,
    string $message,
    string $link = '',
    array $default_channels = ['in_app'],
    ?EntityInterface $reference = NULL
  ): void {
    
    // Check user preferences.
    // If preference is unset, fallback to $default_channels.
    // Preference is saved as an array of selected channels: e.g. ['in_app', 'email'].
    $user_prefs = $this->userData->get('lms_notification', $recipient->id(), "pref_$type");
    $channels = $user_prefs ?? $default_channels;

    if (empty($channels) || !is_array($channels)) {
      // User opted out of this notification type entirely.
      return;
    }

    if (in_array('in_app', $channels, TRUE)) {
      $this->createInAppNotification($type, $recipient, $title, $message, $link, $reference);
    }

    if (in_array('email', $channels, TRUE)) {
      // Logic to queue the email (QueueWorker integration).
      $this->loggerFactory->get('lms_notification')
        ->info('Email notification triggered for user @uid: @title', [
          '@uid' => $recipient->id(),
          '@title' => $title,
        ]);
      // TODO: Implement Drupal queue creation for mail.
    }

    if (in_array('push', $channels, TRUE)) {
      // Foundation for Firebase Cloud Messaging (FCM).
      $this->loggerFactory->get('lms_notification_push')
        ->info('Push notification triggered for user @uid: @title', [
          '@uid' => $recipient->id(),
          '@title' => $title,
        ]);
    }
  }

  /**
   * Creates an in-app notification entity.
   */
  protected function createInAppNotification(
    string $type,
    AccountInterface $recipient,
    string $title,
    string $message,
    string $link,
    ?EntityInterface $reference
  ): void {
    $storage = $this->entityTypeManager->getStorage('lms_notification');
    
    $values = [
      'uid' => $recipient->id(),
      'field_type' => $type,
      'field_title' => $title,
      'field_message' => $message,
      'field_link' => $link,
      'field_channel' => ['in_app'],
      'field_read' => FALSE,
    ];

    if ($reference !== NULL) {
      $values['field_reference_type'] = $reference->getEntityTypeId();
      $values['field_reference_id'] = $reference->id();
    }

    /** @var \Drupal\lms_notification\Entity\Notification $notification */
    $notification = $storage->create($values);
    $notification->save();
  }

}
