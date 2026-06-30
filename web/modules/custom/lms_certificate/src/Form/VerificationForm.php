<?php

namespace Drupal\lms_certificate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Form for verifying a certificate code.
 */
class VerificationForm extends FormBase {

  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function getFormId() {
    return 'lms_certificate_verification_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attributes']['class'][] = 'lms-certificate-verification-form';

    $form['certificate_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Certificate Code'),
      '#required' => TRUE,
      '#description' => $this->t('Enter the certificate code found at the bottom of your certificate.'),
      '#attributes' => ['placeholder' => $this->t('e.g., A1B2C3D4E5')],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Verify'),
      '#button_type' => 'primary',
    ];

    // Placeholder for results.
    if ($result = $form_state->get('verification_result')) {
      $form['result'] = $result;
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $code = $form_state->getValue('certificate_code');

    $certs = $this->entityTypeManager->getStorage('lms_user_certificate')
      ->loadByProperties(['field_certificate_number' => $code]);

    $form_state->setRebuild(TRUE);

    if (empty($certs)) {
      $form_state->set('verification_result', [
        '#type' => 'status_messages',
        '#markup' => '<div class="messages messages--error">' . $this->t('No certificate found with this code.') . '</div>',
      ]);
      return;
    }

    $cert = reset($certs);

    // Check if revoked.
    $is_revoked = (bool) $cert->get('field_revoked')->value;

    $user = $cert->get('uid')->entity;
    $course = $cert->get('field_course')->entity;

    $form_state->set('verification_result', [
      '#theme' => 'certificate_verification_result',
      '#certificate' => $cert,
      '#user' => $user,
      '#course' => $course,
      '#is_revoked' => $is_revoked,
    ]);
  }

}
