<?php

declare(strict_types=1);

namespace Drupal\lms_reading\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Validates JSON payloads for reading-passage import, mirroring
 * lms_flashcard's ImportValidator shape and conventions (see that class'
 * docblock) but simplified for v1: every import always creates ONE new
 * ReadingPassage — there is no "append" mode yet.
 *
 * Accepted payload shape:
 * {
 *   "passage": {
 *     "title": "...", "deck_machine_name": "...", "level": "easy|normal|challenge",
 *     "format": "narrative|dialogue" (optional, defaults to "narrative"),
 *     "body": "...", (required for narrative; an optional intro line for dialogue)
 *     "dialogue": [ {"speaker": "...", "line": "..."}, ... ] (required for dialogue, >= 2 turns; absent/ignored for narrative)
 *     "translation": "...", (optional, revealed only on demand via a "Show Translation" toggle)
 *     "card_terms": ["term1", "term2", ...]
 *   },
 *   "questions": [ {"type": "multiple_choice|true_false|fill_blank|short_answer", ...}, ... ],
 *   "context_items": [ {"target_word", "context_sentence", "choices": [...], "correct_index"}, ... ],
 *   "word_glossary": [ {"term", "meaning", "pronunciation", "part_of_speech"}, ... ]
 * }
 *
 * See READING_PRACTICE_PLAN.md (repo root) section 2 for the full annotated
 * example this schema follows.
 */
final class ReadingImportValidator {

  use StringTranslationTrait;

  private const LEVEL_VALUES = ['easy', 'normal', 'challenge'];

  private const FORMAT_VALUES = ['narrative', 'dialogue'];

  private const QUESTION_TYPES = ['multiple_choice', 'true_false', 'fill_blank', 'short_answer'];

  private const PART_OF_SPEECH_VALUES = [
    'noun', 'verb', 'adjective', 'adverb', 'pronoun', 'preposition',
    'conjunction', 'interjection', 'determiner', 'article', 'phrase',
    'idiom', 'other',
  ];

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Validates a decoded JSON payload.
   *
   * @param array $data
   *   The `json_decode(..., TRUE)` result.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The importing user. Must own (or be staff able to manage) the target
   *   deck — see validatePassage() — otherwise nobody may ever be able to
   *   read the resulting passage: ReadingVisibilityService requires the
   *   *viewer* to also have access to the passage's source deck, so
   *   importing against a deck the importer themselves can't see would
   *   create a passage that's effectively unreadable by anyone.
   *
   * @return array
   *   [
   *     'errors' => string[] (non-empty means the whole import is rejected),
   *     'passage' => ['title', 'machine_name', 'deck_id', 'level', 'body', 'translation', 'card_ids'],
   *     'questions' => [validated question rows],
   *     'context_items' => [validated context-item rows],
   *     'word_glossary' => [validated glossary rows],
   *   ].
   */
  public function validate(array $data, AccountInterface $account): array {
    $result = [
      'errors' => [],
      'passage' => [],
      'questions' => [],
      'context_items' => [],
      'word_glossary' => [],
    ];

    if (empty($data['passage']) || !is_array($data['passage'])) {
      $result['errors'][] = (string) $this->t('Payload must contain a "passage" object.');
      return $result;
    }

    $result['passage'] = $this->validatePassage($data['passage'], $result['errors'], $account);

    if (isset($data['questions'])) {
      if (!is_array($data['questions'])) {
        $result['errors'][] = (string) $this->t('"questions" must be an array.');
      }
      else {
        foreach (array_values($data['questions']) as $index => $question) {
          $result['questions'][] = $this->validateQuestion($question, $index, $result['errors']);
        }
      }
    }

    if (isset($data['context_items'])) {
      if (!is_array($data['context_items'])) {
        $result['errors'][] = (string) $this->t('"context_items" must be an array.');
      }
      else {
        foreach (array_values($data['context_items']) as $index => $item) {
          $result['context_items'][] = $this->validateContextItem($item, $index, $result['errors']);
        }
      }
    }

    if (isset($data['word_glossary'])) {
      if (!is_array($data['word_glossary'])) {
        $result['errors'][] = (string) $this->t('"word_glossary" must be an array.');
      }
      else {
        foreach (array_values($data['word_glossary']) as $index => $entry) {
          $result['word_glossary'][] = $this->validateGlossaryEntry($entry, $index, $result['errors']);
        }
      }
    }

    if (empty($result['questions'])) {
      $result['errors'][] = (string) $this->t('At least one item in "questions" is required (the "Understand" step needs comprehension questions).');
    }

    return $result;
  }

