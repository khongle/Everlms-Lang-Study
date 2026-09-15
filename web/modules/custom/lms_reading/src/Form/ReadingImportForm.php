<?php

declare(strict_types=1);

namespace Drupal\lms_reading\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\file\Entity\File;
use Drupal\lms_reading\Service\ReadingImportBatchService;
use Drupal\lms_reading\Service\ReadingImportValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Import a Reading Passage (+ questions/context items/glossary) from JSON.
 *
 * Mirrors lms_flashcard's ImportForm as the primary content-creation path
 * for this module — see ReadingImportValidator's docblock for the payload
 * schema. Open to any authenticated user, matching lms_flashcard.import: a
 * student importing their own passage becomes its owner and can manage it
 * afterwards via the owner exception in ReadingAccessControlHandler.
 */
final class ReadingImportForm extends FormBase {

  public function __construct(
    protected ReadingImportValidator $importValidator,
    protected ReadingImportBatchService $importBatch,
    protected AccountProxyInterface $currentUser,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('lms_reading.import_validator'),
      $container->get('lms_reading.import_batch'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_reading_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'lms_reading/reading_import';

    $form['example'] = [
      '#markup' => $this->buildExampleTip(),
    ];

    $form['json_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('JSON file'),
      '#upload_location' => 'temporary://reading-imports',
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'json'],
      ],
      '#description' => $this->t('After choosing a file, click its "Upload" button below — then click Import.'),
    ];

    $form['divider'] = [
      '#markup' => '<div class="lms-reading-import__divider">' . $this->t('or') . '</div>',
    ];

    $form['json_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Paste JSON'),
      '#rows' => 16,
      '#description' => $this->t('Paste JSON copied directly from ChatGPT (or any other source) matching the passage schema shown above.'),
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

    $validated = $this->importValidator->validate($data, $this->currentUser);
    $form_state->set('lms_reading_validated_import', $validated);

