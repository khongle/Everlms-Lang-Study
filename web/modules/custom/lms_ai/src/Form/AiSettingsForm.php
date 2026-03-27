<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure LMS AI Platform settings.
 */
class AiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_ai_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['lms_ai.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('lms_ai.settings');

    $form['default_provider'] = [
      '#type' => 'select',
      '#title' => $this->t('Default AI Provider'),
      '#options' => [
        'openai' => $this->t('OpenAI (ChatGPT)'),
        'gemini' => $this->t('Google Gemini'),
      ],
      '#default_value' => $config->get('default_provider') ?: 'openai',
      '#required' => TRUE,
    ];

    $form['openai'] = [
      '#type' => 'details',
      '#title' => $this->t('OpenAI Settings'),
      '#open' => $config->get('default_provider') === 'openai',
      '#states' => [
        'visible' => [
          ':input[name="default_provider"]' => ['value' => 'openai'],
        ],
      ],
    ];

    $form['openai']['api_key'] = [
      '#type' => 'password',
      '#title' => $this->t('API Key'),
      '#maxlength' => 500,
      '#description' => $this->t('Your OpenAI API Key. Leave blank to keep current value.'),
    ];

    $form['openai']['model'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Model'),
      '#options' => [
        'gpt-4o' => 'GPT-4o',
        'gpt-4-turbo' => 'GPT-4 Turbo',
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
      ],
      '#default_value' => $config->get('openai.model') ?: 'gpt-4o',
    ];

    $form['gemini'] = [
      '#type' => 'details',
      '#title' => $this->t('Google Gemini Settings'),
      '#open' => $config->get('default_provider') === 'gemini',
      '#states' => [
        'visible' => [
          ':input[name="default_provider"]' => ['value' => 'gemini'],
        ],
      ],
    ];

    $form['gemini']['gemini_api_key'] = [
      '#type' => 'password',
      '#title' => $this->t('Gemini API Key'),
      '#maxlength' => 500,
      '#description' => $this->t('Your Google Studio API Key. Leave blank to keep current value.'),
    ];

    $form['gemini']['gemini_model'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Model'),
      '#options' => [
        'gemini-2.5-flash' => 'Gemini 2.5 Flash',
        'gemini-2.5-pro' => 'Gemini 2.5 Pro',
        'gemini-flash-latest' => 'Gemini Flash Latest',
        'gemini-pro-latest' => 'Gemini Pro Latest',
      ],
      '#default_value' => $config->get('gemini.model') ?: 'gemini-2.5-flash',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('lms_ai.settings');

    $config->set('default_provider', $form_state->getValue('default_provider'));
    $config->set('openai.model', $form_state->getValue('model'));

    if (!empty($form_state->getValue('api_key'))) {
      $config->set('openai.api_key', trim((string)$form_state->getValue('api_key')));
    }

    $config->set('gemini.model', $form_state->getValue('gemini_model'));
    if (!empty($form_state->getValue('gemini_api_key'))) {
      $config->set('gemini.api_key', trim((string)$form_state->getValue('gemini_api_key')));
    }

    $config->save();
    parent::submitForm($form, $form_state);
  }

}
