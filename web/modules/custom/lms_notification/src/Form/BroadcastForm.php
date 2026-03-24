<?php

declare(strict_types=1);

namespace Drupal\lms_notification\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lms_notification\NotificationManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for sending broadcast announcements to all users.
 */
class BroadcastForm extends FormBase {

  /**
   * Constructs a BroadcastForm.
   */
  public function __construct(
    protected NotificationManager $notificationManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('lms_notification.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_notification_broadcast_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#description' => $this->t('The title of the announcement.'),
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#required' => TRUE,
      '#description' => $this->t('The details of the announcement.'),
    ];

    $form['link'] = [
      '#type' => 'url',
      '#title' => $this->t('Link (optional)'),
      '#description' => $this->t('A URL to direct users to for more information.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Send Broadcast'),
        '#button_type' => 'primary',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $title = $form_state->getValue('title');
    $message = $form_state->getValue('message');
    $link = $form_state->getValue('link') ?? '';

    // Create a batch operation to send notifications to all active users.
    $batch = [
      'title' => $this->t('Sending Broadcast to all users'),
      'operations' => [
        ['\Drupal\lms_notification\Form\BroadcastForm::processBatch', [$title, $message, $link]],
      ],
      'finished' => '\Drupal\lms_notification\Form\BroadcastForm::finishBatch',
    ];

    batch_set($batch);
  }

  /**
   * Batch process callback.
   */
  public static function processBatch(string $title, string $message, string $link, array &$context): void {
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $context['sandbox']['max'] = \Drupal::entityQuery('user')
        ->condition('status', 1)
        ->accessCheck(FALSE)
        ->count()
        ->execute();
    }

    $uids = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('uid', $context['sandbox']['current_id'], '>')
      ->sort('uid', 'ASC')
      ->range(0, 50)
      ->accessCheck(FALSE)
      ->execute();

    if (!empty($uids)) {
      /** @var \Drupal\lms_notification\NotificationManager $notificationManager */
      $notificationManager = \Drupal::service('lms_notification.manager');
      $users = \Drupal::entityTypeManager()->getStorage('user')->loadMultiple($uids);
      
      foreach ($users as $user) {
        $notificationManager->dispatch('announcement', $user, $title, $message, $link);
        $context['sandbox']['progress']++;
        $context['sandbox']['current_id'] = $user->id();
      }
    }

    if ($context['sandbox']['progress'] < $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    } else {
      $context['finished'] = 1;
    }
  }

  /**
   * Batch finished callback.
   */
  public static function finishBatch($success, $results, $operations): void {
    if ($success) {
      \Drupal::messenger()->addStatus(t('Broadcast sent successfully to all users.'));
    }
    else {
      \Drupal::messenger()->addError(t('Finished with an error.'));
    }
  }

}
