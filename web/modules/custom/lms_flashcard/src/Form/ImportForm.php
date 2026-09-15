<?php

declare(strict_types=1);

namespace Drupal\lms_flashcard\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\file\Entity\File;
use Drupal\lms_flashcard\Service\ImportBatchService;
use Drupal\lms_flashcard\Service\ImportValidator;
use Drupal\user\EntityOwnerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Import flashcards from a JSON file or pasted JSON text.
 *
 * This is the primary content-creation path for the module: an editor
 * generates a JSON list of vocabulary externally (e.g. with ChatGPT) and
 * imports it here, either creating a new deck or appending to an existing
 * one. The manual Card add/edit form (CardForm) is a fallback for one-off
 * fixes, not the main authoring flow.
 *
 * Open to any authenticated user, not just staff — a student importing
 * their own vocabulary becomes the deck's owner (ImportBatchService::
 * createDeck()) and may later append to or manage only their own decks;
 * staff (anyone with 'administer lms_course' or 'edit lms_course') may
 * append to any deck, matching FlashcardAccessControlHandler's rules.
 */
final class ImportForm extends FormBase {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ImportValidator $importValidator,
    protected ImportBatchService $importBatch,
    protected RendererInterface $renderer,
    protected AccountProxyInterface $currentUser,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('lms_flashcard.import_validator'),
      $container->get('lms_flashcard.import_batch'),
      $container->get('renderer'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_flashcard_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'lms_flashcard/flashcard_import';

    // Convenience: arriving here from a deck's own edit page (its
    // "+ Import cards (JSON)" link — see DeckForm::buildCardsPanel()) with
    // ?deck=<id> pre-selects "Append to an existing deck" + that deck, so
    // the editor doesn't have to find it again in the dropdown below.
    // Mirrors the same ?deck= convenience CardForm's own "Add card" link
    // already has. Re-validated server-side regardless (currentUserCanAppendTo()
    // is the same check validateForm() applies to a submitted deck_id), so
    // a tampered query string can't be used to see decks the deck_options
    // list below wouldn't otherwise include.
    $prefillDeckId = NULL;
    $requestDeckId = $this->getRequest()->query->get('deck');
    if ($requestDeckId && ctype_digit((string) $requestDeckId) && $this->currentUserCanAppendTo((int) $requestDeckId)) {
      $prefillDeckId = (int) $requestDeckId;
    }

    $form['mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Import into'),
      '#options' => [
        'new' => $this->t('Create a new deck'),
        'existing' => $this->t('Append to an existing deck'),
      ],
      '#default_value' => $prefillDeckId ? 'existing' : 'new',
      '#required' => TRUE,
    ];

    $canManageAnyDeck = $this->canManageAnyDeck();

    $deck_storage = $this->entityTypeManager->getStorage('lms_flashcard_deck');
    $deck_query = $deck_storage->getQuery()->accessCheck(FALSE)->sort('name');
    if (!$canManageAnyDeck) {
      // Non-staff users may only append to decks they own themselves.
      $deck_query->condition('uid', $this->currentUser->id());
    }
    $deck_ids = $deck_query->execute();
    $deck_options = [];
    foreach ($deck_storage->loadMultiple($deck_ids) as $deck) {
      $deck_options[$deck->id()] = $deck->label();
    }

