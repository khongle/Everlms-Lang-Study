<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form handler for the Flashcard Deck entity.
 */
final class DeckForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $form['#attributes']['class'][] = 'lms-flashcard-entity-form';
    $form['#attributes']['class'][] = 'lms-flashcard-entity-form--deck';
    $form['#attached']['library'][] = 'lms_flashcard/flashcard_entity_form';

    // field_cards is an unlimited-cardinality reference with no custom
    // widget, so Drupal's default multi-value widget renders one
    // autocomplete text box per card — dozens of them for a real deck.
    // It's a derived/internal field kept in sync automatically by the JSON
    // importer and by CardForm::save(), never meant for hand-editing here.
    unset($form['field_cards']);

    // In its place: a "Cards in this deck" panel (only once the deck
    // actually exists) — this is the only way to reach an individual
    // card's own edit page, since it's no longer listed on this form.
    // flashcard-entity-form.css lays it out as a sidebar in the space that
    // used to sit empty next to the (fairly narrow) field column.
    if (!$this->entity->isNew()) {
      $form['cards_panel'] = $this->buildCardsPanel();
    }

    return $form;
  }

  /**
   * Builds the sidebar list of this deck's cards, each linking to its own
   * edit/delete form — the manual fallback for fixing a card's content
   * after import.
   */
  private function buildCardsPanel(): array {
    /** @var \Drupal\lms_flashcard\Entity\Deck $deck */
    $deck = $this->entity;
    $storage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $card_ids = $storage->getQuery()
      ->condition('field_deck', $deck->id())
      ->sort('name')
      ->accessCheck(FALSE)
      ->execute();
    $cards = $card_ids ? $storage->loadMultiple($card_ids) : [];
    $timesSeenByCard = $cards ? $this->loadTimesSeenByCard(array_keys($cards)) : [];

    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['lms-flashcard-entity-form__cards-panel']],
      '#weight' => 100,
    ];

    $build['heading'] = [
      '#markup' => '<h3 class="lms-flashcard-entity-form__cards-heading">' . $this->t('Cards in this deck (@count)', ['@count' => count($cards)]) . '</h3>',
    ];

    if (!$cards) {
      $build['empty'] = [
        '#markup' => '<p class="lms-flashcard-entity-form__cards-empty">' . $this->t('No cards yet — use Import or "Add a card" below.') . '</p>',
      ];
    }
    else {
      // Client-side only (js/flashcard-entity-form.js) — a plain
      // #type => 'html_tag' rather than a real Form API element (search/
      // textfield), so it's never part of $form_state's values and can't
      // interfere with saving the deck; it only ever hides/shows the
      // already-rendered card items below by text match. Fine at this
      // deck's own scale (dozens of cards server-rendered at once); a deck
      // with hundreds would want real server-side paging/search instead.
      $build['filter'] = [
        '#type' => 'html_tag',
        '#tag' => 'input',
        '#attributes' => [
          'type' => 'search',
          'class' => ['lms-flashcard-entity-form__cards-filter'],
          'placeholder' => $this->t('Filter by term or definition…'),
          'aria-label' => $this->t('Filter cards in this deck'),
          'autocomplete' => 'off',
          'data-flashcard-cards-filter' => TRUE,
        ],
      ];
      $build['filter_empty'] = [
        '#markup' => '<p class="lms-flashcard-entity-form__cards-empty" data-flashcard-cards-filter-empty hidden>' . $this->t('No cards match your filter.') . '</p>',
      ];

      $build['list'] = ['#type' => 'container', '#attributes' => ['class' => ['lms-flashcard-entity-form__cards-list']]];
      foreach ($cards as $card) {
        $definition = trim((string) strip_tags((string) $card->get('field_definition')->value));
        $timesSeen = $timesSeenByCard[$card->id()] ?? 0;
        $isPublished = (bool) $card->get('status')->value;
        $itemClasses = ['lms-flashcard-entity-form__card-item'];
        if (!$isPublished) {
          $itemClasses[] = 'is-unpublished';
        }
        $build['list'][$card->id()] = [
          '#type' => 'container',
          '#attributes' => ['class' => $itemClasses],
          'header' => [
            '#type' => 'container',
            '#attributes' => ['class' => ['lms-flashcard-entity-form__card-header']],
            'term' => [
              '#type' => 'link',
              '#title' => $card->label(),
              '#url' => $card->toUrl('edit-form'),
              '#attributes' => ['class' => ['lms-flashcard-entity-form__card-term']],
            ],
            // Your own view-count (see CardStat, per uid+card — not an
            // aggregate across every student who's studied this deck), so
            // it's only meaningful on a deck you've actually studied
            // yourself; 0 just means you haven't (yet), not that no one has.
            'seen' => [
              '#markup' => '<span class="lms-flashcard-entity-form__card-seen">' . $this->formatPlural($timesSeen, 'Seen 1 time', 'Seen @count times') . '</span>',
            ],
          ],
          // Only rendered while unpublished — a quick visual flag in the
          // list so an editor scanning it doesn't have to open each card
          // to notice it's hidden from study. See Card::status's docblock.
          'unpublished_badge' => $isPublished ? [] : [
            '#markup' => '<span class="lms-flashcard-entity-form__card-unpublished-badge">' . $this->t('Unpublished — hidden from study') . '</span>',
          ],
          'definition' => [
            '#markup' => '<span class="lms-flashcard-entity-form__card-def">' . mb_strimwidth($definition, 0, 70, '…') . '</span>',
          ],
          'footer' => [
            '#type' => 'container',
            '#attributes' => ['class' => ['lms-flashcard-entity-form__card-footer']],
            'delete' => [
              '#type' => 'link',
              '#title' => $this->t('Delete'),
              '#url' => $card->toUrl('delete-form'),
              '#attributes' => ['class' => ['lms-flashcard-entity-form__card-delete']],
            ],
            // A plain submit button rather than a link to a route, so
            // toggling a card's status never leaves this page (Drupal's
            // multiple-submit-buttons-per-form pattern: its own #submit/
            // #validate override the Deck form's default save handler,
            // and #limit_validation_errors => [] skips validating the
            // rest of the Deck's own fields — clicking this never
            // attempts to (re)save the deck itself). #card_id is a plain
            // custom render-array property, read back in
            // toggleCardStatus() via getTriggeringElement(). Access is
            // re-checked there too, not just by rendering this only where
            // FlashcardAccessControlHandler already grants 'update' on
            // the deck (this whole form is already gated the same way).
            'toggle_status' => [
              '#type' => 'submit',
              '#name' => 'lms_flashcard_toggle_status_' . $card->id(),
              '#value' => $isPublished ? $this->t('Unpublish') : $this->t('Publish'),
              '#card_id' => (int) $card->id(),
              '#limit_validation_errors' => [],
              '#validate' => [],
              '#submit' => ['::toggleCardStatus'],
              '#attributes' => ['class' => ['lms-flashcard-entity-form__card-toggle-status']],
            ],
          ],
        ];
      }
    }

    $build['links'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['lms-flashcard-entity-form__cards-links']],
      // The primary content-creation path (see ImportForm's own docblock)
      // — pre-fills "Append to an existing deck" + this deck via ?deck=,
      // so the editor doesn't have to find it again in that form's
      // dropdown. Comes before "Add a card manually" to match that same
      // primary/fallback ordering.
      'import_link' => [
        '#type' => 'link',
        '#title' => $this->t('+ Import cards (JSON)'),
        '#url' => Url::fromRoute('lms_flashcard.import', [], ['query' => ['deck' => $deck->id()]]),
        '#attributes' => ['class' => ['lms-flashcard-entity-form__cards-add-link']],
      ],
      'add_link' => [
        '#type' => 'link',
        '#title' => $this->t('+ Add a card manually'),
        '#url' => Url::fromRoute('entity.lms_flashcard_card.add_form', [], ['query' => ['deck' => $deck->id()]]),
        '#attributes' => ['class' => ['lms-flashcard-entity-form__cards-add-link']],
      ],
    ];

    return $build;
  }

  /**
   * Submit handler for a card row's Publish/Unpublish button — see
   * buildCardsPanel()'s 'toggle_status' element for why this is a
   * dedicated #submit rather than the Deck form's own save(): flips
   * Card::status and re-displays this same deck edit page, without saving
   * (or even validating) the deck entity itself.
   */
  public function toggleCardStatus(array &$form, FormStateInterface $form_state): void {
    $triggeringElement = $form_state->getTriggeringElement();
    $cardId = $triggeringElement['#card_id'] ?? NULL;
    if (!$cardId) {
      return;
    }

    $card = $this->entityTypeManager->getStorage('lms_flashcard_card')->load($cardId);
    if (!$card) {
      $this->messenger()->addError($this->t('That card no longer exists.'));
      $form_state->setRebuild();
      return;
    }
    // Same owner-or-staff rule as everything else (FlashcardAccessControlHandler)
    // — re-checked here since this bypasses the entity form's own access-gated
    // widgets entirely.
    if (!$card->access('update')) {
      $this->messenger()->addError($this->t('You do not have permission to change that card.'));
      $form_state->setRebuild();
      return;
    }

    $newStatus = !$card->get('status')->value;
    $card->set('status', $newStatus);
    $card->save();

    $this->messenger()->addStatus($newStatus
      ? $this->t('"@term" is published again and will appear in study sessions.', ['@term' => $card->label()])
      : $this->t('"@term" is unpublished — it will no longer appear in any study mode, but stays in Export JSON.', ['@term' => $card->label()])
    );

    // Stay on this same deck edit page (rebuilt with the fresh card list)
    // instead of following the Deck form's own save() redirect.
    $form_state->setRebuild();
  }

  /**
   * Batch-loads the current user's CardStat.field_times_seen for a set of
   * cards, keyed by card ID (a card with no CardStat row at all — never
   * shown to this user — is simply absent, treated as 0 by the caller).
   *
   * One query for the whole panel rather than one per card — see
   * FlashcardApiController::recordCardSeen() for what actually writes
   * these.
   */
  private function loadTimesSeenByCard(array $cardIds): array {
    $uid = (int) $this->currentUser()->id();
    if (!$uid || !$cardIds) {
      return [];
    }

    $statStorage = $this->entityTypeManager->getStorage('lms_flashcard_card_stat');
    $statIds = $statStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardIds, 'IN')
      ->accessCheck(FALSE)
      ->execute();
    if (!$statIds) {
      return [];
    }

    $timesSeenByCard = [];
    foreach ($statStorage->loadMultiple($statIds) as $stat) {
      $timesSeenByCard[(int) $stat->get('field_card')->target_id] = (int) $stat->get('field_times_seen')->value;
    }
    return $timesSeenByCard;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);
    $entity = $this->getEntity();
    $this->messenger()->addStatus(
      $result === SAVED_NEW
        ? $this->t('Deck @label created.', ['@label' => $entity->label()])
        : $this->t('Deck @label updated.', ['@label' => $entity->label()])
    );
    // Not the admin collection (entity.lms_flashcard_deck.collection) — it's
    // gated by 'administer lms_course', which a student editing their own
    // self-imported deck won't have (see the same fix in ImportForm).
    $form_state->setRedirect('lms_flashcard.my_decks');
    return $result;
  }

}
