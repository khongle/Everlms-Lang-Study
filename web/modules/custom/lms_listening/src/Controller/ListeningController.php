<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lms_listening\Entity\ListeningAttempt;
use Drupal\lms_listening\Entity\ListeningPassage;
use Drupal\lms_listening\Service\ListeningPresentationService;
use Drupal\lms_listening\Service\ListeningQueueService;
use Drupal\lms_listening\Service\ListeningVisibilityService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Student-facing pages: passage list, listen view, questions, review.
 *
 * Follows ReadingController's shape (lms_reading) — a small ControllerBase
 * with #theme render arrays and drupalSettings for the JS to pick up —
 * without importing anything from that class, keeping the two modules
 * decoupled at the code level.
 */
final class ListeningController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
    protected ListeningVisibilityService $visibility,
    protected ListeningQueueService $queue,
    protected TimeInterface $time,
    protected ListeningPresentationService $presentation,
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
      $container->get('lms_listening.visibility'),
      $container->get('lms_listening.queue'),
      $container->get('datetime.time'),
      $container->get('lms_listening.presentation'),
    );
  }

  /**
   * Page: /learn/listening — every passage visible to the current user.
   *
   * Accepts an optional ?deck=ID to jump straight to passages built from
   * one specific deck — used as ListeningStartForm's fallback destination
   * (no passages matched the levels picked there, or none were picked at
   * all) rather than being the "Listening" link's direct target any more;
   * see that form's docblock.
   */
  public function myPassages(Request $request): array {
    $uid = (int) $this->currentUser->id();

    $storage = $this->entityTypeManager->getStorage('lms_listening_passage');
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
      /** @var \Drupal\lms_listening\Entity\ListeningPassage $passage */
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
        // Drives the bulk-select checkbox (see the template) — real entity
        // access (ListeningAccessControlHandler: owner OR 'edit lms_course'),
        // not just is_mine, so staff can bulk-delete passages they didn't
        // personally import too.
        'can_delete' => $passage->access('delete', $this->currentUser),
      ];
    }

    return [
      '#theme' => 'lms_listening_my_passages',
      '#passages' => $passages,
      '#cache' => ['contexts' => ['user']],
    ];
  }

  /**
   * Page: /learn/listening/{passage} — the Listen step.
   *
   * Zips field_dialogue_speakers/field_dialogue_lines into one row per
   * turn server-side (same as ReadingController::read()) and never sends
   * the transcript/hints/translation any more "revealed" than the raw
   * field values themselves — hiding them until the student asks is done
   * entirely client-side (see js/listening-passage.js), the same way
   * Reading's word-popup never sends answers ahead of time.
   */
  public function listen(ListeningPassage $lms_listening_passage): array {
    $this->assertVisible($lms_listening_passage);

    $dialogue_turns = $this->presentation->buildDialogueTurns($lms_listening_passage);

    $hints = array_column($lms_listening_passage->get('field_hints')->getValue(), 'value');
    // Pre-formatted here (matching lms_flashcard's DeckForm "Seen N times"
    // line) rather than passing the raw int and pluralizing in Twig —
    // Drupal's Twig layer has no built-in plural filter, only the
    // formatPlural()/{% trans %}{% plural %} machinery, and this module's
    // templates don't otherwise pull that in.
    $times_seen_label = $this->formatPlural($this->recordPassageSeen($lms_listening_passage), 'Seen 1 time', 'Seen @count times');

    return [
      '#theme' => 'lms_listening_passage_view',
      '#passage' => $lms_listening_passage,
      '#dialogue_turns' => $dialogue_turns,
      '#hints' => $hints,
      '#times_seen_label' => $times_seen_label,
      '#attached' => [
        'library' => ['lms_listening/listening_passage', 'lms_note/note_widget'],
        'drupalSettings' => [
          'lmsListening' => [
            'passageId' => (int) $lms_listening_passage->id(),
            'questionsUrl' => '/learn/listening/' . $lms_listening_passage->id() . '/questions',
            // Hint text isn't in the rendered DOM at all (unlike the
            // transcript/translation, which ARE present but hidden) — the
            // template only knows whether hints *exist* (to show the
            // button), so js/listening-passage.js needs the actual text
            // delivered out-of-band here, revealed into the DOM one at a
            // time only after a Hint click.
            'hints' => $hints,
            // Used to pick a voice/accent for the TTS playback — same
            // field/purpose as lms_reading's drupalSettings.lmsReading.language.
            'language' => $lms_listening_passage->get('field_deck')->entity?->get('field_language')->value ?: 'en',
          ],
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Page: /learn/listening-passive/{passage} — the Passive Listening
   * session shell (see PASSIVE_LISTENING_PLAN.md). Renders only the FIRST
   * passage of the session (whatever ListeningApiController::start()
   * queued as passage_ids[0] for mode=passive) — the 2nd/3rd passage never
   * get a page load of their own; js/listening-passive.js fetches them as
   * JSON from ListeningApiController::passiveNext() and updates this same
   * page in place, so the whole 3-passage sequence plays inside one
   * continuous browser tab/user-gesture chain (see that plan doc's notes
   * on why: browsers can be inconsistent about allowing
   * speechSynthesis.speak() outside of one).
   *
   * No comprehension step, no ListeningAttempt — passive mode is
   * deliberately unscored. recordPassageSeen() still runs, exactly like
   * Active mode's listen(), so the "Seen N times" counter stays accurate
   * regardless of which mode a passage was viewed through.
   */
  public function listenPassive(ListeningPassage $lms_listening_passage): array {
    $this->assertVisible($lms_listening_passage);

    $deck = $lms_listening_passage->get('field_deck')->entity;
    $this->recordPassageSeen($lms_listening_passage);

    return [
      '#theme' => 'lms_listening_passive_view',
      '#passage' => $lms_listening_passage,
      '#attached' => [
        'library' => ['lms_listening/listening_passive', 'lms_note/note_widget'],
        'drupalSettings' => [
          'lmsListeningPassive' => [
            'deckId' => $deck ? (int) $deck->id() : NULL,
            'nextUrl' => '/api/lms/listening/passive-next',
            'language' => $deck?->get('field_language')->value ?: 'en',
            'passage' => [
              'id' => (int) $lms_listening_passage->id(),
              'title' => $lms_listening_passage->label(),
              'level' => $lms_listening_passage->get('field_level')->value,
              'transcript' => $this->presentation->buildPassiveTranscript($lms_listening_passage),
              'vocabTerms' => $this->presentation->buildVocabTerms($lms_listening_passage),
              'translation' => $this->presentation->buildTranslation($lms_listening_passage),
            ],
          ],
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Page: /learn/listening/{passage}/questions — the comprehension step.
   * Correct answers are never sent to the client — grading happens
   * server-side in ListeningApiController::submitAttempt().
   */
  public function questions(ListeningPassage $lms_listening_passage): array {
    $this->assertVisible($lms_listening_passage);

    $questions = [];
    foreach ($lms_listening_passage->get('field_questions')->referencedEntities() as $question) {
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

    return [
      '#theme' => 'lms_listening_questions',
      '#passage' => $lms_listening_passage,
      '#attached' => [
        'library' => ['lms_listening/listening_passage'],
        'drupalSettings' => [
          'lmsListening' => [
            'passageId' => (int) $lms_listening_passage->id(),
            'questions' => $questions,
            'submitUrl' => '/api/lms/listening/attempt',
          ],
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Page: /learn/listening/{passage}/review/{attempt} — the Review screen.
   */
  public function review(ListeningPassage $lms_listening_passage, ListeningAttempt $lms_listening_attempt): array {
    $this->assertVisible($lms_listening_passage);

    $uid = (int) $this->currentUser->id();
    if ((int) $lms_listening_attempt->getOwnerId() !== $uid
      || (int) $lms_listening_attempt->get('field_passage')->target_id !== (int) $lms_listening_passage->id()) {
      throw new NotFoundHttpException();
    }

    return [
      '#theme' => 'lms_listening_review',
      '#passage' => $lms_listening_passage,
      '#attempt' => $lms_listening_attempt,
      '#next_passage_id' => $this->findNextPassageId($lms_listening_passage),
      '#attached' => [
        'library' => ['lms_listening/listening_pages'],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Denies access to a passage the current user isn't allowed to listen to.
   */
  private function assertVisible(ListeningPassage $passage): void {
    if (!$this->visibility->isPassageVisibleTo($passage, (int) $this->currentUser->id())) {
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * Records that $passage's Listen step was opened by the current user;
   * returns the updated "times seen" count for the "Seen N times" line on
   * that page. Mirrors FlashcardApiController::recordCardSeen() exactly —
   * counts every open (not just a completed attempt), via the separate
   * ListeningPassageStat entity so this never touches ListeningAttempt
   * (which does mark a completed run) — see that entity's class docblock.
   */
  private function recordPassageSeen(ListeningPassage $passage): int {
    $uid = (int) $this->currentUser->id();
    $stat_storage = $this->entityTypeManager->getStorage('lms_listening_passage_stat');
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

  /**
   * Finds the next passage after $passage, so the Review screen can offer
   * a "Next passage" link straight into it instead of sending the student
   * back to the full /learn/listening grid after every single item — the
   * grid is often a dozen-plus short "sentence" passages from one deck,
   * and re-finding + re-clicking the next card each time is the friction
   * this exists to remove.
   *
   * If $passage is part of an active ListeningStartForm queue (the student
   * picked specific levels to practice), "next" walks that queue instead —
   * see ListeningQueueService::nextAfter() — stopping once it's exhausted
   * rather than spilling into deck passages the student didn't ask for.
   * Otherwise (organic browsing, no queue), "next" falls back to the next
   * passage in the same deck, same 'created' DESC order myPassages() builds
   * the grid in, so it always matches what the student would click next
   * there. Returns NULL if there's nothing left either way.
   */
  private function findNextPassageId(ListeningPassage $passage): ?int {
    $deck_id = $passage->get('field_deck')->target_id;
    if (!$deck_id) {
      return NULL;
    }

    $current_id = (int) $passage->id();
    $uid = (int) $this->currentUser->id();

    $queued = $this->queue->nextAfter($uid, (int) $deck_id, $current_id);
    if ($queued['in_queue']) {
      return $queued['next_id'];
    }

    $storage = $this->entityTypeManager->getStorage('lms_listening_passage');
    $ids = $storage->getQuery()
      ->condition('status', TRUE)
      ->condition('field_deck', $deck_id)
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();

    $found_current = FALSE;
    foreach ($storage->loadMultiple($ids) as $candidate) {
      /** @var \Drupal\lms_listening\Entity\ListeningPassage $candidate */
      if (!$this->visibility->isPassageVisibleTo($candidate, $uid)) {
        continue;
      }
      if ($found_current) {
        return (int) $candidate->id();
      }
      if ((int) $candidate->id() === $current_id) {
        $found_current = TRUE;
      }
    }

    return NULL;
  }

}
