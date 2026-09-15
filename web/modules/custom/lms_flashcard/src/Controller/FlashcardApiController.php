<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\Bytes;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\file\FileInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\lms_flashcard\Entity\Card;
use Drupal\lms_flashcard\Entity\CardStat;
use Drupal\lms_flashcard\Entity\Deck;
use Drupal\lms_flashcard\Service\Sm2SchedulerService;
use Drupal\lms_flashcard\Service\StudyQueueBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * AJAX endpoints backing the flashcard study session UI.
 *
 * Follows lms_note's "own content" convention: routes are gated only by
 * _user_is_logged_in, and every query here is scoped by the resolved uid
 * rather than relying on entity-level access grants (see
 * NoteController::save() et al. for the pattern this mirrors).
 */
final class FlashcardApiController extends ControllerBase {

  private const TEMPSTORE_COLLECTION = 'lms_flashcard_study';

  /**
   * CardStat.field_spell_level bounds — see buildSpellPuzzle() in
   * flashcard-study.js for what each level means difficulty-wise.
   */
  private const SPELL_LEVEL_MIN = 1;
  private const SPELL_LEVEL_MAX = 5;

  /**
   * CardStat.field_quiz_level bounds — see buildQuizExercise() in
   * flashcard-study.js for what each level means difficulty-wise.
   */
  private const QUIZ_LEVEL_MIN = 1;
  private const QUIZ_LEVEL_MAX = 3;

