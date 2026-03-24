<?php

declare(strict_types=1);

namespace Drupal\lms_notification\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\user\UserDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for managing user notification preferences.
 */
class NotificationPreferencesForm extends FormBase {

  /**
   * Constructs a NotificationPreferencesForm.
   */
  public function __construct(
    protected UserDataInterface $userData,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('user.data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_notification_preferences_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $uid = $this->currentUser()->id();
    
    // Define the events that users can subscribe to.
    $events = [
      'enrollment' => $this->t('Course Enrollment'),
      'quiz_result' => $this->t('Quiz Results'),
      'certificate' => $this->t('Certificate Issuance'),
      'payment' => $this->t('Payment Receipts'),
      'forum_reply' => $this->t('Forum Replies'),
      'new_lesson' => $this->t('New Lessons Available'),
      'membership_expiry' => $this->t('Membership Expiry Alerts'),
      'announcement' => $this->t('System Announcements'),
      'reminder' => $this->t('Study Reminders'),
    ];

    $form['#attributes']['class'][] = 'lms-prefs-form';
    $form['#attributes']['id'] = 'notification-prefs-form';
    $form['#attached']['library'][] = 'lms_notification/preferences';

    $is_modal = \Drupal::request()->query->get('_wrapper_format') === 'drupal_modal' || \Drupal::request()->query->has('ajax_form');

    if (!$is_modal) {
      $form['intro'] = [
        '#markup' => '<div class="lms-prefs-header"><h2>' . $this->t('Notification Preferences') . '</h2><p>' . $this->t('Manage how you receive notifications for different activities on the platform.') . '</p></div>',
      ];
    } else {
      $form['intro'] = [
        '#markup' => '<div class="lms-prefs-header" style="margin-bottom: 1rem; color: #cbd5e1;"><p>' . $this->t('Manage how you receive notifications for different activities on the platform.') . '</p></div>',
      ];
    }

    $form['preferences'] = [
      '#type' => 'table',
      '#attributes' => ['class' => ['lms-table']],
      '#header' => [
        $this->t('Event Type'),
        $this->t('In-App Notification'),
        $this->t('Email'),
      ],
      '#empty' => $this->t('No notification settings available.'),
    ];

    foreach ($events as $type => $label) {
      // Default to in_app only if not set.
      $prefs = $this->userData->get('lms_notification', $uid, "pref_$type") ?? ['in_app'];

      $form['preferences'][$type]['label'] = [
        '#markup' => '<strong>' . $label . '</strong>',
      ];
      
      $form['preferences'][$type]['in_app'] = [
        '#type' => 'checkbox',
        '#default_value' => in_array('in_app', $prefs, TRUE),
      ];
      
      $form['preferences'][$type]['email'] = [
        '#type' => 'checkbox',
        '#default_value' => in_array('email', $prefs, TRUE),
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save Preferences'),
        '#button_type' => 'primary',
      ],
    ];

    if ($is_modal) {
      $form['actions']['submit']['#ajax'] = [
        'callback' => '::ajaxSaveCallback',
      ];
      
      $form['actions']['cancel'] = [
        '#type' => 'link',
        '#title' => $this->t('Cancel'),
        '#url' => \Drupal\Core\Url::fromRoute('<current>'),
        '#attributes' => [
          'class' => ['button', 'dialog-cancel', 'lms-btn-cancel'],
        ],
        '#weight' => 99,
      ];
    }

    return $form;
  }

  /**
   * AJAX callback for saving the form inside a modal.
   */
  public function ajaxSaveCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    
    if ($form_state->hasAnyErrors()) {
      $selector = '[data-drupal-selector="' . $form['#attributes']['data-drupal-selector'] . '"]';
      $response->addCommand(new ReplaceCommand($selector, $form));
      return $response;
    }

    // Close the modal dialog on success.
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $uid = $this->currentUser()->id();
    $values = $form_state->getValue('preferences');

    foreach ($values as $type => $channels) {
      $selected = [];
      if (!empty($channels['in_app'])) {
        $selected[] = 'in_app';
      }
      if (!empty($channels['email'])) {
        $selected[] = 'email';
      }
      
      $this->userData->set('lms_notification', $uid, "pref_$type", $selected);
    }

    if (\Drupal::request()->query->get('_wrapper_format') === 'drupal_modal' || \Drupal::request()->query->has('ajax_form')) {
      $form_state->disableRedirect(TRUE);
    } else {
      $this->messenger()->addStatus($this->t('Your notification preferences have been saved.'));
    }
  }

}
