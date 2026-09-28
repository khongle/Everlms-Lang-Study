<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Workspace administration pages for all users' flashcard content.
 */
final class FlashcardAdminController extends ControllerBase {

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    protected DateFormatterInterface $dateFormatter,
  ) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('date.formatter'),
    );
  }

  /**
   * Lists every flashcard deck, including student-owned private decks.
   */
  public function decks(Request $request): array|RedirectResponse {
    if (!$this->entityTypeManager->hasDefinition('lms_flashcard_deck')
      || !$this->entityTypeManager->hasDefinition('lms_flashcard_card')) {
      $this->getLogger('lms_flashcard')->warning('Flashcard management was requested before its entity definitions were available.');
      $this->messenger()->addWarning($this->t('Flashcard management is temporarily unavailable while deployment finishes.'));
      return $this->redirect('lms_course.admin_dashboard');
    }

    $search = trim((string) $request->query->get('search', ''));
    $owner = (int) $request->query->get('owner', 0);
    $status = (string) $request->query->get('status', 'all');
    if (!in_array($status, ['all', 'published', 'draft'], TRUE)) {
      $status = 'all';
    }

    $storage = $this->entityTypeManager->getStorage('lms_flashcard_deck');
    $all_ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->execute();
    $all_decks = $all_ids ? $storage->loadMultiple($all_ids) : [];

    $owner_ids = [];
    $published_count = 0;
    foreach ($all_decks as $deck) {
      $owner_ids[(int) $deck->getOwnerId()] = (int) $deck->getOwnerId();
      $published_count += (int) (bool) $deck->get('status')->value;
    }

    $users = $owner_ids
      ? $this->entityTypeManager->getStorage('user')->loadMultiple($owner_ids)
      : [];
    $owner_options = [];
    foreach ($users as $user) {
      $owner_options[$user->id()] = $user->getDisplayName();
    }
    natcasesort($owner_options);

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->sort('changed', 'DESC')
      ->pager(25);
    if ($search !== '') {
      $query->condition('name', '%' . $search . '%', 'LIKE');
    }
    if ($owner > 0) {
      $query->condition('uid', $owner);
    }
    if ($status !== 'all') {
      $query->condition('status', $status === 'published');
    }

    $deck_ids = $query->execute();
    $decks = [];
    foreach ($storage->loadMultiple($deck_ids) as $deck) {
      $owner_account = $users[$deck->getOwnerId()] ?? $deck->getOwner();
      $topic = $deck->get('field_topic')->entity;
      $course = $deck->get('field_course')->entity;
      $lesson = $deck->get('field_lesson')->entity;
      $decks[] = [
        'id' => (int) $deck->id(),
        'name' => $deck->label(),
        'machine_name' => $deck->get('field_machine_name')->value ?: '-',
        'owner_name' => $owner_account?->getDisplayName() ?? $this->t('Deleted user'),
        'owner_id' => (int) $deck->getOwnerId(),
        'topic' => $topic?->label() ?? $this->t('Uncategorized'),
        'course_name' => $course?->label() ?? '',
        'course_id' => $course ? (int) $course->id() : NULL,
        'lesson_name' => $lesson?->label() ?? '',
        'lesson_id' => $lesson ? (int) $lesson->id() : NULL,
        'card_count' => $deck->get('field_cards')->count(),
        'status' => $deck->get('status')->value ? 'published' : 'draft',
        'changed' => $this->dateFormatter->format((int) $deck->get('changed')->value, 'short'),
        'edit_url' => $deck->toUrl('edit-form', [
          'query' => ['destination' => '/workspace/flashcards'],
        ])->toString(),
        'delete_url' => $deck->toUrl('delete-form', [
          'query' => ['destination' => '/workspace/flashcards'],
        ])->toString(),
      ];
    }

    $card_count = (int) $this->entityTypeManager
      ->getStorage('lms_flashcard_card')
      ->getQuery()
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return [
      '#theme' => 'lms_flashcard_admin_decks',
      '#decks' => $decks,
      '#pager' => ['#type' => 'pager'],
      '#stats' => [
        'decks' => count($all_decks),
        'cards' => $card_count,
        'owners' => count(array_filter($owner_ids)),
        'published' => $published_count,
      ],
      '#filters' => [
        'search' => $search,
        'owner' => $owner,
        'status' => $status,
      ],
      '#owner_options' => $owner_options,
      '#add_url' => Url::fromRoute('entity.lms_flashcard_deck.add_form', [], [
        'query' => ['destination' => '/workspace/flashcards'],
      ])->toString(),
      '#import_url' => Url::fromRoute('lms_flashcard.import')->toString(),
      '#cache' => [
        'contexts' => ['url.query_args'],
        'tags' => ['lms_flashcard_deck_list', 'lms_flashcard_card_list', 'user_list'],
      ],
    ];
  }

}