  /**
   * Validates the "passage" object.
   */
  private function validatePassage(array $passage, array &$errors, AccountInterface $account): array {
    $title = trim((string) ($passage['title'] ?? ''));
    if ($title === '') {
      $errors[] = (string) $this->t('passage.title is required.');
    }
    elseif (mb_strlen($title) > 255) {
      $errors[] = (string) $this->t('passage.title exceeds 255 characters.');
    }

    $machine_name = isset($passage['machine_name']) && trim((string) $passage['machine_name']) !== ''
      ? $this->slugify((string) $passage['machine_name'])
      : $this->slugify($title);
    if ($machine_name !== '' && $this->machineNameExists($machine_name)) {
      $existing = $this->loadPassageByMachineName($machine_name);
      $errors[] = (string) $this->t('A reading passage with machine_name @machine_name already exists (@name, id @id). Choose a different passage.title/machine_name.', [
        '@machine_name' => $machine_name,
        '@name' => $existing?->label() ?? '?',
        '@id' => $existing?->id() ?? '?',
      ]);
    }

    $level = isset($passage['level']) ? trim((string) $passage['level']) : 'normal';
    if (!in_array($level, self::LEVEL_VALUES, TRUE)) {
      $errors[] = (string) $this->t('passage.level must be one of: @values.', ['@values' => implode(', ', self::LEVEL_VALUES)]);
      $level = 'normal';
    }

    $format = isset($passage['format']) ? trim((string) $passage['format']) : 'narrative';
    if (!in_array($format, self::FORMAT_VALUES, TRUE)) {
      $errors[] = (string) $this->t('passage.format must be one of: @values.', ['@values' => implode(', ', self::FORMAT_VALUES)]);
      $format = 'narrative';
    }

    $body = trim((string) ($passage['body'] ?? ''));
    $dialogue = [];
    if ($format === 'dialogue') {
      // body is optional here — just a scene-setting intro line — so no
      // error if it's empty, unlike narrative below.
      $turns = $passage['dialogue'] ?? [];
      if (!is_array($turns) || count($turns) < 2) {
        $errors[] = (string) $this->t('passage.dialogue must be an array with at least 2 turns when passage.format is "dialogue".');
      }
      else {
        foreach (array_values($turns) as $index => $turn) {
          if (!is_array($turn)) {
            $errors[] = (string) $this->t('passage.dialogue[@index] must be an object with "speaker" and "line".', ['@index' => $index]);
            continue;
          }
          $speaker = trim((string) ($turn['speaker'] ?? ''));
          $line = trim((string) ($turn['line'] ?? ''));
          if ($speaker === '') {
            $errors[] = (string) $this->t('passage.dialogue[@index].speaker is required.', ['@index' => $index]);
            continue;
          }
          if (mb_strlen($speaker) > 64) {
            $errors[] = (string) $this->t('passage.dialogue[@index].speaker exceeds 64 characters.', ['@index' => $index]);
            continue;
          }
          if ($line === '') {
            $errors[] = (string) $this->t('passage.dialogue[@index].line is required.', ['@index' => $index]);
            continue;
          }
          $dialogue[] = ['speaker' => $speaker, 'line' => $line];
        }
      }
    }
    elseif ($body === '') {
      $errors[] = (string) $this->t('passage.body is required when passage.format is "narrative".');
    }

    // Optional native-language translation of the whole passage — same
    // field/purpose as lms_listening's own "translation" (see that
    // module's ListeningImportValidator), revealed only on demand via a
    // "Show Translation" toggle, never gated behind format.
    $translation = trim((string) ($passage['translation'] ?? ''));

    $deck_id = NULL;
    $deck_machine_name = trim((string) ($passage['deck_machine_name'] ?? ''));
    if ($deck_machine_name === '') {
      $errors[] = (string) $this->t('passage.deck_machine_name is required (the Flashcard deck this passage is built from).');
    }
    else {
      $deck = $this->loadDeckByMachineName($deck_machine_name);
      if (!$deck) {
        $errors[] = (string) $this->t('passage.deck_machine_name @name does not resolve to an existing Flashcard deck.', ['@name' => $deck_machine_name]);
      }
      elseif (!$this->canBuildPassageFromDeck($deck, $account)) {
        // Mirrors lms_flashcard's own ImportForm::currentUserCanAppendTo():
        // only the deck's owner, or staff who can manage any deck, may
        // build a reading passage from it. Without this check, importing
        // against someone else's private deck would create a passage that
        // ReadingVisibilityService then hides from EVERY viewer (including
        // the importer) — see that service's docblock — so this rejects
        // the import up front with a clear reason instead of silently
        // producing unreadable content.
        $errors[] = (string) $this->t('You do not have permission to build a reading passage from deck @name — only its owner, or staff who can manage Flashcard content, may do this.', ['@name' => $deck->label()]);
      }
      else {
        $deck_id = (int) $deck->id();
      }
    }

    $card_ids = [];
    $card_terms = $passage['card_terms'] ?? [];
    if (!is_array($card_terms) || $card_terms === []) {
      $errors[] = (string) $this->t('passage.card_terms must be a non-empty array of terms already present in the target deck.');
    }
    elseif ($deck_id !== NULL) {
      $terms_by_lower = $this->getDeckCardsByLowerTerm($deck_id);
      foreach (array_values($card_terms) as $term) {
        $term = trim((string) $term);
        $key = mb_strtolower($term);
        if ($term === '' || !isset($terms_by_lower[$key])) {
          $errors[] = (string) $this->t('passage.card_terms: "@term" is not a card in deck @deck.', ['@term' => $term, '@deck' => $deck_machine_name]);
          continue;
        }
        $card_ids[] = $terms_by_lower[$key];
      }
      $card_ids = array_values(array_unique($card_ids));
    }

    return [
      'title' => $title,
      'machine_name' => $machine_name,
      'level' => $level,
      'format' => $format,
      'body' => $body,
      'dialogue' => $dialogue,
      'translation' => $translation,
      'deck_id' => $deck_id,
      'card_ids' => $card_ids,
    ];
  }

