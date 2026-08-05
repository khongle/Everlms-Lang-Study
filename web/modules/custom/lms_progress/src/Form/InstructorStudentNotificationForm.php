<?php

declare(strict_types=1);

namespace Drupal\lms_progress\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lms_notification\NotificationManager;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sends a direct learner notification from the instructor workspace.
 */
final class InstructorStudentNotificationForm extends FormBase {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly NotificationManager $notificationManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('lms_notification.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_progress_instructor_student_notification_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?UserInterface $recipient = NULL): array {
    if (!$recipient instanceof UserInterface || !$recipient->id()) {
      return [];
    }

    $form['#attributes']['class'][] = 'lms-instructor-notify-form';
    $form['recipient_uid'] = [
      '#type' => 'hidden',
      '#value' => (int) $recipient->id(),
    ];

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Notification type'),
      '#options' => [
        'announcement' => $this->t('Announcement'),
        'reminder' => $this->t('Reminder'),
        'system' => $this->t('System message'),
      ],
      '#default_value' => 'announcement',
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#placeholder' => $this->t('Example: Please continue your palliative care module'),
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#required' => TRUE,
      '#rows' => 4,
      '#placeholder' => $this->t('Write the learner-facing message you want to send.'),
    ];

    $form['link'] = [
      '#type' => 'url',
      '#title' => $this->t('Optional link'),
      '#required' => FALSE,
      '#placeholder' => 'https://example.com/path',
      '#description' => $this->t('Add a URL only when you want the learner to open a specific page from the notification.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send Notification'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if (trim((string) $form_state->getValue('title')) === '') {
      $form_state->setErrorByName('title', $this->t('Notification title is required.'));
    }

    if (trim((string) $form_state->getValue('message')) === '') {
      $form_state->setErrorByName('message', $this->t('Notification message is required.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $recipient_id = (int) $form_state->getValue('recipient_uid');
    $recipient = $this->entityTypeManager->getStorage('user')->load($recipient_id);

    if (!$recipient instanceof UserInterface) {
      $this->messenger()->addError($this->t('The learner could not be loaded for notification delivery.'));
      return;
    }

    $type = (string) $form_state->getValue('type');
    $title = trim((string) $form_state->getValue('title'));
    $message = trim((string) $form_state->getValue('message'));
    $link = trim((string) $form_state->getValue('link'));

    $this->notificationManager->dispatch($type, $recipient, $title, $message, $link, ['in_app']);

    $this->messenger()->addStatus($this->t('Notification sent to @name.', ['@name' => $recipient->getDisplayName()]));
    $form_state->setRedirect('lms_progress.instructor_student_detail', ['user' => $recipient_id]);
  }

}
