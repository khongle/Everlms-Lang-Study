<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Builds and runs the flashcard JSON import as a Drupal batch.
 *
 * Mirrors the batch_set()/processBatch()/finishBatch() shape used by
 * lms_notification's BroadcastForm, but chunks work by putting each ~50-row
 * slice in its own operations[] entry (rather than a single sandboxed
 * operation) since the row count and chunk boundaries are already known
 * up front from validation — each operation entry is one bounded request,
 * so hundreds of cards import without hitting a PHP timeout.
 */
final class ImportBatchService {

  private const CHUNK_SIZE = 50;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Builds the batch definition and hands it to batch_set().
   *
   * @param string $mode
   *   'new_deck' or 'append'.
   * @param array $deckData
   *   Validated deck field values (mode=new_deck only), see
   *   ImportValidator::validate().
   * @param int|null $deckId
   *   Target deck ID (mode=append only).
   * @param array $rows
   *   Validated rows (only the error-free ones should be passed in).
   */
  public function startImport(string $mode, array $deckData, ?int $deckId, array $rows): void {
    $operations = [];

    if ($mode === 'new_deck') {
      $operations[] = [[self::class, 'createDeck'], [$deckData]];
    }
    else {
      $operations[] = [[self::class, 'useExistingDeck'], [$deckId]];
    }

    foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
      $operations[] = [[self::class, 'importCardChunk'], [$chunk]];
    }

    $operations[] = [[self::class, 'attachCardsToDeck'], []];

    $batch = [
      'title' => t('Importing flashcards'),
      'init_message' => t('Starting flashcard import…'),
      'progress_message' => t('Processed @current of @total.'),
      'operations' => $operations,
      'finished' => [self::class, 'finishImport'],
    ];

    batch_set($batch);
  }

  /**
   * Batch op: create the deck for a "new deck" import.
   */
  public static function createDeck(array $deckData, array &$context): void {
    $storage = \Drupal::entityTypeManager()->getStorage('lms_flashcard_deck');
    $deck = $storage->create([
      'uid' => \Drupal::currentUser()->id(),
      'name' => $deckData['name'],
      // Already validated unique (or slugified from the name) by
      // ImportValidator — passed through verbatim here rather than
      // re-derived, so the JSON's explicit deck.machine_name (when given)
      // is honored exactly, not silently re-slugified.
      'field_machine_name' => $deckData['machine_name'] ?: NULL,
      'field_topic' => !empty($deckData['topic']) ? ['target_id' => self::resolveTopicTermId($deckData['topic'])] : NULL,
      'field_description' => $deckData['description'] !== '' ? ['value' => $deckData['description'], 'format' => 'basic_html'] : NULL,
      'field_language' => $deckData['language'],
      'field_native_language' => $deckData['native_language'],
      'field_course' => $deckData['course_id'],
      'field_lesson' => $deckData['lesson_id'],
      'status' => TRUE,
    ]);
    $deck->save();

    $context['results']['deck_id'] = (int) $deck->id();
    $context['message'] = t('Created deck @name.', ['@name' => $deckData['name']]);
  }

  /**
   * Batch op: record the target deck ID for an "append" import.
   */
  public static function useExistingDeck(int $deckId, array &$context): void {
    $context['results']['deck_id'] = $deckId;
  }

  /**
   * Batch op: create up to CHUNK_SIZE card entities.
   */
  public static function importCardChunk(array $chunk, array &$context): void {
    $deckId = $context['results']['deck_id'] ?? NULL;
    $storage = \Drupal::entityTypeManager()->getStorage('lms_flashcard_card');
    $context['results']['card_ids'] ??= [];
    $context['results']['failed_rows'] ??= [];
    $context['results']['created_count'] ??= 0;

    foreach ($chunk as $row) {
      $data = $row['data'];
      try {
        $example_sentences = array_map(
          static fn(array $example): string => $example['sentence'],
          $data['examples'] ?? [],
        );
        $example_translations = array_map(
          static fn(array $example): string => $example['translation'],
          $data['examples'] ?? [],
        );
        $card = $storage->create([
          'field_deck' => $deckId,
          'name' => $data['term'],
          'field_definition' => ['value' => $data['definition'], 'format' => 'basic_html'],
          'field_part_of_speech' => $data['part_of_speech'] ?: NULL,
          'field_pronunciation' => $data['pronunciation'],
          'field_example_sentences' => $example_sentences,
          'field_example_translations' => $example_translations,
          'field_difficulty' => $data['difficulty'],
        ]);
        $card->save();
        $context['results']['card_ids'][] = (int) $card->id();
        $context['results']['created_count']++;
      }
      catch (\Throwable $e) {
        $context['results']['failed_rows'][] = [
          'index' => $row['index'],
          'term' => $data['term'] ?? '',
          'error' => $e->getMessage(),
        ];
      }
    }

    $context['message'] = t('Imported @count cards so far…', ['@count' => $context['results']['created_count']]);
  }

  /**
   * Batch op: attach every newly created card to the deck in one save.
   *
   * Done once at the end (rather than per chunk) to avoid re-saving the
   * deck entity dozens of times during a large import.
   */
  public static function attachCardsToDeck(array &$context): void {
    $deckId = $context['results']['deck_id'] ?? NULL;
    $cardIds = $context['results']['card_ids'] ?? [];
    if (!$deckId || !$cardIds) {
      return;
    }

    $deck = \Drupal::entityTypeManager()->getStorage('lms_flashcard_deck')->load($deckId);
    if (!$deck) {
      return;
    }

    $existing = array_map('intval', array_column($deck->get('field_cards')->getValue(), 'target_id'));
    $merged = array_values(array_unique(array_merge($existing, $cardIds)));
    $deck->set('field_cards', $merged);
    $deck->save();
  }

  /**
   * Batch finished callback: report results and log any per-row failures.
   */
  public static function finishImport(bool $success, array $results, array $operations): void {
    if (!$success) {
      \Drupal::messenger()->addError(t('The flashcard import stopped due to an unexpected error.'));
      return;
    }

    $created = $results['created_count'] ?? 0;
    $failed = $results['failed_rows'] ?? [];

    if ($created > 0) {
      \Drupal::messenger()->addStatus(t('Imported @count flashcards.', ['@count' => $created]));
    }

    if ($failed) {
      \Drupal::messenger()->addWarning(t('@count row(s) failed to import and were skipped. See the recent log messages for details.', ['@count' => count($failed)]));
      $logger = \Drupal::logger('lms_flashcard');
      foreach ($failed as $row) {
        $logger->error('Flashcard import row @index (term: @term) failed: @error', [
          '@index' => $row['index'],
          '@term' => $row['term'],
          '@error' => $row['error'],
        ]);
      }
    }

    if (empty($results['deck_id']) && $created === 0 && !$failed) {
      \Drupal::messenger()->addError(t('Nothing was imported.'));
    }
  }

  /**
   * Finds (case-insensitive) or creates a "flashcard_topic" term by name.
   */
  private static function resolveTopicTermId(string $name): ?int {
    $name = trim($name);
    if ($name === '') {
      return NULL;
    }

    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $ids = $storage->getQuery()
      ->condition('vid', 'flashcard_topic')
      ->condition('name', $name)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();
    if ($ids) {
      return (int) reset($ids);
    }

    $term = $storage->create(['vid' => 'flashcard_topic', 'name' => $name]);
    $term->save();
    return (int) $term->id();
  }

}
