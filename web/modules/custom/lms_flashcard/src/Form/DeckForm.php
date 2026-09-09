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
      ->sort('id')
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
      $build['list'] = ['#type' => 'container', '#attributes' => ['class' => ['lms-flashcard-entity-form__cards-list']]];
      foreach ($cards as $card) {
        $definition = trim((string) strip_tags((string) $card->get('field_definition')->value));
        $timesSeen = $timesSeenByCard[$card->id()] ?? 0;
        $build['list'][$card->id()] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['lms-flashcard-entity-form__card-item']],
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
          'definition' => [
            '#markup' => '<span class="lms-flashcard-entity-form__card-def">' . mb_strimwidth($definition, 0, 70, '…') . '</span>',
          ],
          'delete' => [
            '#type' => 'link',
            '#title' => $this->t('Delete'),
            '#url' => $card->toUrl('delete-form'),
            '#attributes' => ['class' => ['lms-flashcard-entity-form__card-delete']],
          ],
        ];
      }
    }

    $build['add_link'] = [
      '#type' => 'link',
      '#title' => $this->t('+ Add a card manually'),
      '#url' => Url::fromRoute('entity.lms_flashcard_card.add_form', [], ['query' => ['deck' => $deck->id()]]),
      '#attributes' => ['class' => ['lms-flashcard-entity-form__cards-add-link']],
    ];

    return $build;
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
