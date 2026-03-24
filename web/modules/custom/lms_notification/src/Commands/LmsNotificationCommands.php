<?php

declare(strict_types=1);

namespace Drupal\lms_notification\Commands;

use Drupal\lms_notification\NotificationManager;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile for LMS Notifications.
 */
class LmsNotificationCommands extends DrushCommands {

  /**
   * Constructs a new LmsNotificationCommands object.
   */
  public function __construct(
    protected NotificationManager $notificationManager,
  ) {
    parent::__construct();
  }

  /**
   * Send reminder notifications to inactive users.
   *
   * @command lms_notification:remind-inactive
   * @aliases lms-remind
   * @usage lms_notification:remind-inactive
   *   Sends reminders to users who haven't logged in for 7 days.
   */
  public function remindInactive(): void {
    $this->output()->writeln('Scanning for inactive users...');

    $timestamp_7_days_ago = \Drupal::time()->getRequestTime() - (7 * 24 * 60 * 60);
    $timestamp_14_days_ago = \Drupal::time()->getRequestTime() - (14 * 24 * 60 * 60);

    // Users who accessed before 7 days ago but after 14 days ago.
    $query = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('access', $timestamp_7_days_ago, '<')
      ->condition('access', $timestamp_14_days_ago, '>')
      ->accessCheck(FALSE)
      ->range(0, 50);

    $uids = $query->execute();

    if (empty($uids)) {
      $this->logger()->success('No inactive users found.');
      return;
    }

    $users = \Drupal::entityTypeManager()->getStorage('user')->loadMultiple($uids);
    $count = 0;

    foreach ($users as $user) {
      $title = 'We miss you!';
      $message = 'It has been a while since you last studied. Come back and continue your courses.';
      $this->notificationManager->dispatch('reminder', $user, $title, $message, '/my-courses');
      $count++;
    }

    $this->logger()->success("Sent reminders to $count users.");
  }

}
