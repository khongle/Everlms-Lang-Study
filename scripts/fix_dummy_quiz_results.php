<?php

/**
 * Script to generate fake question responses for existing dummy Quiz Results 
 * that are missing them.
 * 
 * Usage: ddev drush php-script scripts/fix_dummy_quiz_results.php (Local)
 *    or: drush php-script scripts/fix_dummy_quiz_results.php (Server)
 */

$result_storage = \Drupal::entityTypeManager()->getStorage('lms_quiz_result');
$response_storage = \Drupal::entityTypeManager()->getStorage('lms_question_response');

// Find all quiz results that have an empty field_responses.
$ids = $result_storage->getQuery()
  ->notExists('field_responses')
  ->accessCheck(FALSE)
  ->execute();

if (empty($ids)) {
  echo "All quiz results already have responses attached. Nothing to fix.\n";
  return;
}

$results = $result_storage->loadMultiple($ids);
echo "Found " . count($results) . " dummy quiz results missing responses. Generating now...\n";

$count = 0;
foreach ($results as $result) {
  $quiz = $result->get('field_quiz')->entity;
  if (!$quiz) {
    continue;
  }

  $questions = $quiz->get('field_questions')->referencedEntities();
  $total = count($questions);
  if ($total == 0) {
    continue;
  }

  $score = (float) $result->get('field_score')->value;
  $num_correct = round($total * ($score / 100));
  
  $response_ids = [];
  foreach ($questions as $index => $question) {
    $is_correct = ($index < $num_correct);
    
    $fields_resp = [
      'field_question' => $question->id(),
      'field_quiz' => $quiz->id(),
      'field_correct' => $is_correct,
      'field_score' => $is_correct ? 10 : 0, 
      'field_pending_review' => FALSE,
      'field_text_answer' => $is_correct ? 'Correct dummy answer submitted.' : 'Incorrect dummy answer submitted.',
    ];
    
    if (in_array($question->bundle(), ['single_choice', 'multiple_choice'])) {
      $choices = $question->get('field_choices')->referencedEntities();
      if (!empty($choices)) {
        $fields_resp['field_answers'] = ['target_id' => $choices[0]->id()];
        $fields_resp['field_text_answer'] = $choices[0]->label();
      }
    }
    
    $resp_entity = $response_storage->create($fields_resp);
    $resp_entity->save();
    $response_ids[] = ['target_id' => $resp_entity->id()];
  }

  // Update the result entity
  $result->set('field_responses', $response_ids);
  $result->save();
  $count++;
  
  echo "- Updated Result ID {$result->id()} with {$total} mock responses.\n";
}

echo "\n--- Done! Updated {$count} quiz results. ---\n";
