<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Creates a Listening Passage (and its comprehension questions) from a
 * validated import payload — see ListeningImportValidator.
 *
 * Runs synchronously (no Drupal batch API), same reasoning as
 * ReadingImportBatchService: a single import creates one passage plus at
 * most a few dozen small entities, well within one request's time budget.
 */
final class ListeningImportBatchService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Runs the import and returns the new passage's id.
   *
   * @param array $validated
   *   A validate()-shaped array from ListeningImportValidator, with all
   *   `errors` already confirmed empty by the caller.
   * @param int $uid
   *   The importing user, who becomes the passage's owner.
   *
   * @return int
   *   The new lms_listening_passage entity id.
   */
  public function import(array $validated, int $uid): int {
    $question_ids = [];
    foreach ($validated['questions'] as $question) {
      $question_ids[] = $this->createQuestion($question);
    }

    $passage_storage = $this->entityTypeManager->getStorage('lms_listening_passage');
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
      'field_dialogue_speakers' => array_column($passage_data['dialogue'], 'speaker'),
      'field_dialogue_lines' => array_column($passage_data['dialogue'], 'line'),
      'field_hints' => $passage_data['hints'],
      'field_translation' => $passage_data['translation'] !== '' ? ['value' => $passage_data['translation'], 'format' => 'basic_html'] : NULL,
      'field_prompt' => $passage_data['prompt'] !== '' ? $passage_data['prompt'] : NULL,
      'field_questions' => $question_ids,
      'status' => TRUE,
    ]);
    $passage->save();

    return (int) $passage->id();
  }

  /**
   * Creates one lms_question entity (+ lms_answer children for
   * multiple_choice) from a validated question row. Identical logic to
   * ReadingImportBatchService::createQuestion() — kept as an independent
   * copy per this module's independence from lms_reading's own code.
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

}
