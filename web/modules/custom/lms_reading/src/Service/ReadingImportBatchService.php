<?php

declare(strict_types=1);

namespace Drupal\lms_reading\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Creates a Reading Passage (and its questions/context items/word notes)
 * from a validated import payload — see ReadingImportValidator.
 *
 * Unlike lms_flashcard's ImportBatchService, this runs synchronously
 * (no Drupal batch API): a single import creates one passage plus at most a
 * few dozen small entities, well within one request's time budget, so the
 * extra moving parts of a chunked batch aren't worth it here. If passage
 * imports grow much larger later (e.g. bulk-importing many passages at
 * once), revisit this the same way lms_flashcard did.
 */
final class ReadingImportBatchService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Runs the import and returns the new passage's id.
   *
   * @param array $validated
   *   A validate()-shaped array from ReadingImportValidator, with all
   *   `errors` already confirmed empty by the caller.
   * @param int $uid
   *   The importing user, who becomes the passage's owner.
   *
   * @return int
   *   The new lms_reading_passage entity id.
   */
  public function import(array $validated, int $uid): int {
    $question_ids = [];
    foreach ($validated['questions'] as $question) {
      $question_ids[] = $this->createQuestion($question);
    }

    $context_item_ids = [];
    foreach ($validated['context_items'] as $item) {
      $context_item_ids[] = $this->createContextItem($item);
    }

    $passage_storage = $this->entityTypeManager->getStorage('lms_reading_passage');
    $passage_data = $validated['passage'];
    $passage = $passage_storage->create([
      'uid' => $uid,
      'name' => $passage_data['title'],
      'field_machine_name' => $passage_data['machine_name'] ?: NULL,
      'field_deck' => $passage_data['deck_id'],
      'field_cards' => $passage_data['card_ids'],
      'field_level' => $passage_data['level'],
      'field_format' => $passage_data['format'],
      'field_body' => $passage_data['body'] !== '' ? ['value' => $passage_data['body'], 'format' => 'basic_html'] : NULL,
      'field_translation' => $passage_data['translation'] !== '' ? ['value' => $passage_data['translation'], 'format' => 'basic_html'] : NULL,
      'field_dialogue_speakers' => array_column($passage_data['dialogue'], 'speaker'),
      'field_dialogue_lines' => array_column($passage_data['dialogue'], 'line'),
      'field_questions' => $question_ids,
      'field_context_items' => $context_item_ids,
      'status' => TRUE,
    ]);
    $passage->save();
    $passage_id = (int) $passage->id();

    // Word notes reference the passage (see ReadingWordNote::field_passage)
    // so they're created last, once the passage id is known.
    foreach ($validated['word_glossary'] as $entry) {
      $this->createWordNote($entry, $passage_id);
    }

    return $passage_id;
  }

  /**
   * Creates one lms_question entity (+ lms_answer children for
   * multiple_choice) from a validated question row.
   */
  private function createQuestion(array $question): int {
    $storage = $this->entityTypeManager->getStorage('lms_question');
    $values = [
      'type' => $question['type'],
      'name' => $question['text'],
      'field_explanation' => $question['explanation'] !== '' ? ['value' => $question['explanation'], 'format' => 'basic_html'] : NULL,
    ];

    switch ($question['type']) {
      case 'multiple_choice':
        $answer_storage = $this->entityTypeManager->getStorage('lms_answer');
        $answer_ids = [];
        foreach ($question['choices'] as $choice) {
          $answer = $answer_storage->create([
            'name' => $choice['text'],
            'field_correct' => $choice['correct'],
          ]);
          $answer->save();
          $answer_ids[] = (int) $answer->id();
        }
        $values['field_choices'] = $answer_ids;
        break;

      case 'true_false':
        $values['field_correct_answer'] = $question['correct_answer'];
        break;

      case 'fill_blank':
      case 'short_answer':
        $values['field_correct_answers'] = $question['correct_answers'];
        break;
    }

    $entity = $storage->create($values);
    $entity->save();
    return (int) $entity->id();
  }

  /**
   * Creates one lms_reading_context_item entity.
   */
  private function createContextItem(array $item): int {
    $storage = $this->entityTypeManager->getStorage('lms_reading_context_item');
    $entity = $storage->create([
      'name' => $item['target_word'],
      'field_context_sentence' => ['value' => $item['context_sentence'], 'format' => 'basic_html'],
      'field_choices' => $item['choices'],
      'field_correct_index' => $item['correct_index'],
      'field_explanation' => $item['explanation'] !== '' ? ['value' => $item['explanation'], 'format' => 'basic_html'] : NULL,
    ]);
    $entity->save();
    return (int) $entity->id();
  }

  /**
   * Creates one lms_reading_word_note entity attached to $passage_id.
   */
  private function createWordNote(array $entry, int $passage_id): int {
    $storage = $this->entityTypeManager->getStorage('lms_reading_word_note');
    $entity = $storage->create([
      'field_passage' => $passage_id,
      'name' => $entry['term'],
      'field_meaning' => ['value' => $entry['meaning'], 'format' => 'basic_html'],
      'field_pronunciation' => $entry['pronunciation'],
      'field_part_of_speech' => $entry['part_of_speech'] ?: NULL,
    ]);
    $entity->save();
    return (int) $entity->id();
  }

}
