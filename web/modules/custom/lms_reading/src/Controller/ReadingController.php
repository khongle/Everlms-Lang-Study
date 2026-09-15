<?php

declare(strict_types=1);

namespace Drupal\lms_reading\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lms_reading\Entity\ReadingAttempt;
use Drupal\lms_reading\Entity\ReadingPassage;
use Drupal\lms_reading\Service\ReadingVisibilityService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Student-facing pages: passage list, read view, questions, review.
 *
 * Follows StudyController's shape (lms_flashcard) — a small ControllerBase
 * with #theme render arrays and drupalSettings for the JS to pick up —
 * without importing anything from that class, keeping the two modules
 * decoupled at the code level.
 */
final class ReadingController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
    protected ReadingVisibilityService $visibility,
    protected TimeInterface $time,
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
      $container->get('datetime.time'),
    );
  }

  /**
   * Page: /learn/reading — every passage visible to the current user.
   *
   * Accepts an optional ?deck=ID (used by the "Reading" link added to
   * lms_flashcard's My Flashcards page — see templates/lms-flashcard-my-decks.html.twig
   * there) to jump straight to passages built from one specific deck,
   * instead of the full unfiltered list.
   */
  public function myPassages(Request $request): array {
    $uid = (int) $this->currentUser->id();

    $storage = $this->entityTypeManager->getStorage('lms_reading_passage');
    $query = $storage->getQuery()
      ->condition('status', TRUE)
      ->sort('created', 'DESC')
      ->accessCheck(FALSE);
    $deckFilter = (int) $request->query->get('deck');
    if ($deckFilter) {
      $query->condition('field_deck', $deckFilter);
    }
    $ids = $query->execute();

    $passages = [];
    foreach ($storage->loadMultiple($ids) as $passage) {
      /** @var \Drupal\lms_reading\Entity\ReadingPassage $passage */
      if (!$this->visibility->isPassageVisibleTo($passage, $uid)) {
        continue;
      }

      $deck = $passage->get('field_deck')->entity;
      $passages[] = [
        'entity' => $passage,
        'title' => $passage->label(),
        'deck_name' => $deck?->label() ?? '',
        'level' => $passage->get('field_level')->value,
        'format' => $passage->get('field_format')->value,
        'word_count' => (int) $passage->get('field_word_count')->value,
        'question_count' => $passage->get('field_questions')->count(),
        'is_mine' => (int) $passage->getOwnerId() === $uid,
      ];
    }

    return [
      '#theme' => 'lms_reading_my_passages',
      '#passages' => $passages,
      // Libraries attached from the template itself (attach_library() at
      // the top of lms-reading-my-passages.html.twig), matching how
      // lms_flashcard's StudyController::myDecks() does it for the
      // equivalent "My Flashcards" page — not repeated here.
      '#cache' => ['contexts' => ['user']],
    ];
  }

  /**
   * Page: /learn/reading/{passage} — the Read step.
   *
   * Attaches a lightweight "highlight index" via drupalSettings: every
   * covered card's term (source=card) plus every word-note's term
   * (source=note), so js/reading-passage.js can highlight matches in
   * field_body client-side and know which endpoint-friendly id to send to
   * /api/lms/reading/word-info on click, without a round trip just to know
   * which words ARE clickable.
   */
  public function read(ReadingPassage $lms_reading_passage): array {
    $this->assertVisible($lms_reading_passage);

    $highlight_terms = [];
    foreach ($lms_reading_passage->get('field_cards')->referencedEntities() as $card) {
      $highlight_terms[] = ['term' => $card->label(), 'source' => 'card', 'id' => (int) $card->id()];
    }

    $note_storage = $this->entityTypeManager->getStorage('lms_reading_word_note');
    $note_ids = $note_storage->getQuery()
      ->condition('field_passage', $lms_reading_passage->id())
      ->accessCheck(FALSE)
      ->execute();
    foreach ($note_storage->loadMultiple($note_ids) as $note) {
      $highlight_terms[] = ['term' => $note->label(), 'source' => 'note', 'id' => (int) $note->id()];
    }

    // Zipped here rather than in Twig (which has no built-in "zip" for two
    // parallel multi-value fields) — one {speaker, line} row per dialogue
    // turn, in order. Empty array (and simply unused by the template) for
    // a Narrative passage.
    $speakers = array_column($lms_reading_passage->get('field_dialogue_speakers')->getValue(), 'value');
    $lines = array_column($lms_reading_passage->get('field_dialogue_lines')->getValue(), 'value');
    $dialogue_turns = [];
    foreach ($speakers as $index => $speaker) {
      if (isset($lines[$index])) {
        $dialogue_turns[] = ['speaker' => $speaker, 'line' => $lines[$index]];
      }
    }

    // Pre-formatted here (matching lms_flashcard's DeckForm "Seen N times"
    // line, and lms_listening's own equivalent on its Listen step) rather
    // than passing the raw int and pluralizing in Twig — Drupal's Twig
    // layer has no built-in plural filter, only the formatPlural()/
    // {% trans %}{% plural %} machinery, and this module's templates don't
    // otherwise pull that in.
    $times_seen_label = $this->formatPlural($this->recordPassageSeen($lms_reading_passage), 'Seen 1 time', 'Seen @count times');

    return [
      '#theme' => 'lms_reading_passage_view',
      '#passage' => $lms_reading_passage,
      '#dialogue_turns' => $dialogue_turns,
      '#times_seen_label' => $times_seen_label,
      '#attached' => [
        'library' => ['lms_reading/reading_passage', 'lms_note/note_widget'],
        'drupalSettings' => [
          'lmsReading' => [
            'passageId' => (int) $lms_reading_passage->id(),
            'highlightTerms' => $highlight_terms,
            'wordInfoUrl' => '/api/lms/reading/word-info',
            'questionsUrl' => '/learn/reading/' . $lms_reading_passage->id() . '/questions',
            // Used to pick a voice/accent for the "Listen" read-aloud
            // feature (Web Speech API) — same field, same purpose as
            // StudyController::study()'s drupalSettings.lmsFlashcard.language.
            'language' => $lms_reading_passage->get('field_deck')->entity?->get('field_language')->value ?: 'en',
          ],
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Page: /learn/reading/{passage}/questions — Understand + Guess from
   * Context steps. Correct answers are never sent to the client — grading
   * happens server-side in ReadingApiController::submitAttempt().
   */
  public function questions(ReadingPassage $lms_reading_passage): array {
    $this->assertVisible($lms_reading_passage);

    $questions = [];
    foreach ($lms_reading_passage->get('field_questions')->referencedEntities() as $question) {
      $row = [
        'id' => (int) $question->id(),
        'type' => $question->bundle(),
        'text' => $question->label(),
      ];
      if ($question->bundle() === 'multiple_choice') {
        $row['choices'] = [];
        foreach ($question->get('field_choices')->referencedEntities() as $answer) {
          $row['choices'][] = ['id' => (int) $answer->id(), 'text' => $answer->label()];
        }
      }
      $questions[] = $row;
    }

    $context_items = [];
    foreach ($lms_reading_passage->get('field_context_items')->referencedEntities() as $item) {
      $context_items[] = [
        'id' => (int) $item->id(),
        'target_word' => $item->label(),
        'context_sentence' => trim(strip_tags((string) $item->get('field_context_sentence')->value)),
        'choices' => array_column($item->get('field_choices')->getValue(), 'value'),
      ];
    }

    return [
      '#theme' => 'lms_reading_questions',
      '#passage' => $lms_reading_passage,
      '#attached' => [
        'library' => ['lms_reading/reading_passage'],
        'drupalSettings' => [
          'lmsReading' => [
            'passageId' => (int) $lms_reading_passage->id(),
            'questions' => $questions,
            'contextItems' => $context_items,
            'submitUrl' => '/api/lms/reading/attempt',
          ],
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Page: /learn/reading/{passage}/review/{attempt} — the Review screen.
   */
  public function review(ReadingPassage $lms_reading_passage, ReadingAttempt $lms_reading_attempt): array {
    $this->assertVisible($lms_reading_passage);

    $uid = (int) $this->currentUser->id();
    if ((int) $lms_reading_attempt->getOwnerId() !== $uid
      || (int) $lms_reading_attempt->get('field_passage')->target_id !== (int) $lms_reading_passage->id()) {
      throw new NotFoundHttpException();
    }

    $unknown_terms = array_column($lms_reading_attempt->get('field_unknown_terms')->getValue(), 'value');
    // Computed here rather than in the template: Twig's Drupal sandbox
    // policy disallows calling ->count() on an EntityReferenceFieldItemList
    // directly (only a safelisted set of methods/filters are allowed) —
    // see lms-reading-review.html.twig for where this is used.
    $words_clicked_count = $lms_reading_attempt->get('field_clicked_cards')->count()
      + $lms_reading_attempt->get('field_clicked_notes')->count();

    return [
      '#theme' => 'lms_reading_review',
      '#passage' => $lms_reading_passage,
      '#attempt' => $lms_reading_attempt,
      '#unknown_terms' => $unknown_terms,
      '#words_clicked_count' => $words_clicked_count,
      '#attached' => [
        'library' => ['lms_reading/reading_pages', 'lms_reading/reading_review'],
        'drupalSettings' => [
          'lmsReading' => [
            'attemptId' => (int) $lms_reading_attempt->id(),
            'addUnknownWordsUrl' => '/api/lms/reading/add-unknown-words',
            'myPassagesUrl' => '/learn/reading',
          ],
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Denies access to a passage the current user isn't allowed to read.
   */
  private function assertVisible(ReadingPassage $passage): void {
    if (!$this->visibility->isPassageVisibleTo($passage, (int) $this->currentUser->id())) {
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * Records that $passage's Read step was opened by the current user;
   * returns the updated "times seen" count for the "Seen N times" line on
   * that page. Mirrors FlashcardApiController::recordCardSeen() (and
   * lms_listening's own ListeningController::recordPassageSeen()) exactly
   * — counts every open (not just a completed attempt), via the separate
   * ReadingPassageStat entity so this never touches ReadingAttempt (which
   * does mark a completed run) — see that entity's class docblock.
   */
  private function recordPassageSeen(ReadingPassage $passage): int {
    $uid = (int) $this->currentUser->id();
    $stat_storage = $this->entityTypeManager->getStorage('lms_reading_passage_stat');
    $stat_ids = $stat_storage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_passage', $passage->id())
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();

    $stat = $stat_ids ? $stat_storage->load(reset($stat_ids)) : NULL;
    $stat ??= $stat_storage->create(['uid' => $uid, 'field_passage' => $passage->id()]);

    $times_seen = ((int) ($stat->get('field_times_seen')->value ?? 0)) + 1;
    $stat->set('field_times_seen', $times_seen);
    $stat->set('field_last_seen_at', $this->time->getRequestTime());
    $stat->save();

    return $times_seen;
  }

}
