<?php

namespace Drupal\lms_certificate\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityInterface;

/**
 * Form for revoking a user certificate.
 */
class RevokeCertificateForm extends ConfirmFormBase {

  protected $certificate;

  public function getFormId() {
    return 'lms_certificate_revoke_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, ?EntityInterface $lms_user_certificate = NULL) {
    $this->certificate = $lms_user_certificate;
    
    $form['revoke_reason'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Reason for Revocation'),
      '#required' => TRUE,
    ];
    
    return parent::buildForm($form, $form_state);
  }

  public function getQuestion() {
    return $this->t('Are you sure you want to revoke the certificate %code?', ['%code' => $this->certificate->get('field_certificate_number')->value]);
  }

  public function getCancelUrl() {
    // The certificate management dashboard (no entity collection route exists).
    return Url::fromRoute('lms_certificate.admin_dashboard');
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->certificate->set('field_revoked', 1);
    $this->certificate->set('field_revoke_reason', $form_state->getValue('revoke_reason'));
    $this->certificate->save();

    $this->messenger()->addStatus($this->t('Certificate %code has been successfully revoked.', ['%code' => $this->certificate->get('field_certificate_number')->value]));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