  /**
   * CardStat.field_pronunciation_level bounds — purely informational (see
   * that field's docblock on CardStat), unlike SPELL/QUIZ_LEVEL which also
   * drive exercise difficulty client-side.
   */
  private const PRONUNCIATION_LEVEL_MIN = 1;
  private const PRONUNCIATION_LEVEL_MAX = 5;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
    protected StudyQueueBuilder $queueBuilder,
    protected Sm2SchedulerService $scheduler,
    protected PrivateTempStoreFactory $tempStoreFactory,
    protected TimeInterface $time,
    protected FileRepositoryInterface $fileRepository,
    protected FileSystemInterface $fileSystem,
    protected FileUsageInterface $fileUsage,
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
      $container->get('lms_flashcard.sm2_scheduler'),
      $container->get('tempstore.private'),
      $container->get('datetime.time'),
      $container->get('file.repository'),
      $container->get('file_system'),
      $container->get('file.usage'),
    );
  }

  /**
   * GET /api/lms/flashcard/next?deck=ID — pop the next card off the queue.
   */
  public function nextCard(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $deckId = (int) $request->query->get('deck');
    if (!$deckId) {
      return new JsonResponse(['error' => 'Missing deck'], 400);
    }
    if (!$this->loadVisibleDeck($deckId, $uid)) {
      return new JsonResponse(['error' => 'Deck not found'], 404);
    }

    $store = $this->tempStoreFactory->get(self::TEMPSTORE_COLLECTION);
    $queueKey = "queue:{$uid}:{$deckId}";
    $currentKey = "current:{$uid}:{$deckId}";
    $directionKey = "direction:{$uid}:{$deckId}";
    $practiceKey = "practice:{$uid}:{$deckId}";
    $drillAllKey = "drillall:{$uid}:{$deckId}";
    $styleKey = "style:{$uid}:{$deckId}";

    $direction = $request->query->get('direction', 'forward');
    if (!in_array($direction, ['forward', 'reverse', 'mixed'], TRUE)) {
      $direction = 'forward';
    }
    // $practice controls whether a grade gets persisted to the real SM-2
    // schedule at all (see submitReview()) — "Practice all cards" only.
    // For style=flip specifically it instead controls whether
    // buildMasteryQueue() includes already-mastered cards (see below) —
    // that style never writes a real grade at all, so the "don't touch
    // the schedule" half of $practice's usual meaning is moot for it.
    $practice = $request->query->get('mode') === 'practice';
    $style = in_array($request->query->get('style'), ['spell', 'quiz', 'pronounce'], TRUE) ? $request->query->get('style') : 'flip';
    // $drillAll controls only the *shape* of the queue passed to
    // StudyQueueBuilder::buildQueue() — due/new vs. every card in the deck
    // shuffled. Spelling, Quiz and Pronunciation Practice (style=spell /
    // style=quiz / style=pronounce) also drill every card (so none of them
    // is blocked by "nothing due right now" like Study Now would be) but,
    // unlike $practice, all three keep writing real SM-2 grades in the
    // background — see submitSpellResult() / submitQuizResult() /
    // submitPronunciationResult() in flashcard-study.js, which is why this
    // is tracked as its own flag instead of just reusing $practice here.
    // Unused by style=flip's own queue builder below, but still computed
    // and compared into the cache key so switching "Practice all cards"
    // on/off for it still invalidates the stored queue correctly.
    $drillAll = $practice || in_array($style, ['spell', 'quiz', 'pronounce'], TRUE);

    $queue = $store->get($queueKey);
    // The front-end always sends direction/mode/style (see
    // flashcard-study.js), even on calls after the first — normally
    // redundant since the session already has its own stored values, but it
    // also means a student clicking a *different* "Or study" link (e.g.
    // "Spelling Practice" while a "Study Now" session for the same deck is
    // still unfinished) is deliberately asking to switch modes. Detect that
    // and discard the stale queue so it rebuilds under the newly requested
    // direction/mode/style instead of silently continuing the old,
    // unfinished one.
    if ($queue !== NULL && (
      $store->get($directionKey) !== $direction
      || (bool) $store->get($practiceKey) !== $practice
      || (bool) $store->get($drillAllKey) !== $drillAll
      // Without this, a queue already stored under, say, style=flip stays
      // valid by this comparison alone for *any* other style whose
      // direction/practice/drillAll all happen to match too (style=quiz
      // switching to style=flip usually differs on direction so this
      // rarely bit before, but flip's own queue shape changed under
      // buildMasteryQueue() above without those 3 other flags moving at
      // all, so a session mid-flight right as that shipped would have
      // otherwise kept being served the stale pre-mastery-queue queue
      // until it ran out).
      || $store->get($styleKey) !== $style
    )) {
      $queue = NULL;
    }
    if ($queue === NULL) {
      // style=flip gets its own queue shape — see buildMasteryQueue()'s
      // docblock for why buildQueue()'s due/new gating doesn't apply to
      // it any more.
      $queue = $style === 'flip'
        ? $this->queueBuilder->buildMasteryQueue($uid, $deckId, $practice)
        : $this->queueBuilder->buildQueue($uid, [$deckId], $drillAll);
      $store->set($directionKey, $direction);
      $store->set($practiceKey, $practice);
      $store->set($drillAllKey, $drillAll);
      $store->set($styleKey, $style);
    }
    $sessionDirection = $store->get($directionKey) ?? 'forward';
    $sessionPractice = (bool) $store->get($practiceKey);

    $cardStorage = $this->entityTypeManager->getStorage('lms_flashcard_card');
    $card = NULL;
    $item = NULL;
    // Skip over any queued cards deleted, or unpublished (see Card::status),
    // since the queue was built.
    while ($queue && (!$card || !$card->get('status')->value)) {
      $item = array_shift($queue);
      $card = $cardStorage->load($item['card_id']);
    }

    if (!$card) {
      // Delete rather than store an empty array: leaving `[]` behind would
      // make the `$queue === NULL` check above permanently skip rebuilding,
      // so a student returning to /study after finishing a session (e.g.
      // via "Keep Studying" on the summary page) would get bounced straight
      // back to "empty" forever even once more cards become due — instead
      // of a fresh queue picking them up.
      $store->delete($queueKey);
      $store->delete($currentKey);
      $store->delete($directionKey);
      $store->delete($practiceKey);
      $store->delete($drillAllKey);
      $store->delete($styleKey);
      return new JsonResponse(['status' => 'empty']);
    }

    $store->set($queueKey, $queue);
    $store->set($currentKey, $item);

    $seenInfo = $this->recordCardSeen((int) $card->id(), $uid);
    // Only meaningful for style=flip (see submitMastery()'s docblock);
    // absent/1 for every other style's queue entries, which never set it.
    $masteryLevel = (int) ($item['level'] ?? 1);

    $audio = $card->get('field_audio')->entity;
    $image = $card->get('field_image')->entity;
    $cardId = (int) $card->id();
    $direction = $sessionDirection === 'mixed' ? (random_int(0, 1) ? 'reverse' : 'forward') : $sessionDirection;
    $sentences = array_column($card->get('field_example_sentences')->getValue(), 'value');
    $translations = array_column($card->get('field_example_translations')->getValue(), 'value');

    // Preserve cards created before multi-example support until they are
    // edited or migrated by the database update hook.
    if (!$sentences) {
      $legacySentence = trim(strip_tags((string) $card->get('field_example_sentence')->value));
      $legacyTranslation = trim(strip_tags((string) $card->get('field_example_translation')->value));
      if ($legacySentence !== '' || $legacyTranslation !== '') {
        $sentences = [$legacySentence];
        $translations = [$legacyTranslation];
      }
    }

    $examples = [];
    foreach ($sentences as $delta => $sentence) {
      if (trim((string) $sentence) !== '') {
        $examples[] = [
          'sentence' => (string) $sentence,
          'translation' => (string) ($translations[$delta] ?? ''),
        ];
      }
    }

    $collocationPhrases = array_column($card->get('field_collocations')->getValue(), 'value');
    $collocationTranslations = array_column($card->get('field_collocation_translations')->getValue(), 'value');
    $collocations = [];
    foreach ($collocationPhrases as $delta => $phrase) {
      if (trim((string) $phrase) !== '') {
        $collocations[] = [
          'phrase' => (string) $phrase,
          'translation' => (string) ($collocationTranslations[$delta] ?? ''),
        ];
      }
    }

    return new JsonResponse([
      'status' => 'ok',
      'source' => $item['source'],
      'remaining' => count($queue),
      'practice' => $sessionPractice,
      'card' => [
        'id' => $cardId,
        'direction' => $direction,
        'term' => $card->label(),
        'definition' => $card->get('field_definition')->value,
        'translation' => $card->get('field_translation')->value,
        'part_of_speech' => $card->get('field_part_of_speech')->value,
        'pronunciation' => $card->get('field_pronunciation')->value,
        'difficulty' => $card->get('field_difficulty')->value,
        'examples' => $examples,
        // Retain singular keys for older clients of this endpoint.
        'example_sentence' => $examples[0]['sentence'] ?? '',
        'example_translation' => $examples[0]['translation'] ?? '',
        'collocations' => $collocations,
        'audio_url' => $audio ? \Drupal::service('file_url_generator')->generateString($audio->getFileUri()) : NULL,
        'image_url' => $image ? \Drupal::service('file_url_generator')->generateString($image->getFileUri()) : NULL,
        'times_seen' => $seenInfo['times_seen'],
        'spell_level' => $seenInfo['spell_level'],
        'quiz_level' => $seenInfo['quiz_level'],
        'pronunciation_level' => $seenInfo['pronunciation_level'],
        'mastery_level' => $masteryLevel,
        // In practice mode grading is a no-op (see submitReview()), so the
        // day-interval previews would be misleading — suppress them rather
        // than showing what *would* happen if this were a real review.
        'previews' => $sessionPractice ? [] : $this->buildGradePreviews($cardId, $uid),
        // Quick Edit (see QUICK_EDIT_STUDY_CARD_PLAN.md): lets the front end
        // show/hide the "Quick edit" button per card without a round trip —
        // owner-or-staff, same rule as quickEditFormData()/quickEditSave()
        // below (canQuickEditCard() is the single source of truth for both).
        'can_quick_edit' => $this->canQuickEditCard($card),
        'quick_edit_get_url' => '/api/lms/flashcard/card/' . $cardId . '/quick-edit',
        'quick_edit_save_url' => '/api/lms/flashcard/card/' . $cardId . '/quick-edit',
      ],
    ]);
  }

  /**
   * Whether the current user may Quick Edit this card while studying — the
   * deck's owner, or staff with "edit lms_course" (updated per review: an
   * admin may quick-edit official/shared decks too, not just their own).
   * This is exactly what Card's own access handler already computes for the
   * 'update' operation (FlashcardAccessControlHandler::checkAccess()), so it
   * simply delegates rather than re-implementing the owner check by hand.
   */
  private function canQuickEditCard(Card $card): bool {
    return (bool) $card->access('update', $this->currentUser);
  }

  /**
   * Builds the {id, deck_id, term, definition, ...} shape both Quick Edit
   * endpoints return — the raw editable field values (unlike nextCard()'s
   * 'card', this has no session/SRS state), ready for the study page's
   * edit-drawer form to populate itself from and to re-render the on-screen
   * card from after a save.
   */
  private function buildQuickEditPayload(Card $card): array {
    $sentences = array_column($card->get('field_example_sentences')->getValue(), 'value');
    $translations = array_column($card->get('field_example_translations')->getValue(), 'value');
    $examples = [];
    foreach ($sentences as $delta => $sentence) {
      $examples[] = [
        'sentence' => (string) $sentence,
        'translation' => (string) ($translations[$delta] ?? ''),
      ];
    }

    $collocationPhrases = array_column($card->get('field_collocations')->getValue(), 'value');
    $collocationTranslations = array_column($card->get('field_collocation_translations')->getValue(), 'value');
    $collocations = [];
    foreach ($collocationPhrases as $delta => $phrase) {
      $collocations[] = [
        'phrase' => (string) $phrase,
        'translation' => (string) ($collocationTranslations[$delta] ?? ''),
      ];
    }

    $audio = $card->get('field_audio')->entity;
    $image = $card->get('field_image')->entity;

    return [
      'id' => (int) $card->id(),
      'deck_id' => (int) $card->get('field_deck')->target_id,
      'term' => (string) $card->label(),
      'definition' => (string) $card->get('field_definition')->value,
      'translation' => (string) $card->get('field_translation')->value,
      'part_of_speech' => (string) $card->get('field_part_of_speech')->value,
      'pronunciation' => (string) $card->get('field_pronunciation')->value,
      'difficulty' => (string) ($card->get('field_difficulty')->value ?: 'medium'),
      'examples' => $examples,
      'collocations' => $collocations,
      'audio_url' => $audio ? \Drupal::service('file_url_generator')->generateString($audio->getFileUri()) : NULL,
      'audio_filename' => $audio ? $audio->getFilename() : NULL,
      'image_url' => $image ? \Drupal::service('file_url_generator')->generateString($image->getFileUri()) : NULL,
    ];
  }

  /**
   * GET /api/lms/flashcard/card/{card}/quick-edit — fetch fresh field
   * values for the edit drawer right before opening it (avoids populating
   * the form from a possibly-stale copy already sitting in the study page's
   * JS state).
   */
  public function quickEditFormData(Card $lms_flashcard_card): JsonResponse {
    if (!$this->currentUser->id()) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }
    if (!$this->canQuickEditCard($lms_flashcard_card)) {
      return new JsonResponse(['error' => 'Forbidden'], 403);
    }

    return new JsonResponse([
      'status' => 'ok',
      'card' => $this->buildQuickEditPayload($lms_flashcard_card),
    ]);
  }

  /**
   * Validates/normalizes one `examples_json`/`collocations_json` payload
   * (an array of {$textKey: string, translation: string} rows) for
   * quickEditSave(). A row where both sides are blank is silently dropped
   * (an empty repeater row the drawer's JS adds by default); a row with
   * only one side filled in is rejected, per the plan's "both sides or
   * neither" rule.
   *
   * @return array|\Symfony\Component\HttpFoundation\JsonResponse
   *   The normalized [['text' => ..., 'translation' => ...], ...] list, or
   *   a 400 JsonResponse ready to return as-is if validation failed.
   */
  private function normalizePairsJson(string $json, string $textKey, string $label): array|JsonResponse {
    if (trim($json) === '') {
      return [];
    }
    $decoded = json_decode($json, TRUE);
    if (!is_array($decoded)) {
      return new JsonResponse(['error' => "Invalid {$label} payload"], 400);
    }

    $pairs = [];
    foreach ($decoded as $row) {
      if (!is_array($row)) {
        continue;
      }
      $text = trim((string) ($row[$textKey] ?? ''));
      $translation = trim((string) ($row['translation'] ?? ''));
      if ($text === '' && $translation === '') {
        continue;
      }
      if ($text === '' || $translation === '') {
        return new JsonResponse(['error' => "Each {$label} row needs both text and a translation"], 400);
      }
      $pairs[] = ['text' => $text, 'translation' => $translation];
    }
    return $pairs;
  }

  /**
   * Deletes a file entity only if nothing references it any more —
   * Drupal's own FileFieldItemList::postSave() (run by $card->save() in
   * quickEditSave() just before this is called) already decremented usage
   * for a replaced/removed field_audio or field_image file, but never
   * deletes the file itself once usage hits zero. Checking listUsage()
   * first, rather than deleting unconditionally, guards against the
   * unlikely case of the same file entity being referenced elsewhere.
   */
  private function deleteFileIfUnused(FileInterface $file): void {
    if (empty($this->fileUsage->listUsage($file))) {
      $file->delete();
    }
  }

  /**
   * POST /api/lms/flashcard/card/{card}/quick-edit — save edits made from
   * the study page's Quick Edit drawer. multipart/form-data; see
   * QUICK_EDIT_STUDY_CARD_PLAN.md's "Save Payload" section for the field
   * list. Returns the same shape as quickEditFormData() so the front end
   * can patch its currentCard and re-render immediately.
   */
  public function quickEditSave(Card $lms_flashcard_card, Request $request): JsonResponse {
    if (!$this->currentUser->id()) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }
    $card = $lms_flashcard_card;
    if (!$this->canQuickEditCard($card)) {
      return new JsonResponse(['error' => 'Forbidden'], 403);
    }

    // Soft sanity check, not a security boundary (canQuickEditCard() above
    // already fully authorizes this card regardless of deck) — just guards
    // against the front end accidentally saving into the wrong card if the
    // study session's state and the drawer's captured card ever disagree.
    $deckIdParam = $request->request->get('deck_id');
    if ($deckIdParam !== NULL && $deckIdParam !== '' && (int) $deckIdParam !== (int) $card->get('field_deck')->target_id) {
      return new JsonResponse(['error' => 'deck_id does not match this card'], 400);
    }

    $term = trim((string) $request->request->get('term', ''));
    $definition = trim((string) $request->request->get('definition', ''));
    if ($term === '' || $definition === '') {
      return new JsonResponse(['error' => 'Term and definition are required'], 400);
    }
    if (mb_strlen($term) > 255) {
      return new JsonResponse(['error' => 'Term must be 255 characters or fewer'], 400);
    }

    $pronunciation = trim((string) $request->request->get('pronunciation', ''));
    if (mb_strlen($pronunciation) > 128) {
      return new JsonResponse(['error' => 'Pronunciation must be 128 characters or fewer'], 400);
    }

    $translation = trim((string) $request->request->get('translation', ''));
    if (mb_strlen($translation) > 255) {
      return new JsonResponse(['error' => 'Translation must be 255 characters or fewer'], 400);
    }

    // Allowed values read off the field definitions rather than hardcoded
    // here, so this never drifts from Card::baseFieldDefinitions().
    $posAllowed = array_keys($card->get('field_part_of_speech')->getFieldDefinition()->getSetting('allowed_values'));
    $partOfSpeech = (string) $request->request->get('part_of_speech', '');
    if ($partOfSpeech !== '' && !in_array($partOfSpeech, $posAllowed, TRUE)) {
      return new JsonResponse(['error' => 'Invalid part of speech'], 400);
    }

    $difficultyAllowed = array_keys($card->get('field_difficulty')->getFieldDefinition()->getSetting('allowed_values'));
    $difficulty = (string) $request->request->get('difficulty', 'medium');
    if (!in_array($difficulty, $difficultyAllowed, TRUE)) {
      return new JsonResponse(['error' => 'Invalid difficulty'], 400);
    }

    $examplePairs = $this->normalizePairsJson((string) $request->request->get('examples_json', ''), 'sentence', 'example');
    if ($examplePairs instanceof JsonResponse) {
      return $examplePairs;
    }
    $collocationPairs = $this->normalizePairsJson((string) $request->request->get('collocations_json', ''), 'phrase', 'collocation');
    if ($collocationPairs instanceof JsonResponse) {
      return $collocationPairs;
    }

    $removeAudio = filter_var($request->request->get('remove_audio', 'false'), FILTER_VALIDATE_BOOLEAN);
    $audioUpload = $request->files->get('audio');
    if ($audioUpload && !$audioUpload->isValid()) {
      return new JsonResponse(['error' => 'Audio upload failed, please try again'], 400);
    }
    $oldAudio = $card->get('field_audio')->entity;
    $newAudioFile = NULL;
    if ($audioUpload) {
      $audioFieldDef = $card->get('field_audio')->getFieldDefinition();
      $allowedExtensions = preg_split('/\s+/', trim((string) $audioFieldDef->getSetting('file_extensions'))) ?: [];
      $extension = strtolower($audioUpload->getClientOriginalExtension());
      if (!in_array($extension, $allowedExtensions, TRUE)) {
        return new JsonResponse(['error' => 'Audio file type not allowed. Allowed: ' . implode(', ', $allowedExtensions)], 400);
      }
      $maxSize = Bytes::toNumber((string) $audioFieldDef->getSetting('max_filesize'));
      if ($maxSize && $audioUpload->getSize() > $maxSize) {
        return new JsonResponse(['error' => 'Audio file exceeds the size limit'], 400);
      }

      $directory = 'public://flashcards/audio/' . date('Y-m');
      $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      try {
        $newAudioFile = $this->fileRepository->writeData(
          file_get_contents($audioUpload->getPathname()),
          $directory . '/' . $audioUpload->getClientOriginalName(),
          FileSystemInterface::EXISTS_RENAME
        );
      }
      catch (\Exception $e) {
        return new JsonResponse(['error' => 'Failed to save audio file'], 500);
      }
      $newAudioFile->setPermanent();
      $newAudioFile->save();
    }

    // field_image has no explicit file_extensions setting on Card (see
    // Card::baseFieldDefinitions()), so it falls back to the image field
    // type's own core default — listed explicitly here since that default
    // isn't exposed as a field setting to read back the way field_audio's
    // is above.
    $removeImage = filter_var($request->request->get('remove_image', 'false'), FILTER_VALIDATE_BOOLEAN);
    $imageUpload = $request->files->get('image');
    if ($imageUpload && !$imageUpload->isValid()) {
      return new JsonResponse(['error' => 'Image upload failed, please try again'], 400);
    }
    $oldImage = $card->get('field_image')->entity;
    $newImageFile = NULL;
    if ($imageUpload) {
      $allowedImageExtensions = ['png', 'gif', 'jpg', 'jpeg', 'webp'];
      $extension = strtolower($imageUpload->getClientOriginalExtension());
      if (!in_array($extension, $allowedImageExtensions, TRUE)) {
        return new JsonResponse(['error' => 'Image file type not allowed. Allowed: ' . implode(', ', $allowedImageExtensions)], 400);
      }
      $maxImageSize = 5 * 1024 * 1024;
      if ($imageUpload->getSize() > $maxImageSize) {
        return new JsonResponse(['error' => 'Image file exceeds the 5 MB limit'], 400);
      }

      $directory = 'public://flashcards/images';
      $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      try {
        $newImageFile = $this->fileRepository->writeData(
          file_get_contents($imageUpload->getPathname()),
          $directory . '/' . $imageUpload->getClientOriginalName(),
          FileSystemInterface::EXISTS_RENAME
        );
      }
      catch (\Exception $e) {
        return new JsonResponse(['error' => 'Failed to save image file'], 500);
      }
      $newImageFile->setPermanent();
      $newImageFile->save();
    }

    $card->set('name', $term);
    $existingFormat = $card->get('field_definition')->format ?: 'basic_html';
    $card->set('field_definition', ['value' => $definition, 'format' => $existingFormat]);
    $card->set('field_translation', $translation);
    $card->set('field_part_of_speech', $partOfSpeech !== '' ? $partOfSpeech : NULL);
    $card->set('field_pronunciation', $pronunciation);
    $card->set('field_difficulty', $difficulty);
    $card->set('field_example_sentences', array_column($examplePairs, 'text'));
    $card->set('field_example_translations', array_column($examplePairs, 'translation'));
    // Quick Edit fully migrates a card off the legacy single-example fields
    // the moment it's saved once — clearing them here (rather than leaving
    // whatever they held before) stops nextCard()'s legacy fallback from
    // resurrecting stale pre-migration text if the student empties the
    // example list entirely via this form. See nextCard()'s own comment on
    // that fallback.
    $card->set('field_example_sentence', '');
    $card->set('field_example_translation', '');
    $card->set('field_collocations', array_column($collocationPairs, 'text'));
    $card->set('field_collocation_translations', array_column($collocationPairs, 'translation'));

    if ($newAudioFile) {
      $card->set('field_audio', ['target_id' => $newAudioFile->id()]);
    }
    elseif ($removeAudio) {
      $card->set('field_audio', NULL);
    }

    if ($newImageFile) {
      $card->set('field_image', ['target_id' => $newImageFile->id(), 'alt' => $term]);
    }
    elseif ($removeImage) {
      $card->set('field_image', NULL);
    }

    $card->save();

    // Now that $card->save() above has run Drupal's own file-usage
    // bookkeeping for field_audio/field_image (FileFieldItemList::
    // postSave()), clean up whichever old file it just orphaned — see
    // deleteFileIfUnused()'s docblock.
    if ($oldAudio && ($newAudioFile || $removeAudio) && (int) $oldAudio->id() !== (int) ($newAudioFile?->id() ?? 0)) {
      $this->deleteFileIfUnused($oldAudio);
    }
    if ($oldImage && ($newImageFile || $removeImage) && (int) $oldImage->id() !== (int) ($newImageFile?->id() ?? 0)) {
      $this->deleteFileIfUnused($oldImage);
    }

    return new JsonResponse([
      'status' => 'ok',
      'card' => $this->buildQuickEditPayload($card),
    ]);
  }

  /**
   * Loads a user's CardStat row for a card, creating an unsaved one if none
   * exists yet — shared by recordCardSeen() and submitReview()'s spelling-
   * level update so both work off the same lazy-create pattern as Review.
   */
  private function loadOrCreateCardStat(int $cardId, int $uid): CardStat {
    $statStorage = $this->entityTypeManager->getStorage('lms_flashcard_card_stat');
    $statIds = $statStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardId)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();

    $stat = $statIds ? $statStorage->load(reset($statIds)) : NULL;
    return $stat ?: $statStorage->create(['uid' => $uid, 'field_card' => $cardId]);
  }

  /**
   * Records that a card was shown to a user; returns ['times_seen' => int,
   * 'spell_level' => int, 'quiz_level' => int, 'pronunciation_level' => int]
   * for the JSON payload.
   *
   * Deliberately writes to the separate CardStat entity rather than Review
   * — Review's mere existence marks a card as "not new" for
   * StudyQueueBuilder (see its class docblock), so touching it here would
   * corrupt real SRS scheduling on every practice-mode view. CardStat is
   * counted in both practice and SRS sessions since it's purely a "how many
   * times has the student seen this word" display stat, never consulted for
   * scheduling.
   */
  private function recordCardSeen(int $cardId, int $uid): array {
    $stat = $this->loadOrCreateCardStat($cardId, $uid);

    $timesSeen = ((int) ($stat->get('field_times_seen')->value ?? 0)) + 1;
    $stat->set('field_times_seen', $timesSeen);
    $stat->set('field_last_seen_at', $this->time->getRequestTime());
    $stat->save();

    return [
      'times_seen' => $timesSeen,
      'spell_level' => (int) ($stat->get('field_spell_level')->value ?: self::SPELL_LEVEL_MIN),
      'quiz_level' => (int) ($stat->get('field_quiz_level')->value ?: self::QUIZ_LEVEL_MIN),
      'pronunciation_level' => (int) ($stat->get('field_pronunciation_level')->value ?: self::PRONUNCIATION_LEVEL_MIN),
    ];
  }

  /**
   * Computes what each of the 4 grade buttons would set the next-review
   * interval to, for the card currently being shown — a dry run through
   * Sm2SchedulerService::grade() for every quality, never saved. "Again"
   * is shown as a fixed "<10m" label rather than a real day-interval,
   * since a quality-0 grade resets interval to 0 and gets the card
   * requeued within the same session (see requeueAfterAgain()) instead of
   * literally being scheduled for a fractional day.
   */
  private function buildGradePreviews(int $cardId, int $uid): array {
    $reviewStorage = $this->entityTypeManager->getStorage('lms_flashcard_review');
    $reviewIds = $reviewStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardId)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();
    $review = $reviewIds ? $reviewStorage->load(reset($reviewIds)) : NULL;
    $currentState = $review ? [
      'ease_factor' => (float) $review->get('field_ease_factor')->value,
      'interval_days' => (int) $review->get('field_interval_days')->value,
      'repetitions' => (int) $review->get('field_repetitions')->value,
      'status' => $review->get('field_status')->value,
    ] : ['ease_factor' => 2.5, 'interval_days' => 0, 'repetitions' => 0, 'status' => 'new'];

    $now = $this->time->getRequestTime();
    $labels = ['again', 'hard', 'good', 'easy'];
    $previews = [];
    foreach ($labels as $quality => $label) {
      $previews[$label] = $quality === 0 ? '<10m' : $this->formatIntervalPreview($this->scheduler->grade($currentState, $quality, $now)['interval_days']);
    }
    return $previews;
  }

  /**
   * Formats a day count for the grade-button preview label.
   */
  private function formatIntervalPreview(int $days): string {
    if ($days < 1) {
      return '<1d';
    }
    if ($days % 30 === 0 && $days >= 30) {
      return ($days / 30) . 'mo';
    }
    if ($days % 7 === 0 && $days >= 14) {
      return ($days / 7) . 'w';
    }
    return $days . 'd';
  }

  /**
   * POST /api/lms/flashcard/review — grade the card currently being shown.
   *
   * Body: {"deck_id": int, "quality": 0-3} (0=Again, 1=Hard, 2=Good, 3=Easy).
   * Also accepts an optional "spell_correct": bool, sent only from Spelling
   * Practice (see checkSpelling() in flashcard-study.js, which auto-derives
   * `quality` from it too since that style hides the 4 grade buttons) —
   * bumps CardStat.field_spell_level up or down a notch, independent of the
   * `quality`-driven SM-2 update above. See FlashcardApiController::
   * loadOrCreateCardStat() and the SPELL_LEVEL_MIN/MAX constants.
   *
   * Also accepts an optional "quiz_correct": bool, sent only from Quiz
   * Practice (submitQuizResult() in flashcard-study.js, same idea as
   * spell_correct) — bumps CardStat.field_quiz_level instead, via the
   * QUIZ_LEVEL_MIN/MAX constants.
   *
   * Also accepts an optional "pronunciation_correct": bool, sent only from
   * Pronunciation Practice (submitPronunciationResult() in
   * flashcard-study.js, same idea as spell_correct/quiz_correct) — bumps
   * CardStat.field_pronunciation_level instead, via the
   * PRONUNCIATION_LEVEL_MIN/MAX constants. Whether the attempt counts as
   * correct is decided client-side, either by the Web Speech API transcript
   * match or the manual fallback buttons — see buildPronunciationExercise().
   */
  public function submitReview(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $data = json_decode($request->getContent(), TRUE) ?? [];
    $deckId = (int) ($data['deck_id'] ?? 0);
    $quality = $data['quality'] ?? NULL;
    if (!$deckId || $quality === NULL || !is_numeric($quality)) {
      return new JsonResponse(['error' => 'Missing or invalid deck_id/quality'], 400);
    }
    $quality = (int) $quality;
    if ($quality < 0 || $quality > 3) {
      return new JsonResponse(['error' => 'quality must be 0-3'], 400);
    }
    if (!$this->loadVisibleDeck($deckId, $uid)) {
      return new JsonResponse(['error' => 'Deck not found'], 404);
    }

    $store = $this->tempStoreFactory->get(self::TEMPSTORE_COLLECTION);
    $currentKey = "current:{$uid}:{$deckId}";
    $practiceKey = "practice:{$uid}:{$deckId}";
    $item = $store->get($currentKey);
    $cardId = $item['card_id'] ?? (int) ($data['card_id'] ?? 0);
    if (!$cardId) {
      return new JsonResponse(['error' => 'No card currently in session for this deck'], 400);
    }
    // A "Practice all cards" session (see StudyQueueBuilder::buildQueue())
    // grades for feedback only — the SM-2 schedule underneath is never
    // touched, so cramming a deck doesn't disturb the student's real due
    // dates.
    $practice = (bool) $store->get($practiceKey);

    $reviewStorage = $this->entityTypeManager->getStorage('lms_flashcard_review');
    $reviewIds = $reviewStorage->getQuery()
      ->condition('uid', $uid)
      ->condition('field_card', $cardId)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();

    $review = $reviewIds ? $reviewStorage->load(reset($reviewIds)) : NULL;
    $currentState = $review ? [
      'ease_factor' => (float) $review->get('field_ease_factor')->value,
      'interval_days' => (int) $review->get('field_interval_days')->value,
      'repetitions' => (int) $review->get('field_repetitions')->value,
      'status' => $review->get('field_status')->value,
    ] : ['ease_factor' => 2.5, 'interval_days' => 0, 'repetitions' => 0, 'status' => 'new'];

    $now = $this->time->getRequestTime();
    $result = $this->scheduler->grade($currentState, $quality, $now);

    if (!$practice) {
      if (!$review) {
        $review = $reviewStorage->create(['uid' => $uid, 'field_card' => $cardId]);
      }
      $review->set('field_ease_factor', $result['ease_factor']);
      $review->set('field_interval_days', $result['interval_days']);
      $review->set('field_repetitions', $result['repetitions']);
      $review->set('field_status', $result['status']);
      $review->set('field_next_review_at', $result['next_review_at']);
      $review->set('field_last_reviewed_at', $result['last_reviewed_at']);
      if ($result['lapses_delta']) {
        $review->set('field_lapses', ((int) ($review->get('field_lapses')->value ?? 0)) + $result['lapses_delta']);
      }
      $review->save();
    }

    // Spelling Practice (style=spell): the 4 grade buttons are hidden there
    // in favor of auto-grading the typed answer as $quality above (see
    // checkSpelling() in flashcard-study.js, which is what sends this
    // request at all in that mode) — spell_correct additionally drives a
    // second, independent difficulty-level counter on CardStat. Absent for
    // a manual button click in the plain flip/reverse styles.
    $spellLevel = NULL;
    if (array_key_exists('spell_correct', $data)) {
      $stat = $this->loadOrCreateCardStat($cardId, $uid);
      $spellLevel = (int) ($stat->get('field_spell_level')->value ?: self::SPELL_LEVEL_MIN);
      $spellLevel = !empty($data['spell_correct'])
        ? min(self::SPELL_LEVEL_MAX, $spellLevel + 1)
        : max(self::SPELL_LEVEL_MIN, $spellLevel - 1);
      $stat->set('field_spell_level', $spellLevel);
      $stat->save();
    }

    // Quiz Practice (style=quiz): same idea as spell_correct above but for
    // its own independent level — see FlashcardApiController::
    // buildQuizPool() and buildQuizExercise() in flashcard-study.js.
    $quizLevel = NULL;
    if (array_key_exists('quiz_correct', $data)) {
      $stat = $this->loadOrCreateCardStat($cardId, $uid);
      $quizLevel = (int) ($stat->get('field_quiz_level')->value ?: self::QUIZ_LEVEL_MIN);
      $quizLevel = !empty($data['quiz_correct'])
        ? min(self::QUIZ_LEVEL_MAX, $quizLevel + 1)
        : max(self::QUIZ_LEVEL_MIN, $quizLevel - 1);
      $stat->set('field_quiz_level', $quizLevel);
      $stat->save();
    }

    // Pronunciation Practice (style=pronounce): same idea as spell_correct/
    // quiz_correct above but for its own independent level — see
    // buildPronunciationExercise() in flashcard-study.js.
    $pronunciationLevel = NULL;
    if (array_key_exists('pronunciation_correct', $data)) {
      $stat = $this->loadOrCreateCardStat($cardId, $uid);
      $pronunciationLevel = (int) ($stat->get('field_pronunciation_level')->value ?: self::PRONUNCIATION_LEVEL_MIN);
      $pronunciationLevel = !empty($data['pronunciation_correct'])
        ? min(self::PRONUNCIATION_LEVEL_MAX, $pronunciationLevel + 1)
        : max(self::PRONUNCIATION_LEVEL_MIN, $pronunciationLevel - 1);
      $stat->set('field_pronunciation_level', $pronunciationLevel);
      $stat->save();
    }

    // "Again": the card isn't done for this session — bring it back a few
    // cards ahead instead of letting it disappear until tomorrow.
    if ($quality === 0 && $item) {
      $queueKey = "queue:{$uid}:{$deckId}";
      $queue = $store->get($queueKey) ?? [];
      $queue = $this->queueBuilder->requeueAfterAgain($queue, $item);
      $store->set($queueKey, $queue);
    }

    return new JsonResponse([
      'status' => 'ok',
      'review' => [
        'ease_factor' => $result['ease_factor'],
        'interval_days' => $result['interval_days'],
        'next_review_at' => $result['next_review_at'],
        'status' => $result['status'],
      ],
      'spell_level' => $spellLevel,
      'quiz_level' => $quizLevel,
      'pronunciation_level' => $pronunciationLevel,
    ]);
  }

  /**
   * POST /api/lms/flashcard/mastery — advances the three-level "Study Now"
   * flow for the card currently being shown (style=flip): flip to see the
   * definition (level 1), a later recall check — its definition shown,
   * pick the right term among 4 (level 2) — then, once that's passed, a
   * final pass through Quiz Practice's own 3 exercises reused as-is
   * (level 3, folded in at the student's own request rather than kept as
   * a separate "Quiz" entry point — see js/flashcard-study.js's quiz
   * block in showCard() and finishQuizAnswer()). See buildMasteryExercise()/
   * handleMasteryContinue()/finishMasteryLevel2()/finishMasteryLevel3() in
   * that file for the front-end side of all three levels.
   *
   * Deliberately a separate endpoint from submitReview() rather than a new
   * `quality` value or an extra flag on that one: by the student's own
   * choice (see StudyController::study()'s docblock) this flow never
   * touches the real SM-2 schedule (Sm2SchedulerService, the Review
   * entity) at all, in either direction, at any level — routing it through
   * submitReview() would risk that leaking in later. Only requeues within
   * the session's own queue (via StudyQueueBuilder::requeueForMastery()),
   * exactly like Again's requeueAfterAgain() but tagged with which level
   * to come back at. Level 3's `quiz_correct` is the one exception: it
   * still bumps CardStat.field_quiz_level up/down a notch, same as a real
   * Quiz Practice session's own quiz_correct would (see submitReview()'s
   * handling of it) — that's just this card's per-user exercise-difficulty
   * counter, not the real schedule, so keeping it in sync either way this
   * exercise is reached is the point, not something to avoid here.
   *
   * Body: {"deck_id": int, "action": "level1_done"|"level2_pass"|
   * "level2_fail"|"level3_pass"|"level3_fail", "quiz_correct"?: bool}.
   * - level1_done: the flip was just viewed — queue it again a few cards
   *   ahead, now at level 2.
   * - level2_pass: the recall check was answered correctly — queue it
   *   again a few cards ahead, now at level 3.
   * - level2_fail: answered incorrectly — queue it again a few cards
   *   ahead, back at level 1, to be recall-checked again only after that.
   * - level3_pass: the highest level was cleared — nothing to requeue,
   *   the card was already popped off the queue in nextCard() and simply
   *   isn't put back, so it won't appear again this session. Also the one
   *   action that sets CardStat.field_mastered, unlike the requeuing above
   *   a persistent flag that outlives the session — see that field's own
   *   docblock and StudyQueueBuilder::getMasteryPercent().
   * - level3_fail: answered incorrectly — queue it again a few cards
   *   ahead, all the way back at level 1.
   * `quiz_correct` (only meaningful, and expected, alongside level3_pass/
   * level3_fail) mirrors submitReview()'s own handling of it.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   `{status: 'ok', quiz_level: int|null}` — quiz_level is the updated
   *   CardStat.field_quiz_level when `quiz_correct` was sent, else NULL
   *   (same shape/purpose as submitReview()'s own quiz_level, so
   *   finishMasteryLevel3() can update the on-screen badge the same way
   *   submitQuizResult() does).
   */
  public function submitMastery(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $data = json_decode($request->getContent(), TRUE) ?? [];
    $deckId = (int) ($data['deck_id'] ?? 0);
    $action = $data['action'] ?? NULL;
    $validActions = ['level1_done', 'level2_pass', 'level2_fail', 'level3_pass', 'level3_fail'];
    if (!$deckId || !in_array($action, $validActions, TRUE)) {
      return new JsonResponse(['error' => 'Missing or invalid deck_id/action'], 400);
    }
    if (!$this->loadVisibleDeck($deckId, $uid)) {
      return new JsonResponse(['error' => 'Deck not found'], 404);
    }

    $nextLevel = match ($action) {
      'level1_done' => 2,
      'level2_pass' => 3,
      'level2_fail', 'level3_fail' => 1,
      'level3_pass' => NULL,
    };

    if ($nextLevel !== NULL) {
      $store = $this->tempStoreFactory->get(self::TEMPSTORE_COLLECTION);
      $currentKey = "current:{$uid}:{$deckId}";
      $queueKey = "queue:{$uid}:{$deckId}";
      $item = $store->get($currentKey);
      if ($item) {
        $queue = $store->get($queueKey) ?? [];
        $queue = $this->queueBuilder->requeueForMastery($queue, $item, $nextLevel);
        $store->set($queueKey, $queue);
      }
    }

    $quizLevel = NULL;
    if ($action === 'level3_pass' || array_key_exists('quiz_correct', $data)) {
      $item ??= $this->tempStoreFactory->get(self::TEMPSTORE_COLLECTION)->get("current:{$uid}:{$deckId}");
      $cardId = $item['card_id'] ?? (int) ($data['card_id'] ?? 0);
      if ($cardId) {
        $stat = $this->loadOrCreateCardStat((int) $cardId, $uid);

        if (array_key_exists('quiz_correct', $data)) {
          $quizLevel = (int) ($stat->get('field_quiz_level')->value ?: self::QUIZ_LEVEL_MIN);
          $quizLevel = !empty($data['quiz_correct'])
            ? min(self::QUIZ_LEVEL_MAX, $quizLevel + 1)
            : max(self::QUIZ_LEVEL_MIN, $quizLevel - 1);
          $stat->set('field_quiz_level', $quizLevel);
        }

        // Persists past this session, unlike the queue-level requeuing
        // above — see field_mastered's own docblock on CardStat for why:
        // this is what StudyQueueBuilder::getMasteryPercent() counts to
        // show a lasting "% of this deck mastered" on the deck card.
        // level3_fail only affects this session's queue, never this flag —
        // but "Reset Mastery" (StudyQueueBuilder::resetMastery()) does
        // clear it, by student request, so also set field_ever_mastered
        // alongside it: that flag is what actually gates Spelling/
        // Pronunciation/Reading/Listening (StudyQueueBuilder::
        // isPracticeUnlocked()), and a reset never touches it — once
        // genuinely earned, it stays earned.
        if ($action === 'level3_pass') {
          $stat->set('field_mastered', TRUE);
          $stat->set('field_ever_mastered', TRUE);
        }

        $stat->save();
      }
    }

    return new JsonResponse(['status' => 'ok', 'quiz_level' => $quizLevel]);
  }

  /**
   * POST /api/lms/flashcard/reset-mastery — "Reset Mastery" on the deck
   * card, for a student who wants to go back through "Study Now" and
   * re-earn the 100% that unlocks Spelling/Pronunciation/Reading/
   * Listening rather than being stuck there permanently. Thin wrapper
   * around StudyQueueBuilder::resetMastery() — see its docblock for what
   * this does and, just as importantly, does NOT touch (the real SM-2
   * schedule is left alone).
   *
   * Body: {"deck_id": int}.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   `{status: 'ok', reset_count: int}` — how many cards were reset, so
   *   the deck card can update its own mastery bar/lock state without a
   *   full page reload.
   */
  public function resetMastery(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $data = json_decode($request->getContent(), TRUE) ?? [];
    $deckId = (int) ($data['deck_id'] ?? 0);
    if (!$deckId) {
      return new JsonResponse(['error' => 'Missing deck_id'], 400);
    }
    if (!$this->loadVisibleDeck($deckId, $uid)) {
      return new JsonResponse(['error' => 'Deck not found'], 404);
    }

    $resetCount = $this->queueBuilder->resetMastery($uid, $deckId);
    return new JsonResponse(['status' => 'ok', 'reset_count' => $resetCount]);
  }

  /**
   * GET /api/lms/flashcard/session-stats?deck=ID
   */
  public function sessionStats(Request $request): JsonResponse {
    $uid = (int) $this->currentUser->id();
    if (!$uid) {
      return new JsonResponse(['error' => 'Unauthorized'], 403);
    }

    $deckId = (int) $request->query->get('deck');
    if (!$deckId) {
      return new JsonResponse(['error' => 'Missing deck'], 400);
    }
    if (!$this->loadVisibleDeck($deckId, $uid)) {
      return new JsonResponse(['error' => 'Deck not found'], 404);
    }

    $counts = $this->queueBuilder->getDueCounts($uid, $deckId);
    return new JsonResponse(['status' => 'ok'] + $counts);
  }

  /**
   * Loads a deck only if it's visible to the given user, else NULL.
   *
   * Guards every deck-scoped endpoint here against a guessed/tampered
   * deck ID being used to read or study someone else's private deck —
   * see StudyQueueBuilder::isDeckVisibleTo() for the visibility rule.
   */
  private function loadVisibleDeck(int $deckId, int $uid): ?Deck {
    $deck = $this->entityTypeManager->getStorage('lms_flashcard_deck')->load($deckId);
    if (!$deck instanceof Deck || !$this->queueBuilder->isDeckVisibleTo($deck, $uid)) {
      return NULL;
    }
    return $deck;
  }

}