  /**
   * Validates one entry of "questions" into the shape
   * ReadingImportBatchService::createQuestion() expects.
   */
  private function validateQuestion(mixed $question, int $index, array &$errors): array {
    $label = (string) $this->t('questions[@index]', ['@index' => $index]);
    if (!is_array($question)) {
      $errors[] = (string) $this->t('@label must be an object.', ['@label' => $label]);
      return [];
    }

    $type = trim((string) ($question['type'] ?? ''));
    if (!in_array($type, self::QUESTION_TYPES, TRUE)) {
      $errors[] = (string) $this->t('@label.type must be one of: @values.', ['@label' => $label, '@values' => implode(', ', self::QUESTION_TYPES)]);
      return [];
    }

    $text = trim((string) ($question['text'] ?? ''));
    if ($text === '') {
      $errors[] = (string) $this->t('@label.text is required.', ['@label' => $label]);
    }

    $row = [
      'type' => $type,
      'text' => $text,
      'explanation' => trim((string) ($question['explanation'] ?? '')),
    ];

    if ($type === 'multiple_choice') {
      $choices = $question['choices'] ?? [];
      $valid_choices = [];
      $has_correct = FALSE;
      if (!is_array($choices) || count($choices) < 2) {
        $errors[] = (string) $this->t('@label.choices must be an array with at least 2 options.', ['@label' => $label]);
      }
      else {
        foreach (array_values($choices) as $choice_index => $choice) {
          $choice_text = trim((string) ($choice['text'] ?? ''));
          if ($choice_text === '') {
            $errors[] = (string) $this->t('@label.choices[@i] requires "text".', ['@label' => $label, '@i' => $choice_index]);
            continue;
          }
          $correct = !empty($choice['correct']);
          $has_correct = $has_correct || $correct;
          $valid_choices[] = ['text' => $choice_text, 'correct' => $correct];
        }
        if (!$has_correct) {
          $errors[] = (string) $this->t('@label.choices must mark exactly one option "correct": true.', ['@label' => $label]);
        }
      }
      $row['choices'] = $valid_choices;
    }
    elseif ($type === 'true_false') {
      if (!array_key_exists('correct_answer', $question) || !is_bool($question['correct_answer'])) {
        $errors[] = (string) $this->t('@label.correct_answer must be a boolean (true/false).', ['@label' => $label]);
      }
      $row['correct_answer'] = (bool) ($question['correct_answer'] ?? FALSE);
    }
    else {
      // fill_blank / short_answer.
      $answers = $question['correct_answers'] ?? [];
      $clean = [];
      if (!is_array($answers) || $answers === []) {
        $errors[] = (string) $this->t('@label.correct_answers must be a non-empty array of accepted answers.', ['@label' => $label]);
      }
      else {
        foreach (array_values($answers) as $answer) {
          $answer = trim((string) $answer);
          if ($answer !== '') {
            $clean[] = $answer;
          }
        }
      }
      $row['correct_answers'] = $clean;
    }

    return $row;
  }

