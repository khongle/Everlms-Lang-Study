<?php

namespace Drupal\lms_payment\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure payment gateway settings for this site.
 */
class PaymentSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lms_payment_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['lms_payment.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('lms_payment.settings');

    $form['stripe'] = [
      '#type' => 'details',
      '#title' => $this->t('Stripe Settings'),
      '#open' => TRUE,
    ];
    $form['stripe']['enable_stripe'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Stripe (Credit Card)'),
      '#default_value' => $config->get('enable_stripe') ?? 1,
    ];
    $form['stripe']['stripe_publishable_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Publishable Key'),
      '#default_value' => $config->get('stripe_publishable_key'),
      '#description' => $this->t('Starts with pk_test_ or pk_live_'),
    ];
    $form['stripe']['stripe_secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret Key'),
      '#default_value' => $config->get('stripe_secret_key'),
      '#description' => $this->t('Starts with sk_test_ or sk_live_'),
    ];

    $form['paypal'] = [
      '#type' => 'details',
      '#title' => $this->t('PayPal Settings'),
      '#open' => FALSE,
    ];
    $form['paypal']['enable_paypal'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable PayPal'),
      '#default_value' => $config->get('enable_paypal') ?? 1,
    ];
    $form['paypal']['paypal_client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('paypal_client_id'),
    ];
    $form['paypal']['paypal_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret Key'),
      '#default_value' => $config->get('paypal_secret'),
    ];

    $form['zalopay'] = [
      '#type' => 'details',
      '#title' => $this->t('ZaloPay Settings'),
      '#open' => FALSE,
    ];
    $form['zalopay']['enable_zalopay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable ZaloPay'),
      '#default_value' => $config->get('enable_zalopay') ?? 0,
    ];
    $form['zalopay']['zalopay_app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#default_value' => $config->get('zalopay_app_id'),
    ];
    $form['zalopay']['zalopay_key1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key 1'),
      '#default_value' => $config->get('zalopay_key1'),
    ];
    $form['zalopay']['zalopay_key2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key 2'),
      '#default_value' => $config->get('zalopay_key2'),
    ];

    $form['bank_transfer'] = [
      '#type' => 'details',
      '#title' => $this->t('Bank Transfer Settings (VietQR)'),
      '#open' => FALSE,
    ];
    $form['bank_transfer']['enable_bank_transfer'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Bank Transfer'),
      '#default_value' => $config->get('enable_bank_transfer') ?? 1,
    ];
    $form['bank_transfer']['bank_bin'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bank BIN or Short Name'),
      '#default_value' => $config->get('bank_bin'),
      '#description' => $this->t('E.g., VCB, MB, or BIN code like 970436.'),
    ];
    $form['bank_transfer']['bank_account_no'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bank Account Number'),
      '#default_value' => $config->get('bank_account_no'),
    ];
    $form['bank_transfer']['bank_account_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bank Account Name'),
      '#default_value' => $config->get('bank_account_name'),
      '#description' => $this->t('E.g., NGUYEN VAN A'),
    ];
    $form['bank_transfer']['bank_instructions'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Bank Instructions'),
      '#default_value' => $config->get('bank_instructions'),
      '#description' => $this->t('Instructions shown to users when they select Bank Transfer, including Bank Name, Account No., etc.'),
      '#rows' => 5,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('lms_payment.settings')
      ->set('stripe_publishable_key', $form_state->getValue('stripe_publishable_key'))
      ->set('stripe_secret_key', $form_state->getValue('stripe_secret_key'))
      ->set('paypal_client_id', $form_state->getValue('paypal_client_id'))
      ->set('paypal_secret', $form_state->getValue('paypal_secret'))
      ->set('zalopay_app_id', $form_state->getValue('zalopay_app_id'))
      ->set('zalopay_key1', $form_state->getValue('zalopay_key1'))
      ->set('zalopay_key2', $form_state->getValue('zalopay_key2'))
      ->set('bank_bin', $form_state->getValue('bank_bin'))
      ->set('bank_account_no', $form_state->getValue('bank_account_no'))
      ->set('bank_account_name', $form_state->getValue('bank_account_name'))
      ->set('bank_instructions', $form_state->getValue('bank_instructions'))
      ->set('enable_stripe', $form_state->getValue('enable_stripe'))
      ->set('enable_paypal', $form_state->getValue('enable_paypal'))
      ->set('enable_zalopay', $form_state->getValue('enable_zalopay'))
      ->set('enable_bank_transfer', $form_state->getValue('enable_bank_transfer'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
