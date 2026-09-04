<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * SM-2 tuning and daily new-card cap for the flashcard module.
 */
final class FlashcardSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['lms_flashcard.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_flashcard_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('lms_flashcard.settings');

    $form['new_cards_per_day'] = [
      '#type' => 'number',
      '#title' => $this->t('New cards per day'),
      '#description' => $this->t('Maximum number of brand-new cards a student starts studying per day, across all decks.'),
      '#min' => 0,
      '#default_value' => $config->get('new_cards_per_day'),
      '#required' => TRUE,
    ];

    $form['hard_interval_factor'] = [
      '#type' => 'number',
      '#step' => 0.1,
      '#min' => 1,
      '#title' => $this->t('"Hard" interval multiplier'),
      '#description' => $this->t('Applied instead of the ease-factor growth when a student grades a card "Hard". Set to 1.0 for a smaller, textbook-SM-2-neutral bump.'),
      '#default_value' => $config->get('hard_interval_factor'),
      '#required' => TRUE,
    ];

    $form['easy_bonus'] = [
      '#type' => 'number',
      '#step' => 0.1,
      '#min' => 1,
      '#title' => $this->t('"Easy" bonus multiplier'),
      '#description' => $this->t('Extra multiplier applied on top of the normal interval growth when a student grades a card "Easy". Set to 1.0 to disable the bonus.'),
      '#default_value' => $config->get('easy_bonus'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('lms_flashcard.settings')
      ->set('new_cards_per_day', (int) $form_state->getValue('new_cards_per_day'))
      ->set('hard_interval_factor', (float) $form_state->getValue('hard_interval_factor'))
      ->set('easy_bonus', (float) $form_state->getValue('easy_bonus'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