  /**
   * Validates one entry of "context_items".
   */
  private function validateContextItem(mixed $item, int $index, array &$errors): array {
    $label = (string) $this->t('context_items[@index]', ['@index' => $index]);
    if (!is_array($item)) {
      $errors[] = (string) $this->t('@label must be an object.', ['@label' => $label]);
      return [];
    }

    $word = trim((string) ($item['target_word'] ?? ''));
    if ($word === '') {
      $errors[] = (string) $this->t('@label.target_word is required.', ['@label' => $label]);
    }

    $sentence = trim((string) ($item['context_sentence'] ?? ''));
    if ($sentence === '') {
      $errors[] = (string) $this->t('@label.context_sentence is required.', ['@label' => $label]);
    }

    $choices = $item['choices'] ?? [];
    $clean_choices = [];
    if (!is_array($choices) || count($choices) < 2) {
      $errors[] = (string) $this->t('@label.choices must be an array with at least 2 options.', ['@label' => $label]);
    }
    else {
      foreach (array_values($choices) as $choice) {
        $choice = trim((string) $choice);
        if ($choice !== '') {
          $clean_choices[] = $choice;
        }
      }
    }

    $correct_index = (int) ($item['correct_index'] ?? -1);
    if ($correct_index < 0 || $correct_index >= count($clean_choices)) {
      $errors[] = (string) $this->t('@label.correct_index must be a valid index into "choices".', ['@label' => $label]);
      $correct_index = 0;
    }

    return [
      'target_word' => $word,
      'context_sentence' => $sentence,
      'choices' => $clean_choices,
      'correct_index' => $correct_index,
      'explanation' => trim((string) ($item['explanation'] ?? '')),
    ];
  }

  /**
   * Validates one entry of "word_glossary".
   */
  private function validateGlossaryEntry(mixed $entry, int $index, array &$errors): array {
    $label = (string) $this->t('word_glossary[@index]', ['@index' => $index]);
    if (!is_array($entry)) {
      $errors[] = (string) $this->t('@label must be an object.', ['@label' => $label]);
      return [];
    }

    $term = trim((string) ($entry['term'] ?? ''));
    if ($term === '') {
      $errors[] = (string) $this->t('@label.term is required.', ['@label' => $label]);
    }

    $meaning = trim((string) ($entry['meaning'] ?? ''));
    if ($meaning === '') {
      $errors[] = (string) $this->t('@label.meaning is required.', ['@label' => $label]);
    }

    $part_of_speech = isset($entry['part_of_speech']) ? trim((string) $entry['part_of_speech']) : '';
    if ($part_of_speech !== '' && !in_array($part_of_speech, self::PART_OF_SPEECH_VALUES, TRUE)) {
      $part_of_speech = 'other';
    }

    return [
      'term' => $term,
      'meaning' => $meaning,
      'pronunciation' => mb_substr(trim((string) ($entry['pronunciation'] ?? '')), 0, 128),
      'part_of_speech' => $part_of_speech,
    ];
  }

  /**
   * Whether $account may build a reading passage from $deck.
   *
   * Same rule as lms_flashcard's ImportForm::currentUserCanAppendTo(): the
   * deck's own owner, or anyone with 'edit lms_course'/'administer
   * lms_course' (staff who can manage any deck).
   */
  private function canBuildPassageFromDeck(EntityInterface $deck, AccountInterface $account): bool {
    if ($account->hasPermission('edit lms_course') || $account->hasPermission('administer lms_course')) {
      return TRUE;
    }
    return $deck instanceof \Drupal\user\EntityOwnerInterface
      && (int) $deck->getOwnerId() === (int) $account->id();
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
   * Whether a passage with this machine_name already exists.
   */
  private function machineNameExists(string $machine_name): bool {
    return (bool) $this->loadPassageByMachineName($machine_name);
  }

  private function loadPassageByMachineName(string $machine_name): ?EntityInterface {
    $storage = $this->entityTypeManager->getStorage('lms_reading_passage');
    $ids = $storage->getQuery()
      ->condition('field_machine_name', $machine_name)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();
    return $ids ? $storage->load(reset($ids)) : NULL;
  }

  /**
   * Loads a Flashcard deck by its field_machine_name.
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
   * Maps lowercased term => card id for every card in a deck.
   */
  private function getDeckCardsByLowerTerm(int $deck_id): array {
    $storage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $ids = $storage->getQuery()
      ->condition('field_deck', $deck_id)
      ->accessCheck(FALSE)
      ->execute();
    if (!$ids) {
      return [];
    }
    $map = [];
    foreach ($storage->loadMultiple($ids) as $card) {
      $map[mb_strtolower((string) $card->label())] = (int) $card->id();
    }
    return $map;
  }

}
