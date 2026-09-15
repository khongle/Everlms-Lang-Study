<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\lms_flashcard\Entity\Deck;

/**
 * Form handler for the Flashcard entity (manual add/edit fallback).
 */
final class CardForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $form['#attributes']['class'][] = 'lms-flashcard-entity-form';
    $form['#attributes']['class'][] = 'lms-flashcard-entity-form--card';
    $form['#attached']['library'][] = 'lms_flashcard/flashcard_entity_form';

    $deck = $this->entity->get('field_deck')->entity;

    // A custom paired widget below keeps each sentence and translation at
    // the same delta. Separate unlimited widgets can drift when one side is
    // added, removed, or reordered independently. Same reasoning for
    // collocations/collocation_translations further below.
    unset(
      $form['field_example_sentence'],
      $form['field_example_translation'],
      $form['field_example_sentences'],
      $form['field_example_translations'],
      $form['field_collocations'],
      $form['field_collocation_translations'],
    );

    // Convenience: allow "Add card" to be linked from a deck's page with
    // ?deck=<id> and have field_deck pre-filled, so the manual fallback form
    // doesn't force the editor to look up the deck ID.
    if ($this->entity->isNew() && $this->entity->get('field_deck')->isEmpty()) {
      $deck_id = $this->getRequest()->query->get('deck');
      if ($deck_id && ctype_digit((string) $deck_id)) {
        $deck = $this->entityTypeManager
          ->getStorage('lms_flashcard_deck')
          ->load($deck_id);
        $form['field_deck']['widget'][0]['target_id']['#default_value'] = $deck;
      }
    }

    $form['navigation'] = $this->buildNavigation($deck);
    $form['examples'] = $this->buildExamplesElement($form_state);
    $form['collocations'] = $this->buildCollocationsElement($form_state);

    return $form;
  }

  /**
   * Builds sentence/translation rows that can only be added or removed as pairs.
   */
  private function buildExamplesElement(FormStateInterface $form_state): array {
    $items = $form_state->get('flashcard_example_items');
    if ($items === NULL) {
      $sentences = array_column($this->entity->get('field_example_sentences')->getValue(), 'value');
      $translations = array_column($this->entity->get('field_example_translations')->getValue(), 'value');
      $items = [];
      for ($delta = 0; $delta < max(count($sentences), count($translations)); $delta++) {
        $items[] = [
          'sentence' => (string) ($sentences[$delta] ?? ''),
          'translation' => (string) ($translations[$delta] ?? ''),
        ];
      }
      $items = $items ?: [['sentence' => '', 'translation' => '']];
    }

    $element = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#weight' => -5,
      '#attributes' => [
        'id' => 'lms-flashcard-examples',
        'class' => ['lms-flashcard-entity-form__examples'],
      ],
      'heading' => [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $this->t('Examples'),
        '#attributes' => ['class' => ['lms-flashcard-entity-form__examples-heading']],
      ],
      'help' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Each sentence stays paired with the translation in the same row.'),
        '#attributes' => ['class' => ['lms-flashcard-entity-form__examples-help']],
      ],
      'items' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['lms-flashcard-entity-form__examples-list']],
      ],
    ];

    foreach (array_values($items) as $delta => $item) {
      $element['items'][$delta] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['lms-flashcard-entity-form__example-row']],
        'number' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $this->t('Example @number', ['@number' => $delta + 1]),
          '#attributes' => ['class' => ['lms-flashcard-entity-form__example-number']],
        ],
        'sentence' => [
          '#type' => 'textarea',
          '#title' => $this->t('Sentence'),
          '#rows' => 2,
          '#default_value' => $item['sentence'] ?? '',
          '#required' => FALSE,
        ],
        'translation' => [
          '#type' => 'textarea',
          '#title' => $this->t('Translation'),
          '#rows' => 2,
          '#default_value' => $item['translation'] ?? '',
          '#required' => FALSE,
        ],
        'remove' => [
          '#type' => 'submit',
          '#value' => $this->t('Remove example'),
          '#name' => 'remove_example_' . $delta,
          '#example_index' => $delta,
          '#submit' => ['::removeExample'],
          '#limit_validation_errors' => [],
          '#ajax' => [
            'callback' => '::ajaxExamples',
            'wrapper' => 'lms-flashcard-examples',
          ],
          '#attributes' => ['class' => ['lms-flashcard-entity-form__example-remove']],
        ],
      ];
    }

    $element['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('+ Add example'),
      '#submit' => ['::addExample'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::ajaxExamples',
        'wrapper' => 'lms-flashcard-examples',
      ],
      '#attributes' => ['class' => ['lms-flashcard-entity-form__example-add']],
    ];

    return $element;
  }

  /**
   * AJAX callback for the paired examples widget.
   */
  public function ajaxExamples(array &$form, FormStateInterface $form_state): array {
    return $form['examples'];
  }

  /**
   * Adds one empty sentence/translation pair.
   */
  public function addExample(array &$form, FormStateInterface $form_state): void {
    $items = $this->submittedExamples($form_state);
    $items[] = ['sentence' => '', 'translation' => ''];
    $this->storeExamples($form_state, $items);
    $form_state->setRebuild();
  }

  /**
   * Removes a sentence and its translation together.
   */
  public function removeExample(array &$form, FormStateInterface $form_state): void {
    $items = $this->submittedExamples($form_state);
    $index = (int) ($form_state->getTriggeringElement()['#example_index'] ?? -1);
    if (isset($items[$index])) {
      unset($items[$index]);
    }
    $this->storeExamples($form_state, array_values($items) ?: [
      ['sentence' => '', 'translation' => ''],
    ]);
    $form_state->setRebuild();
  }

  /**
   * Returns only the editable values from submitted example rows.
   */
  private function submittedExamples(FormStateInterface $form_state): array {
    $submitted = $form_state->getValue(['examples', 'items']);
    $user_input = $form_state->getUserInput();
    $raw_items = $user_input['examples']['items'] ?? [];
    if ((!is_array($submitted) || $submitted === []) && is_array($raw_items)) {
      $submitted = $raw_items;
    }
    $items = [];
    foreach (is_array($submitted) ? $submitted : [] as $item) {
      $items[] = [
        'sentence' => trim((string) ($item['sentence'] ?? '')),
        'translation' => trim((string) ($item['translation'] ?? '')),
      ];
    }
    return $items;
  }

  /**
   * Keeps rebuilt AJAX elements and submitted input on the same row indexes.
   */
  private function storeExamples(FormStateInterface $form_state, array $items): void {
    $form_state->set('flashcard_example_items', $items);
    $user_input = $form_state->getUserInput();
    $user_input['examples']['items'] = $items;
    $form_state->setUserInput($user_input);
  }

  /**
   * Builds phrase/translation rows that can only be added or removed as
   * pairs — same reasoning and shape as buildExamplesElement() above, just
   * for field_collocations/field_collocation_translations instead.
   */
  private function buildCollocationsElement(FormStateInterface $form_state): array {
    $items = $form_state->get('flashcard_collocation_items');
    if ($items === NULL) {
      $phrases = array_column($this->entity->get('field_collocations')->getValue(), 'value');
      $translations = array_column($this->entity->get('field_collocation_translations')->getValue(), 'value');
      $items = [];
      for ($delta = 0; $delta < max(count($phrases), count($translations)); $delta++) {
        $items[] = [
          'phrase' => (string) ($phrases[$delta] ?? ''),
          'translation' => (string) ($translations[$delta] ?? ''),
        ];
      }
      $items = $items ?: [['phrase' => '', 'translation' => '']];
    }

    $element = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#weight' => -3,
      '#attributes' => [
        'id' => 'lms-flashcard-collocations',
        'class' => ['lms-flashcard-entity-form__examples', 'lms-flashcard-entity-form__collocations'],
      ],
      'heading' => [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $this->t('Collocations'),
        '#attributes' => ['class' => ['lms-flashcard-entity-form__examples-heading']],
      ],
      'help' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Common word combinations this term appears in (e.g. "make a decision"). Each collocation stays paired with the translation in the same row.'),
        '#attributes' => ['class' => ['lms-flashcard-entity-form__examples-help']],
      ],
      'items' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['lms-flashcard-entity-form__examples-list']],
      ],
    ];

    foreach (array_values($items) as $delta => $item) {
      $element['items'][$delta] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['lms-flashcard-entity-form__example-row']],
        'number' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $this->t('Collocation @number', ['@number' => $delta + 1]),
          '#attributes' => ['class' => ['lms-flashcard-entity-form__example-number']],
        ],
        'phrase' => [
          '#type' => 'textfield',
          '#title' => $this->t('Collocation'),
          '#default_value' => $item['phrase'] ?? '',
          '#required' => FALSE,
        ],
        'translation' => [
          '#type' => 'textfield',
          '#title' => $this->t('Translation'),
          '#default_value' => $item['translation'] ?? '',
          '#required' => FALSE,
        ],
        'remove' => [
          '#type' => 'submit',
          '#value' => $this->t('Remove collocation'),
          '#name' => 'remove_collocation_' . $delta,
          '#collocation_index' => $delta,
          '#submit' => ['::removeCollocation'],
          '#limit_validation_errors' => [],
          '#ajax' => [
            'callback' => '::ajaxCollocations',
            'wrapper' => 'lms-flashcard-collocations',
          ],
          '#attributes' => ['class' => ['lms-flashcard-entity-form__example-remove']],
        ],
      ];
    }

    $element['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('+ Add collocation'),
      '#submit' => ['::addCollocation'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::ajaxCollocations',
        'wrapper' => 'lms-flashcard-collocations',
      ],
      '#attributes' => ['class' => ['lms-flashcard-entity-form__example-add']],
    ];

    return $element;
  }

  /**
   * AJAX callback for the paired collocations widget.
   */
  public function ajaxCollocations(array &$form, FormStateInterface $form_state): array {
    return $form['collocations'];
  }

  /**
   * Adds one empty phrase/translation pair.
   */
  public function addCollocation(array &$form, FormStateInterface $form_state): void {
    $items = $this->submittedCollocations($form_state);
    $items[] = ['phrase' => '', 'translation' => ''];
    $this->storeCollocations($form_state, $items);
    $form_state->setRebuild();
  }

  /**
   * Removes a collocation and its translation together.
   */
  public function removeCollocation(array &$form, FormStateInterface $form_state): void {
    $items = $this->submittedCollocations($form_state);
    $index = (int) ($form_state->getTriggeringElement()['#collocation_index'] ?? -1);
    if (isset($items[$index])) {
      unset($items[$index]);
    }
    $this->storeCollocations($form_state, array_values($items) ?: [
      ['phrase' => '', 'translation' => ''],
    ]);
    $form_state->setRebuild();
  }

  /**
   * Returns only the editable values from submitted collocation rows.
   */
  private function submittedCollocations(FormStateInterface $form_state): array {
    $submitted = $form_state->getValue(['collocations', 'items']);
    $user_input = $form_state->getUserInput();
    $raw_items = $user_input['collocations']['items'] ?? [];
    if ((!is_array($submitted) || $submitted === []) && is_array($raw_items)) {
      $submitted = $raw_items;
    }
    $items = [];
    foreach (is_array($submitted) ? $submitted : [] as $item) {
      $items[] = [
        'phrase' => trim((string) ($item['phrase'] ?? '')),
        'translation' => trim((string) ($item['translation'] ?? '')),
      ];
    }
    return $items;
  }

  /**
   * Keeps rebuilt AJAX elements and submitted input on the same row indexes.
   */
  private function storeCollocations(FormStateInterface $form_state, array $items): void {
    $form_state->set('flashcard_collocation_items', $items);
    $user_input = $form_state->getUserInput();
    $user_input['collocations']['items'] = $items;
    $form_state->setUserInput($user_input);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);
    foreach ($this->submittedExamples($form_state) as $delta => $item) {
      if ($item['sentence'] === '' && $item['translation'] !== '') {
        $form_state->setError(
          $form['examples']['items'][$delta]['sentence'],
          $this->t('Enter the sentence for example @number.', ['@number' => $delta + 1]),
        );
      }
      if ($item['sentence'] !== '' && $item['translation'] === '') {
        $form_state->setError(
          $form['examples']['items'][$delta]['translation'],
          $this->t('Enter the translation for example @number.', ['@number' => $delta + 1]),
        );
      }
    }
    foreach ($this->submittedCollocations($form_state) as $delta => $item) {
      if ($item['phrase'] === '' && $item['translation'] !== '') {
        $form_state->setError(
          $form['collocations']['items'][$delta]['phrase'],
          $this->t('Enter the collocation for row @number.', ['@number' => $delta + 1]),
        );
      }
      if ($item['phrase'] !== '' && $item['translation'] === '') {
        $form_state->setError(
          $form['collocations']['items'][$delta]['translation'],
          $this->t('Enter the translation for collocation @number.', ['@number' => $delta + 1]),
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    parent::submitForm($form, $form_state);

    $sentences = [];
    $translations = [];
    foreach ($this->submittedExamples($form_state) as $item) {
      if ($item['sentence'] !== '' && $item['translation'] !== '') {
        $sentences[] = $item['sentence'];
        $translations[] = $item['translation'];
      }
    }
    $this->entity->set('field_example_sentences', $sentences);
    $this->entity->set('field_example_translations', $translations);

    $phrases = [];
    $collocationTranslations = [];
    foreach ($this->submittedCollocations($form_state) as $item) {
      if ($item['phrase'] !== '' && $item['translation'] !== '') {
        $phrases[] = $item['phrase'];
        $collocationTranslations[] = $item['translation'];
      }
    }
    $this->entity->set('field_collocations', $phrases);
    $this->entity->set('field_collocation_translations', $collocationTranslations);
  }

  /**
   * Builds in-form navigation because the site theme hides breadcrumbs on
   * every /manage route.
   */
  private function buildNavigation(?Deck $deck): array {
    $navigation = [
      '#type' => 'container',
      '#weight' => -100,
      '#attributes' => [
        'class' => ['lms-flashcard-entity-form__breadcrumb'],
        'aria-label' => $this->t('Breadcrumb'),
      ],
      'decks' => [
        '#type' => 'link',
        '#title' => $this->t('My Flashcards'),
        '#url' => Url::fromRoute('lms_flashcard.my_decks'),
      ],
    ];

    if ($deck) {
      $navigation['deck'] = [
        '#type' => 'link',
        '#title' => $deck->label(),
        '#url' => $deck->toUrl('edit-form'),
      ];
    }

    $navigation['current'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $this->entity->isNew()
        ? $this->t('Add card')
        : $this->t('Edit @card', ['@card' => $this->entity->label()]),
      '#attributes' => ['aria-current' => 'page'],
    ];

    return $navigation;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);
    $entity = $this->getEntity();
    $this->messenger()->addStatus(
      $result === SAVED_NEW
        ? $this->t('Flashcard @label created.', ['@label' => $entity->label()])
        : $this->t('Flashcard @label updated.', ['@label' => $entity->label()])
    );

    // Keep the deck's field_cards back-reference in sync so the container
    // side (used by the study queue and the deck's card count) reflects
    // manually added/edited cards the same way the JSON importer does.
    $deck = $entity->get('field_deck')->entity;
    if ($deck) {
      $existing = array_map('intval', array_column($deck->get('field_cards')->getValue(), 'target_id'));
      if (!in_array((int) $entity->id(), $existing, TRUE)) {
        $existing[] = (int) $entity->id();
        $deck->set('field_cards', $existing);
        $deck->save();
      }
      $form_state->setRedirectUrl($deck->toUrl('canonical'));
      return $result;
    }

    $form_state->setRedirectUrl($entity->toUrl('collection'));
    return $result;
  }

}