    $form['deck_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Target deck'),
      '#options' => $deck_options,
      '#empty_option' => $this->t('- Select a deck -'),
      '#default_value' => $prefillDeckId,
      '#description' => $canManageAnyDeck ? NULL : $this->t('Only decks you created are listed here.'),
      '#states' => [
        'visible' => [':input[name="mode"]' => ['value' => 'existing']],
        'required' => [':input[name="mode"]' => ['value' => 'existing']],
      ],
    ];

    $form['example'] = [
      '#markup' => $this->buildExampleTip(),
    ];

    $form['json_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('JSON file'),
      '#upload_location' => 'temporary://flashcard-imports',
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'json'],
      ],
      '#description' => $this->t('After choosing a file, click its "Upload" button below — then click Import.'),
    ];

    $form['divider'] = [
      '#markup' => '<div class="lms-flashcard-import__divider">' . $this->t('or') . '</div>',
    ];

    $form['json_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Paste JSON'),
      '#rows' => 16,
      '#description' => $this->t('Paste JSON copied directly from ChatGPT (or any other source) matching the deck/cards schema.'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $mode = $form_state->getValue('mode');
    if ($mode === 'existing') {
      $deckId = (int) $form_state->getValue('deck_id');
      if (!$deckId) {
        $form_state->setErrorByName('deck_id', $this->t('Select a deck to append cards to.'));
        return;
      }
      // Re-check ownership server-side — the deck_id select is already
      // filtered to the user's own decks in buildForm(), but that's only a
      // UI convenience; a tampered POST must not be able to append cards
      // into someone else's deck.
      if (!$this->currentUserCanAppendTo($deckId)) {
        $form_state->setErrorByName('deck_id', $this->t('You do not have permission to add cards to that deck.'));
        return;
      }
    }

    $raw = $this->extractJsonText($form_state);
    if ($raw === NULL) {
      $form_state->setErrorByName('json_text', $this->t('Upload a .json file or paste JSON to import.'));
      return;
    }

    try {
      $data = json_decode($raw, TRUE, 512, JSON_THROW_ON_ERROR);
    }
    catch (\JsonException $e) {
      $form_state->setErrorByName('json_text', $this->t('The JSON could not be parsed: @message', ['@message' => $e->getMessage()]));
      return;
    }

    if (!is_array($data)) {
      $form_state->setErrorByName('json_text', $this->t('The JSON must decode to an object.'));
      return;
    }

    // The form's radio selection is authoritative over whatever the JSON
    // itself says, so an editor can paste a "new deck" style payload but
    // still route it into an existing deck by picking that radio (and vice
    // versa, ignoring any stray deck_id in a paste meant for a new deck).
    if ($mode === 'existing') {
      unset($data['deck']);
      $data['deck_id'] = (int) $form_state->getValue('deck_id');
    }
    else {
      unset($data['deck_id']);
    }

    $validated = $this->importValidator->validate($data);
    $form_state->set('lms_flashcard_validated_import', $validated);

    $this->reportRowIssues($validated);

    foreach ($validated['deck_errors'] as $error) {
      $form_state->setErrorByName('json_text', $error);
    }

    if (!$validated['deck_errors'] && $validated['valid_row_count'] === 0) {
      $form_state->setErrorByName('json_text', $this->t('No valid card rows to import — see the errors above.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    /** @var array $validated */
    $validated = $form_state->get('lms_flashcard_validated_import');
    $valid_rows = array_values(array_filter($validated['rows'], static fn (array $row) => empty($row['errors'])));

    $this->importBatch->startImport(
      $validated['mode'],
      $validated['deck'] ?? [],
      $validated['deck_id'] ?? NULL,
      $valid_rows,
    );

    // The uploaded file's contents are already captured in $valid_rows; the
    // temporary file entity itself has no further purpose.
    $fids = $form_state->getValue('json_file');
    if (!empty($fids)) {
      $file = File::load(reset($fids));
      $file?->delete();
    }

    // Always land on "My Flashcards" — unlike the deck canonical page or
    // the admin collection (entity.lms_flashcard_deck.collection, gated by
    // 'administer lms_course'), it's reachable by every authenticated user
    // regardless of role, shows the deck(s) they just imported, and leads
    // straight into studying them.
    $form_state->setRedirect('lms_flashcard.my_decks');
  }

  /**
   * Whether the current user may append cards to any deck, not just theirs.
   */
  private function canManageAnyDeck(): bool {
    return $this->currentUser->hasPermission('administer lms_course')
      || $this->currentUser->hasPermission('edit lms_course');
  }

  /**
   * Whether the current user may append cards to a specific deck.
   */
  private function currentUserCanAppendTo(int $deckId): bool {
    if ($this->canManageAnyDeck()) {
      return TRUE;
    }
    $deck = $this->entityTypeManager->getStorage('lms_flashcard_deck')->load($deckId);
    return $deck instanceof EntityOwnerInterface && (int) $deck->getOwnerId() === (int) $this->currentUser->id();
  }

  /**
   * Builds the "example JSON" tip block shown above the file/paste inputs.
   *
   * A static, always-visible sample of the exact schema ImportValidator
   * expects — the point is that an editor never has to leave this page (or
   * remember the schema) to know what to paste in after asking ChatGPT for
   * vocabulary. The "Use this example" / "Copy" buttons are wired up by
   * js/flashcard-import.js.
   */
  private function buildExampleTip(): Markup {
    $sample = [
      'deck' => [
        'name' => 'Everyday English: Family & Home',
        'machine_name' => 'everyday-english-family-home',
        'topic' => 'Family',
        'description' => 'Common words you use every day at home.',
        'language' => 'en',
        'native_language' => 'vi',
      ],
      'cards' => [
        [
          'term' => 'family',
          'definition' => 'A group of people related by blood or marriage, such as parents and children.',
          'translation' => 'gia đình',
          'part_of_speech' => 'noun',
          'pronunciation' => '/ˈfæməli/',
          'examples' => [
            [
              'sentence' => 'My family has dinner together every evening.',
              'translation' => 'Gia đình tôi ăn tối cùng nhau mỗi buổi tối.',
            ],
            [
              'sentence' => 'Her family lives near the city center.',
              'translation' => 'Gia đình cô ấy sống gần trung tâm thành phố.',
            ],
          ],
          'collocations' => [
            [
              'phrase' => 'immediate family',
              'translation' => 'gia đình gần (bố mẹ, con cái, anh chị em ruột)',
            ],
            [
              'phrase' => 'start a family',
              'translation' => 'lập gia đình, sinh con',
            ],
          ],
          'difficulty' => 'easy',
        ],
        [
          'term' => 'neighbor',
          'definition' => 'A person who lives next door or nearby.',
          'translation' => 'hàng xóm',
          'part_of_speech' => 'noun',
          'pronunciation' => '/ˈneɪbər/',
          'examples' => [
            [
              'sentence' => 'Our neighbor helped us carry the groceries.',
              'translation' => 'Hàng xóm giúp chúng tôi mang đồ tạp hóa.',
            ],
          ],
          'difficulty' => 'medium',
        ],
      ],
    ];
    $json = json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $html = '<details class="lms-flashcard-import__tip" open>';
    $html .= '<summary>';
    $html .= '<i class="fas fa-lightbulb"></i> ' . $this->t('Example JSON (tip: ask ChatGPT to generate a list in this exact shape)');
    $html .= '</summary>';
    $html .= '<p class="lms-flashcard-import__tip-hint">' . $this->t('Only <code>term</code> and <code>definition</code> are required per card — everything else is optional. For "Append to an existing deck", drop the top-level <code>"deck"</code> object and keep just <code>"cards"</code>.') . '</p>';
    $html .= '<p class="lms-flashcard-import__tip-hint">' . $this->t('<code>translation</code> is the term itself translated into the deck\'s native language (e.g. "pineapple" → "quả dứa") — a short word/phrase, not the fuller English <code>definition</code>. Shown in the study page\'s "Which word matches this meaning?" recall check and editable later from Quick Edit while studying.') . '</p>';
    $html .= '<p class="lms-flashcard-import__tip-hint">' . $this->t('<code>collocations</code> are common word combinations this term appears in (e.g. "start a family" for "family") — separate from <code>examples</code>, which are full sentences. Each entry needs both <code>phrase</code> and <code>translation</code>.') . '</p>';
    $html .= '<p class="lms-flashcard-import__tip-hint">' . $this->t('Updating existing cards instead of duplicating them: use a deck\'s "Export JSON" link (on <a href="@my_decks">My Flashcards</a>) to get its current content with each card\'s <code>id</code> included, edit it (an LLM works well for this), then re-import here with "Append to an existing deck". A card whose <code>id</code> matches one already in the deck is updated in place; a card with no <code>id</code> is added as new.', [
      '@my_decks' => '/learn/flashcards',
    ]) . '</p>';
    $html .= '<p class="lms-flashcard-import__tip-hint">' . $this->t('<code>deck.machine_name</code> is a stable ID for the deck (auto-generated from the name if you leave it out). Re-importing with the same machine_name is blocked instead of silently creating a duplicate deck — this is what keeps repeated imports from "nhầm" (mixing up which deck gets which words). <code>deck.topic</code> is just a category label (e.g. "Family", "Travel") for browsing decks; a new topic is created automatically the first time it\'s used.') . '</p>';
    $html .= '<p class="lms-flashcard-import__tip-hint">' . $this->t('Pronunciation audio (MP3) can\'t be set from JSON — upload it per card afterwards by editing the card. Cards without an uploaded MP3 still get read aloud during study using the browser\'s built-in voice.') . '</p>';
    $html .= '<div class="lms-flashcard-import__tip-actions">';
    $html .= '<button type="button" class="lms-flashcard-import__tip-btn" data-flashcard-use-example>' . $this->t('Use this example') . '</button>';
    $html .= '<button type="button" class="lms-flashcard-import__tip-btn" data-flashcard-copy-example>' . $this->t('Copy') . '</button>';
    $html .= '</div>';
    $html .= '<pre class="lms-flashcard-import__tip-code"><code id="lms-flashcard-example-json">' . htmlspecialchars($json, ENT_QUOTES, 'UTF-8') . '</code></pre>';
    $html .= '</details>';

    return Markup::create($html);
  }

  /**
   * Reads the JSON payload from the file upload or the pasted textarea.
   */
  private function extractJsonText(FormStateInterface $form_state): ?string {
    $fids = $form_state->getValue('json_file');
    if (!empty($fids)) {
      $file = File::load(reset($fids));
      if ($file) {
        $contents = file_get_contents($file->getFileUri());
        if ($contents !== FALSE && trim($contents) !== '') {
          return $contents;
        }
      }
    }

    $text = trim((string) $form_state->getValue('json_text'));
    return $text !== '' ? $text : NULL;
  }

  /**
   * Surfaces per-row validation errors/warnings as messages with a table.
   */
  private function reportRowIssues(array $validated): void {
    $error_rows = array_filter($validated['rows'], static fn (array $row) => !empty($row['errors']));
    $warning_rows = array_filter($validated['rows'], static fn (array $row) => empty($row['errors']) && !empty($row['warnings']));

    if ($error_rows) {
      $this->messenger()->addError($this->buildIssueTable($error_rows, 'errors', $this->t('Rows with errors (not imported)')));
    }
    if ($warning_rows) {
      $this->messenger()->addWarning($this->buildIssueTable($warning_rows, 'warnings', $this->t('Rows with warnings (will still be imported)')));
    }
  }

  /**
   * Renders a themed table of row issues to a Markup string for messenger().
   */
  private function buildIssueTable(array $rows, string $key, $caption): Markup {
    $table_rows = [];
    foreach ($rows as $row) {
      $table_rows[] = [
        $row['index'] + 1,
        $row['data']['term'] ?? '—',
        implode('; ', $row[$key]),
      ];
    }

    $build = [
      '#type' => 'table',
      '#caption' => $caption,
      '#header' => [$this->t('Row'), $this->t('Term'), $key === 'errors' ? $this->t('Error') : $this->t('Warning')],
      '#rows' => $table_rows,
    ];

    return Markup::create($this->renderer->renderInIsolation($build));
  }

}
