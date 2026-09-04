<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Validates JSON payloads for flashcard import, row by row.
 *
 * Accepts two shapes (see the module's plan doc for the full schema):
 *  - {"deck": {...}, "cards": [...]}   -> create a new deck.
 *  - {"deck_id": 42, "cards": [...]}   -> append cards to an existing deck.
 *
 * Deliberately decoupled from *where* the JSON came from (file upload,
 * pasted textarea, or — as a future extension — an AI generator) so any
 * producer of this exact shape can reuse the same validation + import
 * pipeline.
 */
final class ImportValidator {

  use StringTranslationTrait;

  public const MAX_CARDS_PER_IMPORT = 2000;

  private const PART_OF_SPEECH_VALUES = [
    'noun', 'verb', 'adjective', 'adverb', 'pronoun', 'preposition',
    'conjunction', 'interjection', 'determiner', 'article', 'phrase',
    'idiom', 'other',
  ];

  private const DIFFICULTY_VALUES = ['easy', 'medium', 'hard'];

  private const LANGUAGE_VALUES = ['en', 'vi', 'ja', 'ko', 'zh', 'fr', 'de', 'es', 'other'];

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Validates a decoded JSON payload.
   *
   * @param array $data
   *   The `json_decode(..., TRUE)` result.
   *
   * @return array
   *   [
   *     'mode' => 'new_deck'|'append',
   *     'deck_errors' => string[],
   *     'deck' => array (validated deck field values, mode=new_deck only),
   *     'deck_id' => int (mode=append only),
   *     'rows' => [
   *       ['index' => int, 'data' => array, 'errors' => string[], 'warnings' => string[]],
   *       ...
   *     ],
   *     'valid_row_count' => int,
   *   ].
   */
  public function validate(array $data): array {
    $result = [
      'mode' => NULL,
      'deck_errors' => [],
      'deck' => [],
      'deck_id' => NULL,
      'rows' => [],
      'valid_row_count' => 0,
    ];

    if (isset($data['deck_id'])) {
      $result['mode'] = 'append';
      $deck_id = (int) $data['deck_id'];
      $deck = $deck_id ? $this->entityTypeManager->getStorage('lms_flashcard_deck')->load($deck_id) : NULL;
      if (!$deck) {
        $result['deck_errors'][] = (string) $this->t('deck_id @id does not resolve to an existing deck.', ['@id' => $data['deck_id']]);
      }
      else {
        $result['deck_id'] = $deck_id;
      }
    }
    elseif (isset($data['deck']) && is_array($data['deck'])) {
      $result['mode'] = 'new_deck';
      $result['deck'] = $this->validateDeck($data['deck'], $result['deck_errors']);
    }
    else {
      $result['deck_errors'][] = (string) $this->t('Payload must contain either a "deck" object (to create a new deck) or a "deck_id" (to append to an existing one).');
    }

    if (empty($data['cards']) || !is_array($data['cards'])) {
      $result['deck_errors'][] = (string) $this->t('Payload must contain a non-empty "cards" array.');
      return $result;
    }

    if (count($data['cards']) > self::MAX_CARDS_PER_IMPORT) {
      $result['deck_errors'][] = (string) $this->t('Too many cards in one import (@count). Maximum is @max.', [
        '@count' => count($data['cards']),
        '@max' => self::MAX_CARDS_PER_IMPORT,
      ]);
      return $result;
    }

    $seen_terms = [];
    $existing_terms = $result['deck_id'] ? $this->getExistingTerms($result['deck_id']) : [];

    foreach (array_values($data['cards']) as $index => $card) {
      $row = ['index' => $index, 'data' => [], 'errors' => [], 'warnings' => []];

      if (!is_array($card)) {
        $row['errors'][] = (string) $this->t('Row is not a JSON object.');
        $result['rows'][] = $row;
        continue;
      }

      $term = trim((string) ($card['term'] ?? ''));
      $definition = trim((string) ($card['definition'] ?? ''));

      if ($term === '') {
        $row['errors'][] = (string) $this->t('"term" is required.');
      }
      elseif (mb_strlen($term) > 255) {
        $row['errors'][] = (string) $this->t('"term" exceeds 255 characters.');
      }

      if ($definition === '') {
        $row['errors'][] = (string) $this->t('"definition" is required.');
      }

      $term_key = mb_strtolower($term);
      if ($term !== '') {
        if (isset($seen_terms[$term_key])) {
          $row['errors'][] = (string) $this->t('Duplicate term @term within this import (row @other).', [
            '@term' => $term,
            '@other' => $seen_terms[$term_key],
          ]);
        }
        else {
          $seen_terms[$term_key] = $index;
        }
        if (isset($existing_terms[$term_key])) {
          $row['warnings'][] = (string) $this->t('@term already exists in this deck — will be imported as an additional card.', ['@term' => $term]);
        }
      }

      $part_of_speech = isset($card['part_of_speech']) ? trim((string) $card['part_of_speech']) : '';
      if ($part_of_speech !== '' && !in_array($part_of_speech, self::PART_OF_SPEECH_VALUES, TRUE)) {
        $row['warnings'][] = (string) $this->t('Unrecognized part_of_speech @value, stored as "other".', ['@value' => $part_of_speech]);
        $part_of_speech = 'other';
      }

      $difficulty = isset($card['difficulty']) ? trim((string) $card['difficulty']) : 'medium';
      if (!in_array($difficulty, self::DIFFICULTY_VALUES, TRUE)) {
        $row['warnings'][] = (string) $this->t('Unrecognized difficulty @value, defaulted to "medium".', ['@value' => $difficulty]);
        $difficulty = 'medium';
      }

      $pronunciation = trim((string) ($card['pronunciation'] ?? ''));
      if (mb_strlen($pronunciation) > 128) {
        $row['warnings'][] = (string) $this->t('"pronunciation" truncated to 128 characters.');
        $pronunciation = mb_substr($pronunciation, 0, 128);
      }

      $examples = [];
      if (isset($card['examples'])) {
        if (!is_array($card['examples'])) {
          $row['errors'][] = (string) $this->t('"examples" must be an array.');
        }
        else {
          foreach (array_values($card['examples']) as $example_index => $example) {
            if (!is_array($example)) {
              $row['errors'][] = (string) $this->t('Example @number must be an object.', ['@number' => $example_index + 1]);
              continue;
            }
            $sentence = trim((string) ($example['sentence'] ?? ''));
            $translation = trim((string) ($example['translation'] ?? ''));
            if ($sentence === '') {
              $row['errors'][] = (string) $this->t('Example @number requires a sentence.', ['@number' => $example_index + 1]);
              continue;
            }
            if ($translation === '') {
              $row['errors'][] = (string) $this->t('Example @number requires a translation.', ['@number' => $example_index + 1]);
              continue;
            }
            $examples[] = [
              'sentence' => $sentence,
              'translation' => $translation,
            ];
          }
        }
      }
      else {
        // Keep the original one-example JSON shape fully compatible.
        $sentence = trim((string) ($card['example_sentence'] ?? ''));
        $translation = trim((string) ($card['example_translation'] ?? ''));
        if ($sentence !== '' || $translation !== '') {
          $examples[] = [
            'sentence' => $sentence,
            'translation' => $translation,
          ];
        }
      }

      $row['data'] = [
        'term' => $term,
        'definition' => $definition,
        'part_of_speech' => $part_of_speech,
        'pronunciation' => $pronunciation,
        'examples' => $examples,
        'difficulty' => $difficulty,
      ];

      $result['rows'][] = $row;
      if (empty($row['errors'])) {
        $result['valid_row_count']++;
      }
    }

    return $result;
  }

  /**
   * Validates the "deck" object of a new-deck import.
   */
  private function validateDeck(array $deck, array &$errors): array {
    $name = trim((string) ($deck['name'] ?? ''));
    if ($name === '') {
      $errors[] = (string) $this->t('deck.name is required.');
    }
    elseif (mb_strlen($name) > 255) {
      $errors[] = (string) $this->t('deck.name exceeds 255 characters.');
    }

    $language = isset($deck['language']) ? trim((string) $deck['language']) : 'en';
    if (!in_array($language, self::LANGUAGE_VALUES, TRUE)) {
      $language = 'other';
    }

    $native_language = isset($deck['native_language']) ? trim((string) $deck['native_language']) : 'vi';
    if (!in_array($native_language, self::LANGUAGE_VALUES, TRUE)) {
      $native_language = 'other';
    }

    $course_id = NULL;
    if (!empty($deck['course_id'])) {
      $course = $this->entityTypeManager->getStorage('lms_course')->load((int) $deck['course_id']);
      if (!$course) {
        $errors[] = (string) $this->t('deck.course_id @id does not resolve to an existing course.', ['@id' => $deck['course_id']]);
      }
      else {
        $course_id = (int) $deck['course_id'];
      }
    }

    $lesson_id = NULL;
    if (!empty($deck['lesson_id'])) {
      $lesson = $this->entityTypeManager->getStorage('lms_lesson')->load((int) $deck['lesson_id']);
      if (!$lesson) {
        $errors[] = (string) $this->t('deck.lesson_id @id does not resolve to an existing lesson.', ['@id' => $deck['lesson_id']]);
      }
      else {
        $lesson_id = (int) $deck['lesson_id'];
      }
    }

    // machine_name is the anti-duplicate-import safeguard: an explicit
    // value is used verbatim (so the same JSON re-imported later, or a
    // ChatGPT session asked to "add more cards" days apart, can target the
    // exact same deck on purpose via "Append" instead of by fragile name
    // matching); when omitted it's auto-slugified from deck.name — either
    // way, a "create new deck" import that collides with an existing
    // machine_name is rejected rather than silently creating a
    // near-duplicate deck.
    $machine_name = isset($deck['machine_name']) && trim((string) $deck['machine_name']) !== ''
      ? $this->slugify((string) $deck['machine_name'])
      : $this->slugify($name);

    if ($machine_name !== '' && $this->machineNameExists($machine_name)) {
      $existing = $this->loadDeckByMachineName($machine_name);
      $errors[] = (string) $this->t('A deck with machine_name @machine_name already exists (@name, id @id). Use "Append to an existing deck" instead, or set a different deck.machine_name.', [
        '@machine_name' => $machine_name,
        '@name' => $existing?->label() ?? '?',
        '@id' => $existing?->id() ?? '?',
      ]);
    }

    // topic is matched/created by name in ImportBatchService (which can
    // write to the taxonomy) — this validator only normalizes the string.
    $topic = trim((string) ($deck['topic'] ?? ''));
    if (mb_strlen($topic) > 255) {
      $topic = mb_substr($topic, 0, 255);
    }

    return [
      'name' => $name,
      'machine_name' => $machine_name,
      'topic' => $topic,
      'description' => trim((string) ($deck['description'] ?? '')),
      'language' => $language,
      'native_language' => $native_language,
      'course_id' => $course_id,
      'lesson_id' => $lesson_id,
    ];
  }

  /**
   * Slugifies a string into a lowercase, hyphenated machine name.
   */
  private function slugify(string $value): string {
    $slug = mb_strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug);
    return mb_substr(trim($slug, '-'), 0, 64);
  }

  /**
   * Whether a deck with this machine_name already exists.
   */
  private function machineNameExists(string $machine_name): bool {
    return (bool) $this->loadDeckByMachineName($machine_name);
  }

  /**
   * Loads the deck with this machine_name, if any.
   */
  private function loadDeckByMachineName(string $machine_name): ?EntityInterface {
    $storage = $this->entityTypeManager->getStorage('lms_flashcard_deck');
    $ids = $storage->getQuery()
      ->condition('field_machine_name', $machine_name)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();
    return $ids ? $storage->load(reset($ids)) : NULL;
  }

  /**
   * Loads lowercased existing card terms for a deck (dupe-warning lookup).
   */
  private function getExistingTerms(int $deck_id): array {
    $storage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $ids = $storage->getQuery()
      ->condition('field_deck', $deck_id)
      ->accessCheck(FALSE)
      ->execute();
    if (!$ids) {
      return [];
    }
    $terms = [];
    foreach ($storage->loadMultiple($ids) as $card) {
      $terms[mb_strtolower((string) $card->label())] = TRUE;
    }
    return $terms;
  }

}
