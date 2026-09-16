<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\lms_listening\Service\ListeningQueueService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Level-picker "popup" shown before a Listening practice session starts.
 *
 * Route: /learn/listening-start?deck=ID — this is where the "Listening"
 * button on a Flashcard deck card (lms-flashcard-my-decks.html.twig) leads.
 * Rather than dropping the student straight into a passage, it asks which
 * level(s) they want to practice (any combination of Easy/Normal/
 * Challenge — a level with zero passages in this deck is disabled rather
 * than offered) and how many passages per level (default 3), builds a
 * small queue via ListeningQueueService (buildFromLevels(), unattempted
 * ones first) and redirects into the first passage in it. From there,
 * ListeningController's Review screen chains "Next passage" through the
 * rest of that same queue — see ListeningController::findNextPassageId().
 *
 * Implemented as a plain server-rendered form/page rather than a JS modal:
 * it needs no client-side script, works with JS disabled, and keeps
 * lms_flashcard's template decoupled from lms_listening the same way its
 * existing links already are (a plain hardcoded href, never a call into
 * this module's code) — see that template's comment on the "Listening"
 * link.
 */
final class ListeningStartForm extends FormBase {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ListeningQueueService $queue,
    protected AccountProxyInterface $currentUser,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('lms_listening.queue'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_listening_start_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attributes']['class'][] = 'lms-listening-start-form';

    $deck_id = (int) $this->getRequest()->query->get('deck');
    if (!$deck_id) {
      $form['message'] = [
        '#markup' => '<p>' . $this->t('No deck was specified.') . ' <a href="' . Url::fromRoute('lms_listening.my_passages')->toString() . '">' . $this->t('Back to Listening') . '</a></p>',
      ];
      return $form;
    }

    // Carried via a hidden value (rather than re-reading the query string
    // in submitForm()) so it survives the POST round-trip exactly as
    // validated here, same as any other form input.
    $form['deck'] = ['#type' => 'value', '#value' => $deck_id];

    $deck = $this->entityTypeManager->getStorage('lms_flashcard_deck')->load($deck_id);
    $deck_label = $deck ? $deck->label() : $this->t('this deck');

    $form['intro'] = [
      '#markup' => '<p class="lms-listening-start-form__intro">' . $this->t('Pick one or more levels to practice from %deck, and how many passages per level. Unattempted passages get queued up first.', ['%deck' => $deck_label]) . '</p>',
    ];

    $counts = $this->queue->countByLevel((int) $this->currentUser->id(), $deck_id);

    // Passive Listening (see PASSIVE_LISTENING_PLAN.md) — no-JS parity
    // with js/listening-start-modal.js's mode toggle. #states hides the
    // level/count fields below when Passive is picked, same "just a
    // progressive enhancement" spirit as the rest of this module: with JS
    // disabled the fields simply stay visible (harmless — they're just
    // ignored in that branch of submitForm() below), rather than the form
    // breaking.
    $form['mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Mode'),
      '#options' => [
        'active' => $this->t('Active — listen, then answer comprehension questions'),
        'passive' => $this->t('Passive — just listen to 3 passages (Easy → Normal → Challenge, picked at random), no questions'),
      ],
      '#default_value' => 'active',
    ];

    // Not #required here — Active mode needs at least one level, but
    // Passive mode's "specific passages" option below is a valid
    // alternative to picking any level at all, so the actual rule lives in
    // validateForm() instead, which can tell the two modes apart.
    $form['levels'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Levels'),
      '#options' => [
        'easy' => $this->t('Easy (@count available)', ['@count' => $counts['easy'] ?? 0]),
        'normal' => $this->t('Normal (@count available)', ['@count' => $counts['normal'] ?? 0]),
        'challenge' => $this->t('Challenge (@count available)', ['@count' => $counts['challenge'] ?? 0]),
      ],
    ];
    // A level with nothing to practice can't be picked at all, rather than
    // being offered and only failing (or silently contributing nothing)
    // after submission.
    foreach (['easy', 'normal', 'challenge'] as $level) {
      if (empty($counts[$level])) {
        $form['levels'][$level]['#disabled'] = TRUE;
      }
    }

    // Shared by both modes: Active applies it only to the levels checked
    // above; Passive (see PASSIVE_LISTENING_PLAN.md) always covers
    // whichever levels are checked above too (originally always all 3,
    // revised after the student asked to be able to narrow it down here
    // as well, same as Active mode already could).
    $form['per_level'] = [
      '#type' => 'number',
      '#title' => $this->t('Passages per level'),
      '#min' => 1,
      '#default_value' => 3,
    ];

    // Passive-only: pick exact passages instead of a random per-level
    // count — see PASSIVE_LISTENING_PLAN.md. Only shown (via #states) once
    // Passive is picked, and only built at all if the deck actually has
    // any passages to list.
    $passages = $this->queue->listPassagesForDeck((int) $this->currentUser->id(), $deck_id);
    if ($passages) {
      $options = [];
      foreach ($passages as $passage) {
        $format = ucfirst((string) ($passage['format'] ?? 'sentence'));
        $options[$passage['id']] = ucfirst($passage['level']) . ' - ' . $format . ' - ' . $passage['title'];
      }
      $form['specific_passages'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Or choose specific passages'),
        '#description' => $this->t('Checking any passage here plays exactly those (still Easy → Normal → Challenge order), instead of the random per-level pick above.'),
        '#options' => $options,
        '#states' => [
          'visible' => [':input[name="mode"]' => ['value' => 'passive']],
        ],
      ];
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Start practicing'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * Replaces the 'levels' field's old #required — Active mode still
   * requires at least one level, but Passive mode's "specific passages"
   * option is a valid alternative to picking any level at all, so the two
   * modes need different rules rather than one blanket #required.
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);

    $levels = array_values(array_filter((array) $form_state->getValue('levels')));
    if ($form_state->getValue('mode') === 'passive') {
      $specific = array_values(array_filter((array) $form_state->getValue('specific_passages', [])));
      if (!$levels && !$specific) {
        $form_state->setErrorByName('levels', $this->t('Pick at least one level, or choose specific passages below.'));
      }
      return;
    }

    if (!$levels) {
      $form_state->setErrorByName('levels', $this->t('Pick at least one level.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $deck_id = (int) $form_state->getValue('deck');
    $uid = (int) $this->currentUser->id();

    if ($form_state->getValue('mode') === 'passive') {
      $this->submitPassive($deck_id, $uid, $form_state);
      return;
    }

    $levels = array_values(array_filter((array) $form_state->getValue('levels')));
    $per_level = max(1, (int) $form_state->getValue('per_level') ?: 3);

    $passage_ids = $this->queue->buildFromLevels($uid, $deck_id, $levels, $per_level);

    if (!$passage_ids) {
      $this->messenger()->addWarning($this->t('No passages were found for the level(s) you picked in this deck.'));
      $form_state->setRedirectUrl(Url::fromRoute('lms_listening.my_passages', [], ['query' => ['deck' => $deck_id]]));
      return;
    }

    $this->queue->setQueue($uid, $deck_id, $passage_ids);
    $form_state->setRedirectUrl(Url::fromRoute('lms_listening.listen', ['lms_listening_passage' => $passage_ids[0]]));
  }

  /**
   * mode=passive branch of submitForm() — see PASSIVE_LISTENING_PLAN.md.
   * Note this no-JS path can reach passage 1's page, but Passive
   * Listening's whole point (hands-free auto-advance through 3 passages)
   * needs js/listening-passive.js to actually work — without it the
   * student lands on one passage with no way to move to the next except
   * navigating back here and starting over. Documented as an accepted,
   * inherently-degraded fallback, not a bug.
   */
  private function submitPassive(int $deck_id, int $uid, FormStateInterface $form_state): void {
    $specific = array_values(array_filter((array) $form_state->getValue('specific_passages', [])));

    if ($specific) {
      // Chosen exact passages override the level/per-level pick entirely
      // — see ListeningQueueService::buildQueueFromIds().
      $passage_ids = $this->queue->buildQueueFromIds($uid, $deck_id, array_map('intval', $specific));
    }
    else {
      $levels = array_values(array_filter((array) $form_state->getValue('levels')));
      $per_level = max(1, (int) $form_state->getValue('per_level') ?: 1);
      $passage_ids = $this->queue->buildPassiveQueue($uid, $deck_id, $per_level, $levels);
    }

    if (!$passage_ids) {
      $this->messenger()->addWarning($this->t('No listening passages were found for this deck.'));
      $form_state->setRedirectUrl(Url::fromRoute('lms_listening.my_passages', [], ['query' => ['deck' => $deck_id]]));
      return;
    }

    $this->queue->setQueue($uid, $deck_id, $passage_ids, 'passive');
    $form_state->setRedirectUrl(Url::fromRoute('lms_listening.listen_passive', ['lms_listening_passage' => $passage_ids[0]]));
  }

}
