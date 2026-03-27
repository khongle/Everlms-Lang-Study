<?php

declare(strict_types=1);

namespace Drupal\lms_zoom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Zoom settings for Server-to-Server OAuth.
 */
final class ZoomSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_zoom_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['lms_zoom.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('lms_zoom.settings');

    $form['account_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account ID'),
      '#default_value' => $config->get('account_id'),
      '#required' => TRUE,
    ];

    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('client_id'),
      '#required' => TRUE,
    ];

    $form['client_secret'] = [
      '#type' => 'password',
      '#title' => $this->t('Client Secret'),
      '#description' => $this->t('Leave blank if you do not want to update. Used for Server-to-Server OAuth.'),
    ];

    $form['sdk_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Meeting SDK Key'),
      '#default_value' => $config->get('sdk_key'),
      '#description' => $this->t('Required for embedding Zoom meetings on the website (Web SDK).'),
    ];

    $form['sdk_secret'] = [
      '#type' => 'password',
      '#title' => $this->t('Meeting SDK Secret'),
      '#description' => $this->t('Leave blank to keep existing secret. Required for Web SDK signatures.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('lms_zoom.settings');
    $config->set('account_id', $form_state->getValue('account_id'));
    $config->set('client_id', $form_state->getValue('client_id'));

    if ($form_state->getValue('client_secret')) {
      $config->set('client_secret', $form_state->getValue('client_secret'));
    }

    $config->set('sdk_key', $form_state->getValue('sdk_key'));
    if ($form_state->getValue('sdk_secret')) {
      $config->set('sdk_secret', $form_state->getValue('sdk_secret'));
    }

    $config->save();
    parent::submitForm($form, $form_state);
  }

}
