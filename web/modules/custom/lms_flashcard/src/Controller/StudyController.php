<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\lms_flashcard\Entity\Deck;
use Drupal\lms_flashcard\Service\StudyQueueBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Student-facing pages: deck list and the flashcard study session.
 */
final class StudyController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
    protected StudyQueueBuilder $queueBuilder,
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
      $container->get('lms_flashcard.study_queue_builder'),
    );
  }

  /**
   * Page: /learn/flashcards — every deck visible to the current user, with
   * due/new counts.
   *
   * A deck is visible if it's published and either (a) the current user
   * owns it, or (b) its owner is staff (has course-management permission)
   * — i.e. an "official" deck meant for everyone. A deck a student
   * self-imports via /manage/flashcard-decks/import is otherwise private to
   * them, matching lms_note's per-user "own content" model.
   */
  public function myDecks(): array {
    $uid = (int) $this->currentUser->id();

    $storage = $this->entityTypeManager->getStorage('lms_flashcard_deck');
    $deckIds = $storage->getQuery()
      ->condition('status', TRUE)
      ->sort('name')
      ->accessCheck(FALSE)
      ->execute();

    $decks = [];
    foreach ($storage->loadMultiple($deckIds) as $deck) {
      /** @var \Drupal\lms_flashcard\Entity\Deck $deck */
      if (!$this->queueBuilder->isDeckVisibleTo($deck, $uid)) {
        continue;
      }

      $counts = $this->queueBuilder->getDueCounts($uid, (int) $deck->id());
      $decks[] = [
        'entity' => $deck,
        'name' => $deck->label(),
        'description' => $deck->get('field_description')->value,
        'language' => $deck->get('field_language')->value,
        'card_count' => $deck->get('field_cards')->count(),
        'due_count' => $counts['due'],
        'new_count' => $counts['new'],
        'is_mine' => (int) $deck->getOwnerId() === $uid,
      ];
    }

    return [
      '#theme' => 'lms_flashcard_my_decks',
      '#decks' => $decks,
      '#cache' => ['contexts' => ['user']],
    ];
  }

  /**
   * Page: /learn/flashcards/{deck}/study — the flip-card study session.
   *
   * Accepts an optional ?direction=forward|reverse|mixed, chosen by the
   * student on the My Flashcards page (see the 3 links in
   * lms-flashcard-my-decks.html.twig) — passed through to the front end,
   * which sends it back on the *first* /api/lms/flashcard/next call of the
   * session; FlashcardApiController::nextCard() is what actually persists
   * it for the rest of the session.
   *
   * Also accepts an optional ?mode=practice, from the "Practice all cards"
   * link on the same page — drills every card in the deck regardless of SRS
   * due dates, without touching the student's real review schedule (see
   * StudyQueueBuilder::buildQueue() and FlashcardApiController::submitReview()).
   */
  public function study(Deck $lms_flashcard_deck, Request $request): array {
    $this->assertVisible($lms_flashcard_deck);

    $direction = $request->query->get('direction', 'forward');
    if (!in_array($direction, ['forward', 'reverse', 'mixed'], TRUE)) {
      $direction = 'forward';
    }
    $practice = $request->query->get('mode') === 'practice';

    return [
      '#theme' => 'lms_flashcard_study_session',
      '#deck' => $lms_flashcard_deck,
      '#practice' => $practice,
      '#attached' => [
        'library' => ['lms_flashcard/flashcard_study'],
        'drupalSettings' => [
          'lmsFlashcard' => [
            'deckId' => (int) $lms_flashcard_deck->id(),
            // Used to pick a voice/accent for the text-to-speech fallback
            // when a card has no uploaded pronunciation MP3 (see
            // js/flashcard-study.js speakCard()).
            'language' => $lms_flashcard_deck->get('field_language')->value ?: 'en',
            'direction' => $direction,
            'practice' => $practice,
            'nextUrl' => '/api/lms/flashcard/next',
            'reviewUrl' => '/api/lms/flashcard/review',
            'summaryUrl' => '/learn/flashcards/' . $lms_flashcard_deck->id() . '/summary' . ($practice ? '?mode=practice' : ''),
          ],
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Page: /learn/flashcards/{deck}/summary — end-of-session recap.
   *
   * The optional ?mode=practice (carried over from the study page's
   * drupalSettings.lmsFlashcard.summaryUrl, see study() above) just
   * controls the recap copy/CTA below — it never affects #remaining_due /
   * #remaining_new, which always reflect the real SRS schedule regardless
   * of how the session that just ended was studied.
   */
  public function summary(Deck $lms_flashcard_deck, Request $request): array {
    $this->assertVisible($lms_flashcard_deck);

    $uid = (int) $this->currentUser->id();
    $counts = $this->queueBuilder->getDueCounts($uid, (int) $lms_flashcard_deck->id());

    return [
      '#theme' => 'lms_flashcard_session_summary',
      '#deck' => $lms_flashcard_deck,
      '#remaining_due' => $counts['due'],
      '#remaining_new' => $counts['new'],
      '#practice' => $request->query->get('mode') === 'practice',
      '#attached' => ['library' => ['lms_flashcard/flashcard_pages']],
    ];
  }

  /**
   * Denies access to a deck the current user isn't allowed to study.
   *
   * Guards direct/guessed URLs since these routes are only gated by
   * _user_is_logged_in, not per-deck entity access.
   */
  private function assertVisible(Deck $deck): void {
    if (!$this->queueBuilder->isDeckVisibleTo($deck, (int) $this->currentUser->id())) {
      throw new AccessDeniedHttpException();
    }
  }

}
