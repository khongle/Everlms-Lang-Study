<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_flashcard\Entity\Card;
use Drupal\lms_flashcard\Entity\Deck;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Exports a deck's cards as JSON in the exact shape the importer accepts.
 *
 * The point of this round trip: an editor can grab a deck's current
 * content, hand it to an LLM with edit instructions ("improve these
 * definitions", "add 2 more examples per card"), and re-import the result
 * via /manage/flashcard-decks/import ("Append to an existing deck") without
 * creating duplicate cards — each exported card carries its own `id`, which
 * ImportValidator/ImportBatchService recognize as "update this existing
 * card" rather than "create a new one". See those classes' docblocks for
 * the other half of this mechanism.
 */
final class DeckExportController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Page: /manage/flashcard-decks/{deck}/export — human-facing view with a
   * "Copy" button, meant to be pasted straight into an LLM chat.
   */
  public function export(Deck $lms_flashcard_deck): array {
    $data = $this->buildExportData($lms_flashcard_deck);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    return [
      '#theme' => 'lms_flashcard_deck_export',
      '#deck' => $lms_flashcard_deck,
      '#json' => $json,
      '#card_count' => count($data['cards']),
      '#attached' => [
        'library' => ['lms_flashcard/flashcard_pages', 'lms_flashcard/flashcard_export'],
      ],
    ];
  }

  /**
   * Route: /manage/flashcard-decks/{deck}/export/download — same payload as
   * a downloadable .json file, for archiving rather than pasting to an LLM.
   */
  public function download(Deck $lms_flashcard_deck): JsonResponse {
    $data = $this->buildExportData($lms_flashcard_deck);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $filename = $lms_flashcard_deck->get('field_machine_name')->value ?: ('deck-' . $lms_flashcard_deck->id());
    $filename = preg_replace('/[^a-z0-9-]+/i', '-', $filename) . '.json';

    $response = new JsonResponse($json, 200, [], TRUE);
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
    return $response;
  }

  /**
   * Builds ['deck_id' => int, 'cards' => [...]] — the "append to an
   * existing deck" import shape (see ImportValidator::validate()), with an
   * extra `id` on every card so a re-import can match it back to the same
   * entity. Queries by field_deck rather than the deck's field_cards
   * back-reference, matching StudyController::buildQuizPool() — field_cards
   * is a derived cache of this same relationship, not the source of truth.
   */
  private function buildExportData(Deck $deck): array {
    $storage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $cardIds = $storage->getQuery()
      ->condition('field_deck', $deck->id())
      ->sort('id')
      ->accessCheck(FALSE)
      ->execute();

    $cards = [];
    foreach ($storage->loadMultiple($cardIds) as $card) {
      $cards[] = [
        'id' => (int) $card->id(),
        'term' => $card->label(),
        'definition' => trim(strip_tags((string) $card->get('field_definition')->value)),
        'translation' => $card->get('field_translation')->value ?: '',
        'part_of_speech' => $card->get('field_part_of_speech')->value ?: '',
        'pronunciation' => $card->get('field_pronunciation')->value ?: '',
        'examples' => $this->buildPairs($card, 'field_example_sentences', 'field_example_translations', 'sentence'),
        'collocations' => $this->buildPairs($card, 'field_collocations', 'field_collocation_translations', 'phrase'),
        'difficulty' => $card->get('field_difficulty')->value ?: 'medium',
      ];
    }

    return [
      'deck_id' => (int) $deck->id(),
      'cards' => $cards,
    ];
  }

  /**
   * Zips two parallel multi-value string fields into [{$textKey, translation}]
   * pairs, dropping any position where the text side is blank. Shared by
   * examples (sentence/translation) and collocations (phrase/translation)
   * export — both are stored the same paired-by-delta way, see Card.php.
   */
  private function buildPairs(Card $card, string $textField, string $translationField, string $textKey): array {
    $texts = array_column($card->get($textField)->getValue(), 'value');
    $translations = array_column($card->get($translationField)->getValue(), 'value');

    $pairs = [];
    foreach ($texts as $delta => $text) {
      if (trim((string) $text) !== '') {
        $pairs[] = [
          $textKey => (string) $text,
          'translation' => (string) ($translations[$delta] ?? ''),
        ];
      }
    }
    return $pairs;
  }

}
