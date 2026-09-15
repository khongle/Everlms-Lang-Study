<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Validates JSON payloads for listening-passage import, mirroring
 * lms_reading's ReadingImportValidator shape and conventions (see that
 * class' docblock) — every import always creates ONE new ListeningPassage,
 * there is no "append" mode.
 *
 * Accepted payload shape:
 * {
 *   "listening_passage": {
 *     "title": "...", "deck_machine_name": "...", "level": "easy|normal|challenge",
 *     "format": "sentence|narrative|dialogue" (optional, defaults to "sentence"),
 *     "body": "...", (required for sentence/narrative; an optional intro line for dialogue)
 *     "dialogue": [ {"speaker": "...", "line": "..."}, ... ] (required for dialogue, >= 2 turns; absent/ignored otherwise)
 *     "hints": ["...", "..."], (optional, ordered — most useful for narrative/dialogue)
 *     "translation": "...", (optional)
 *     "card_terms": ["term1", "term2", ...]
 *   },
 *   "questions": [ {"type": "multiple_choice|true_false|fill_blank|short_answer", ...}, ... ]
 * }
 *
 * See LISTENING_TRAINING_PLAN.txt (repo root) for the pedagogical rationale
 * behind the 3 formats and the hint/transcript/translation reveal order.
 */
final class ListeningImportValidator {

  use StringTranslationTrait;

  private const LEVEL_VALUES = ['easy', 'normal', 'challenge'];

  private const FORMAT_VALUES = ['sentence', 'narrative', 'dialogue'];

  private const QUESTION_TYPES = ['multiple_choice', 'true_false', 'fill_blank', 'short_answer'];

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
   *   listen to the resulting passage: ListeningVisibilityService requires
   *   the *viewer* to also have access to the passage's source deck, so
   *   importing against a deck the importer themselves can't see would
   *   create a passage that's effectively unreachable by anyone.
   *
   * @return array
   *   [
   *     'errors' => string[] (non-empty means the whole import is rejected),
   *     'passage' => ['title', 'machine_name', 'deck_id', 'level', 'format', 'body', 'dialogue', 'hints', 'translation', 'card_ids'],
   *     'questions' => [validated question rows],
   *   ].
   */
  public function validate(array $data, AccountInterface $account): array {
    $result = [
      'errors' => [],
      'passage' => [],
      'questions' => [],
    ];

    if (empty($data['listening_passage']) || !is_array($data['listening_passage'])) {
      $result['errors'][] = (string) $this->t('Payload must contain a "listening_passage" object.');
      return $result;
    }

    $result['passage'] = $this->validatePassage($data['listening_passage'], $result['errors'], $account);

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

    if (empty($result['questions'])) {
      $result['errors'][] = (string) $this->t('At least one item in "questions" is required (the comprehension step needs at least one question).');
    }

    return $result;
  }

