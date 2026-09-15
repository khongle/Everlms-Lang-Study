<?php

declare(strict_types=1);

namespace Drupal\lms_reading\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lms_reading\Entity\ReadingPassage;
use Drupal\lms_reading\Service\ReadingVisibilityService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * AJAX endpoints backing the reading passage UI.
 *
 * Follows lms_note's "own content" convention (also used by
 * FlashcardApiController): routes are gated only by _user_is_logged_in, and
 * every query here is scoped by the resolved uid / passage visibility
 * rather than relying on entity-level access grants.
 */
final class ReadingApiController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
    protected ReadingVisibilityService $visibility,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('lms_reading.visibility'),
    );
  }

  /**
   * GET /api/lms/reading/word-info?passage=ID&term=STR
   *
   * Resolves a clicked word's popup info: a matching Card in the passage's
   * field_cards first, falling back to a ReadingWordNote for the passage —
   * see the class docblock on ReadingWordNote for why in that order.
   */
  public function wordInfo(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $passage = $this->loadVisiblePassage((int) $request->query->get('passage'), $uid);
    if (!$passage) {
      return new JsonResponse(['error' => 'Passage not found'], 404);
    }

    $term = trim((string) $request->query->get('term', ''));
    if ($term === '') {
      return new JsonResponse(['error' => 'Missing term'], 400);
    }
    $key = mb_strtolower($term);

    foreach ($passage->get('field_cards')->referencedEntities() as $card) {
      if (mb_strtolower((string) $card->label()) === $key) {
        return new JsonResponse([
          'found' => TRUE,
          'source' => 'card',
          'id' => (int) $card->id(),
          'term' => $card->label(),
          'meaning' => trim(strip_tags((string) $card->get('field_definition')->value)),
          'pronunciation' => (string) $card->get('field_pronunciation')->value,
          'part_of_speech' => (string) $card->get('field_part_of_speech')->value,
        ]);
      }
    }

    $note_storage = $this->entityTypeManager->getStorage('lms_reading_word_note');
    $note_ids = $note_storage->getQuery()
      ->condition('field_passage', $passage->id())
      ->condition('name', $term)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();
    if ($note_ids) {
      $note = $note_storage->load(reset($note_ids));
      return new JsonResponse([
        'found' => TRUE,
        'source' => 'note',
        'id' => (int) $note->id(),
        'term' => $note->label(),
        'meaning' => trim(strip_tags((string) $note->get('field_meaning')->value)),
        'pronunciation' => (string) $note->get('field_pronunciation')->value,
        'part_of_speech' => (string) $note->get('field_part_of_speech')->value,
      ]);
    }

    return new JsonResponse(['found' => FALSE]);
  }

  /**
   * POST /api/lms/reading/attempt
   *
   * Body: {
   *   passage_id, duration_seconds,
   *   answers: {questionId: answerValue, ...},
   *   context_answers: {contextItemId: selectedIndex, ...},
   *   clicked_cards: [cardId, ...], clicked_notes: [noteId, ...],
   *   unknown_terms: [term, ...]
   * }
   *
   * `answerValue` shape depends on the question's type (see
   * questions()/read the field_choices/etc off the loaded lms_question
   * entity rather than trusting anything about type from the client):
   * multiple_choice -> selected lms_answer id (int); true_false -> bool;
   * fill_blank/short_answer -> string.
   *
   * Grading happens entirely here, from the entities themselves — never
   * trusting a "correct"/"score" value from the client.
   */
  public function submitAttempt(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $body = json_decode((string) $request->getContent(), TRUE);
    if (!is_array($body)) {
      return new JsonResponse(['error' => 'Invalid JSON body'], 400);
    }

    $passage = $this->loadVisiblePassage((int) ($body['passage_id'] ?? 0), $uid);
    if (!$passage) {
      return new JsonResponse(['error' => 'Passage not found'], 404);
    }

    $answers = is_array($body['answers'] ?? NULL) ? $body['answers'] : [];
    $context_answers = is_array($body['context_answers'] ?? NULL) ? $body['context_answers'] : [];

    [$correct, $total] = $this->gradeQuestions($passage, $answers);
    [$context_correct, $context_total] = $this->gradeContextItems($passage, $context_answers);
    $correct += $context_correct;
    $total += $context_total;
    $score = $total > 0 ? (int) round($correct / $total * 100) : 0;

    $clicked_cards = $this->sanitizeIntList($body['clicked_cards'] ?? []);
    $clicked_notes = $this->sanitizeIntList($body['clicked_notes'] ?? []);
    $unknown_terms = array_values(array_unique(array_filter(array_map(
      static fn ($term) => trim((string) $term),
      is_array($body['unknown_terms'] ?? NULL) ? $body['unknown_terms'] : [],
    ), static fn ($term) => $term !== '')));

    $attempt_storage = $this->entityTypeManager->getStorage('lms_reading_attempt');
    $attempt = $attempt_storage->create([
      'uid' => $uid,
      'field_passage' => $passage->id(),
      'field_score' => $score,
      'field_correct_count' => $correct,
      'field_question_count' => $total,
      'field_duration_seconds' => max(0, (int) ($body['duration_seconds'] ?? 0)),
      'field_clicked_cards' => $clicked_cards,
      'field_clicked_notes' => $clicked_notes,
      'field_unknown_terms' => $unknown_terms,
    ]);
    $attempt->save();

    return new JsonResponse([
      'attempt_id' => (int) $attempt->id(),
      'score' => $score,
      'correct_count' => $correct,
      'question_count' => $total,
      'review_url' => '/learn/reading/' . $passage->id() . '/review/' . $attempt->id(),
    ]);
  }

  /**
   * POST /api/lms/reading/add-unknown-words
   *
   * Body: {attempt_id}. Creates a new lms_flashcard_card (via the entity
   * API only — see ReadingPassage's class docblock) for every term in the
   * attempt's field_unknown_terms that isn't already a card in the
   * passage's deck, then attaches them to that deck. Guarded by
   * field_added_to_flashcards so pressing the button twice never
   * duplicates cards.
   */
  public function addUnknownWords(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $body = json_decode((string) $request->getContent(), TRUE);
    $attempt_id = (int) ($body['attempt_id'] ?? 0);

    $attempt_storage = $this->entityTypeManager->getStorage('lms_reading_attempt');
    $attempt = $attempt_id ? $attempt_storage->load($attempt_id) : NULL;
    if (!$attempt || (int) $attempt->getOwnerId() !== $uid) {
      return new JsonResponse(['error' => 'Attempt not found'], 404);
    }
    if ($attempt->get('field_added_to_flashcards')->value) {
      return new JsonResponse(['error' => 'Already added'], 409);
    }

    $passage = $attempt->get('field_passage')->entity;
    if (!$passage instanceof ReadingPassage) {
      return new JsonResponse(['error' => 'Passage not found'], 404);
    }
    $deck = $passage->get('field_deck')->entity;
    if (!$deck) {
      return new JsonResponse(['error' => 'Deck not found'], 404);
    }

    $card_storage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $existing_ids = $card_storage->getQuery()
      ->condition('field_deck', $deck->id())
      ->accessCheck(FALSE)
      ->execute();
    $existing_terms = [];
    foreach ($card_storage->loadMultiple($existing_ids) as $card) {
      $existing_terms[mb_strtolower((string) $card->label())] = TRUE;
    }

    $note_storage = $this->entityTypeManager->getStorage('lms_reading_word_note');

    $added_count = 0;
    $new_card_ids = [];
    foreach (array_column($attempt->get('field_unknown_terms')->getValue(), 'value') as $term) {
      $key = mb_strtolower($term);
      if (isset($existing_terms[$key])) {
        continue;
      }

      $definition = '';
      $note_ids = $note_storage->getQuery()
        ->condition('field_passage', $passage->id())
        ->condition('name', $term)
        ->range(0, 1)
        ->accessCheck(FALSE)
        ->execute();
      if ($note_ids) {
        $note = $note_storage->load(reset($note_ids));
        $definition = trim(strip_tags((string) $note->get('field_meaning')->value));
      }
      if ($definition === '') {
        $definition = (string) $this->t('(Added from Reading — edit this definition.)');
      }

      $card = $card_storage->create([
        'field_deck' => $deck->id(),
        'name' => $term,
        'field_definition' => ['value' => $definition, 'format' => 'basic_html'],
      ]);
      $card->save();
      $new_card_ids[] = (int) $card->id();
      $existing_terms[$key] = TRUE;
      $added_count++;
    }

    if ($new_card_ids) {
      $existing_on_deck = array_map('intval', array_column($deck->get('field_cards')->getValue(), 'target_id'));
      $deck->set('field_cards', array_values(array_unique(array_merge($existing_on_deck, $new_card_ids))));
      $deck->save();
    }

    $attempt->set('field_added_to_flashcards', TRUE);
    $attempt->save();

    return new JsonResponse(['added_count' => $added_count]);
  }

  /**
   * Grades the "Understand" comprehension questions.
   *
   * @return array{0: int, 1: int}
   *   [correct_count, total_count].
   */
  private function gradeQuestions(ReadingPassage $passage, array $answers): array {
    $correct = 0;
    $total = 0;
    foreach ($passage->get('field_questions')->referencedEntities() as $question) {
      $total++;
      $given = $answers[$question->id()] ?? NULL;
      if ($given === NULL) {
        continue;
      }

      $is_correct = match ($question->bundle()) {
        'multiple_choice' => $this->isMultipleChoiceCorrect($question, $given),
        'true_false' => is_bool($given) && $given === (bool) $question->get('field_correct_answer')->value,
        'fill_blank', 'short_answer' => $this->isTextAnswerCorrect($question, $given),
        default => FALSE,
      };
      if ($is_correct) {
        $correct++;
      }
    }
    return [$correct, $total];
  }

  /**
   * Grades the "Guess from Context" items.
   *
   * @return array{0: int, 1: int}
   *   [correct_count, total_count].
   */
  private function gradeContextItems(ReadingPassage $passage, array $answers): array {
    $correct = 0;
    $total = 0;
    foreach ($passage->get('field_context_items')->referencedEntities() as $item) {
      $total++;
      if (!array_key_exists((string) $item->id(), $answers) && !array_key_exists($item->id(), $answers)) {
        continue;
      }
      $given = $answers[$item->id()] ?? $answers[(string) $item->id()] ?? NULL;
      if ((int) $given === (int) $item->get('field_correct_index')->value) {
        $correct++;
      }
    }
    return [$correct, $total];
  }

  private function isMultipleChoiceCorrect($question, mixed $given): bool {
    $selected_id = (int) $given;
    foreach ($question->get('field_choices')->referencedEntities() as $answer) {
      if ((int) $answer->id() === $selected_id) {
        return (bool) $answer->get('field_correct')->value;
      }
    }
    return FALSE;
  }

  private function isTextAnswerCorrect($question, mixed $given): bool {
    $given = mb_strtolower(trim((string) $given));
    if ($given === '') {
      return FALSE;
    }
    foreach (array_column($question->get('field_correct_answers')->getValue(), 'value') as $accepted) {
      if (mb_strtolower(trim((string) $accepted)) === $given) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Loads a passage only if it exists AND is visible to $uid.
   */
  private function loadVisiblePassage(int $passage_id, int $uid): ?ReadingPassage {
    if (!$passage_id) {
      return NULL;
    }
    $passage = $this->entityTypeManager->getStorage('lms_reading_passage')->load($passage_id);
    if (!$passage instanceof ReadingPassage || !$this->visibility->isPassageVisibleTo($passage, $uid)) {
      return NULL;
    }
    return $passage;
  }

  /**
   * Normalizes a client-supplied array into a deduplicated list of ints.
   */
  private function sanitizeIntList(mixed $value): array {
    if (!is_array($value)) {
      return [];
    }
    return array_values(array_unique(array_map('intval', array_filter($value, 'is_numeric'))));
  }

}