    foreach ($validated['errors'] as $error) {
      $form_state->setErrorByName('json_text', $error);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    /** @var array $validated */
    $validated = $form_state->get('lms_reading_validated_import');

    $passage_id = $this->importBatch->import($validated, (int) $this->currentUser->id());

    $fids = $form_state->getValue('json_file');
    if (!empty($fids)) {
      $file = File::load(reset($fids));
      $file?->delete();
    }

    $this->messenger()->addStatus($this->t('Reading passage "@title" imported.', ['@title' => $validated['passage']['title']]));
    $form_state->setRedirect('lms_reading.read', ['lms_reading_passage' => $passage_id]);
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
   * Builds the "example JSON" tip block shown above the file/paste inputs —
   * a 2-tab switcher (Narrative / Dialogue, see js/reading-import.js for
   * the click handling) rather than two stacked <details>, so picking the
   * one you need doesn't mean scrolling past the other's full JSON dump
   * first.
   */
  private function buildExampleTip(): Markup {
    $html = '<div class="lms-reading-import__tip">';
    $html .= '<div class="lms-reading-import__example-tabs" role="tablist" aria-label="' . $this->t('Example JSON format') . '">';
    $html .= '<button type="button" class="lms-reading-import__example-tab is-active" data-reading-example-tab="narrative" role="tab" aria-selected="true">';
    $html .= '<i class="fas fa-align-left"></i> ' . $this->t('Narrative');
    $html .= '</button>';
    $html .= '<button type="button" class="lms-reading-import__example-tab" data-reading-example-tab="dialogue" role="tab" aria-selected="false">';
    $html .= '<i class="fas fa-comments"></i> ' . $this->t('Dialogue');
    $html .= '</button>';
    $html .= '</div>';

    $html .= $this->buildExamplePanel(
      'narrative',
      $this->t('A plain paragraph. Tip: ask ChatGPT to generate one in this exact shape.'),
      $this->buildNarrativeSample(),
      FALSE,
    );
    $html .= $this->buildExamplePanel(
      'dialogue',
      $this->t('A back-and-forth conversation instead of a paragraph.'),
      $this->buildDialogueSample(),
      TRUE,
    );
    $html .= '</div>';

    return Markup::create($html);
  }

  /**
   * The Narrative-format sample payload (passage.format omitted — it
   * defaults to "narrative", so existing JSON built before the Dialogue
   * format existed keeps working unchanged).
   */
  private function buildNarrativeSample(): array {
    $sample = [
      'passage' => [
        'title' => "At the Chemist's — Easy",
        'deck_machine_name' => 'everyday-english-at-the-chemists',
        'level' => 'easy',
        'body' => "Yesterday, I went to the chemist's because I had a bad cough and a sore throat. I told the pharmacist about my symptoms. She recommended some cough medicine and told me to take it twice a day.",
        'card_terms' => ['chemist', 'cough', 'pharmacist', 'medicine', 'symptom'],
      ],
      'questions' => [
        [
          'type' => 'multiple_choice',
          'text' => "Why did the person go to the chemist's?",
          'choices' => [
            ['text' => 'For a haircut', 'correct' => FALSE],
            ['text' => 'For a cough and sore throat', 'correct' => TRUE],
          ],
        ],
        [
          'type' => 'true_false',
          'text' => 'The pharmacist told them to rest.',
          'correct_answer' => TRUE,
        ],
        [
          'type' => 'fill_blank',
          'text' => 'Take the medicine ___ a day.',
          'correct_answers' => ['twice'],
        ],
      ],
      'context_items' => [
        [
          'target_word' => 'recommended',
          'context_sentence' => 'The pharmacist recommended some medicine for my cough.',
          'choices' => ['refused', 'suggested', 'bought', 'forgot'],
          'correct_index' => 1,
        ],
      ],
      'word_glossary' => [
        [
          'term' => 'prescription',
          'meaning' => "A doctor's written instruction for medicine.",
          'pronunciation' => '/prɪˈskrɪpʃn/',
          'part_of_speech' => 'noun',
        ],
      ],
    ];

    return $sample;
  }

  /**
   * The Dialogue-format sample payload — passage.body here is just the
   * optional scene-setting intro; the conversation itself is
   * passage.dialogue, an ordered list of {speaker, line} turns.
   */
  private function buildDialogueSample(): array {
    return [
      'passage' => [
        'title' => "At the Chemist's — A Conversation",
        'deck_machine_name' => 'everyday-english-at-the-chemists',
        'level' => 'normal',
        'format' => 'dialogue',
        'body' => "David goes to the chemist's because he has a bad cough.",
        'dialogue' => [
          ['speaker' => 'Chemist', 'line' => 'Good morning! How can I help you today?'],
          ['speaker' => 'David', 'line' => "I've had a bad cough for three days. Do you have anything for it?"],
          ['speaker' => 'Chemist', 'line' => 'I can recommend this cough medicine. Take it twice a day, after meals.'],
          ['speaker' => 'David', 'line' => 'Thank you. Should I see a doctor if it does not get better?'],
          ['speaker' => 'Chemist', 'line' => "Yes, if it's still bad in a week, please see a doctor."],
        ],
        'card_terms' => ['chemist', 'cough', 'medicine'],
      ],
      'questions' => [
        [
          'type' => 'multiple_choice',
          'text' => 'How often should David take the cough medicine?',
          'choices' => [
            ['text' => 'Once a day', 'correct' => FALSE],
            ['text' => 'Twice a day, after meals', 'correct' => TRUE],
          ],
        ],
        [
          'type' => 'true_false',
          'text' => 'The chemist told David to see a doctor immediately.',
          'correct_answer' => FALSE,
        ],
      ],
      'context_items' => [
        [
          'target_word' => 'recommend',
          'context_sentence' => 'I can recommend this cough medicine.',
          'choices' => ['forbid', 'suggest', 'sell', 'forget'],
          'correct_index' => 1,
        ],
      ],
    ];
  }

  /**
   * Renders one `data-reading-example-panel` tab panel for a sample
   * payload — shared by the Narrative and Dialogue examples above.
   * js/reading-import.js shows exactly one panel at a time, toggled by
   * the tab buttons built in buildExampleTip().
   *
   * @param string $key
   *   Also used to build the panel's `<code>` id, so "Use this
   *   example"/"Copy" each act on their own panel's JSON, never whichever
   *   happens to be first on the page.
   */
  private function buildExamplePanel(string $key, $description, array $sample, bool $hidden): string {
    $json = json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $code_id = 'lms-reading-example-json-' . $key;

    $html = '<div class="lms-reading-import__example-panel" data-reading-example-panel="' . $key . '"' . ($hidden ? ' hidden' : '') . '>';
    $html .= '<p class="lms-reading-import__tip-hint"><i class="fas fa-lightbulb"></i> ' . $description . '</p>';
    if ($key === 'narrative') {
      $html .= '<p class="lms-reading-import__tip-hint">' . $this->t('Every term in <code>card_terms</code> must already exist as a card in the deck named by <code>deck_machine_name</code> — import the vocabulary into Flashcards first (see <a href="@import">Import Flashcards</a>), then build the reading passage around it.', ['@import' => '/manage/flashcard-decks/import']) . '</p>';
      $html .= '<p class="lms-reading-import__tip-hint">' . $this->t('At least one item in <code>questions</code> is required. <code>context_items</code> and <code>word_glossary</code> are optional — <code>word_glossary</code> is only for words in the passage that are NOT already in <code>card_terms</code>.') . '</p>';
    }
    else {
      $html .= '<p class="lms-reading-import__tip-hint">' . $this->t('Set <code>passage.format</code> to <code>"dialogue"</code> and replace <code>passage.body</code> with <code>passage.dialogue</code> — an ordered list of <code>{"speaker", "line"}</code> turns (at least 2). <code>passage.body</code> becomes optional: a one-line scene-setting intro shown above the conversation, or omit it entirely.') . '</p>';
      $html .= '<p class="lms-reading-import__tip-hint">' . $this->t('Everything else (<code>card_terms</code>, <code>questions</code>, <code>context_items</code>, <code>word_glossary</code>) works exactly the same as a Narrative passage — see its tab.') . '</p>';
    }
    $html .= '<div class="lms-reading-import__tip-actions">';
    $html .= '<button type="button" class="lms-reading-import__tip-btn" data-reading-use-example="' . $code_id . '">' . $this->t('Use this example') . '</button>';
    $html .= '<button type="button" class="lms-reading-import__tip-btn" data-reading-copy-example="' . $code_id . '">' . $this->t('Copy') . '</button>';
    $html .= '</div>';
    $html .= '<pre class="lms-reading-import__tip-code"><code id="' . $code_id . '">' . htmlspecialchars($json, ENT_QUOTES, 'UTF-8') . '</code></pre>';
    $html .= '</div>';

    return $html;
  }

}