  /**
   * Validates the "listening_passage" object.
   */
  private function validatePassage(array $passage, array &$errors, AccountInterface $account): array {
    $title = trim((string) ($passage['title'] ?? ''));
    if ($title === '') {
      $errors[] = (string) $this->t('listening_passage.title is required.');
    }
    elseif (mb_strlen($title) > 255) {
      $errors[] = (string) $this->t('listening_passage.title exceeds 255 characters.');
    }

    $machine_name = isset($passage['machine_name']) && trim((string) $passage['machine_name']) !== ''
      ? $this->slugify((string) $passage['machine_name'])
      : $this->slugify($title);
    if ($machine_name !== '' && $this->machineNameExists($machine_name)) {
      $existing = $this->loadPassageByMachineName($machine_name);
      $errors[] = (string) $this->t('A listening passage with machine_name @machine_name already exists (@name, id @id). Choose a different listening_passage.title/machine_name.', [
        '@machine_name' => $machine_name,
        '@name' => $existing?->label() ?? '?',
        '@id' => $existing?->id() ?? '?',
      ]);
    }

    $level = isset($passage['level']) ? trim((string) $passage['level']) : 'normal';
    if (!in_array($level, self::LEVEL_VALUES, TRUE)) {
      $errors[] = (string) $this->t('listening_passage.level must be one of: @values.', ['@values' => implode(', ', self::LEVEL_VALUES)]);
      $level = 'normal';
    }

    $format = isset($passage['format']) ? trim((string) $passage['format']) : 'sentence';
    if (!in_array($format, self::FORMAT_VALUES, TRUE)) {
      $errors[] = (string) $this->t('listening_passage.format must be one of: @values.', ['@values' => implode(', ', self::FORMAT_VALUES)]);
      $format = 'sentence';
    }

    $body = trim((string) ($passage['body'] ?? ''));
    $dialogue = [];
    if ($format === 'dialogue') {
      // body is optional here — just a scene-setting intro line — so no
      // error if it's empty, unlike sentence/narrative below.
      $turns = $passage['dialogue'] ?? [];
      if (!is_array($turns) || count($turns) < 2) {
        $errors[] = (string) $this->t('listening_passage.dialogue must be an array with at least 2 turns when listening_passage.format is "dialogue".');
      }
      else {
        foreach (array_values($turns) as $index => $turn) {
          if (!is_array($turn)) {
            $errors[] = (string) $this->t('listening_passage.dialogue[@index] must be an object with "speaker" and "line".', ['@index' => $index]);
            continue;
          }
          $speaker = trim((string) ($turn['speaker'] ?? ''));
          $line = trim((string) ($turn['line'] ?? ''));
          if ($speaker === '') {
            $errors[] = (string) $this->t('listening_passage.dialogue[@index].speaker is required.', ['@index' => $index]);
            continue;
          }
          if (mb_strlen($speaker) > 64) {
            $errors[] = (string) $this->t('listening_passage.dialogue[@index].speaker exceeds 64 characters.', ['@index' => $index]);
            continue;
          }
          if ($line === '') {
            $errors[] = (string) $this->t('listening_passage.dialogue[@index].line is required.', ['@index' => $index]);
            continue;
          }
          $dialogue[] = ['speaker' => $speaker, 'line' => $line];
        }
      }
    }
    elseif ($body === '') {
      $errors[] = (string) $this->t('listening_passage.body is required when listening_passage.format is "sentence" or "narrative".');
    }

    $hints = [];
    if (isset($passage['hints'])) {
      if (!is_array($passage['hints'])) {
        $errors[] = (string) $this->t('listening_passage.hints must be an array of strings.');
      }
      else {
        foreach (array_values($passage['hints']) as $hint) {
          $hint = trim((string) $hint);
          if ($hint !== '') {
            $hints[] = $hint;
          }
        }
      }
    }

    $translation = trim((string) ($passage['translation'] ?? ''));

    $deck_id = NULL;
    $deck_machine_name = trim((string) ($passage['deck_machine_name'] ?? ''));
    if ($deck_machine_name === '') {
      $errors[] = (string) $this->t('listening_passage.deck_machine_name is required (the Flashcard deck this passage is built from).');
    }
    else {
      $deck = $this->loadDeckByMachineName($deck_machine_name);
      if (!$deck) {
        $errors[] = (string) $this->t('listening_passage.deck_machine_name @name does not resolve to an existing Flashcard deck.', ['@name' => $deck_machine_name]);
      }
      elseif (!$this->canBuildPassageFromDeck($deck, $account)) {
        // Mirrors ReadingImportValidator::validatePassage()'s equivalent
        // check — without this, importing against someone else's private
        // deck would create a passage that ListeningVisibilityService then
        // hides from EVERY viewer (including the importer).
        $errors[] = (string) $this->t('You do not have permission to build a listening passage from deck @name — only its owner, or staff who can manage Flashcard content, may do this.', ['@name' => $deck->label()]);
      }
      else {
        $deck_id = (int) $deck->id();
      }
    }

    $card_ids = [];
    $card_terms = $passage['card_terms'] ?? [];
    if (!is_array($card_terms) || $card_terms === []) {
      $errors[] = (string) $this->t('listening_passage.card_terms must be a non-empty array of terms already present in the target deck.');
    }
    elseif ($deck_id !== NULL) {
      $terms_by_lower = $this->getDeckCardsByLowerTerm($deck_id);
      foreach (array_values($card_terms) as $term) {
        $term = trim((string) $term);
        $key = mb_strtolower($term);
        if ($term === '' || !isset($terms_by_lower[$key])) {
          $errors[] = (string) $this->t('listening_passage.card_terms: "@term" is not a card in deck @deck.', ['@term' => $term, '@deck' => $deck_machine_name]);
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
      'hints' => $hints,
      'translation' => $translation,
      'deck_id' => $deck_id,
      'card_ids' => $card_ids,
    ];
  }

  /**
   * Validates one entry of "questions" into the shape
   * ListeningImportBatchService::createQuestion() expects. Identical rules
   * to ReadingImportValidator::validateQuestion() — kept as an independent
   * copy per this module's independence from lms_reading's own code.
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
   * Whether $account may build a listening passage from $deck.
   *
   * Same rule as ReadingImportValidator::canBuildPassageFromDeck(): the
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
    $storage = $this->entityTypeManager->getStorage('lms_listening_passage');
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
