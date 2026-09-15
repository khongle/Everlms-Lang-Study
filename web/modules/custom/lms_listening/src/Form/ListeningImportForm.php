<?php

declare(strict_types=1);

namespace Drupal\lms_listening\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\file\Entity\File;
use Drupal\lms_listening\Service\ListeningImportBatchService;
use Drupal\lms_listening\Service\ListeningImportValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Import a Listening Passage (+ comprehension questions) from JSON.
 *
 * Mirrors lms_reading's ReadingImportForm as the primary content-creation
 * path for this module — see ListeningImportValidator's docblock for the
 * per-item payload schema. Open to any authenticated user, matching
 * lms_reading.import/lms_flashcard.import: a student importing their own
 * passage becomes its owner and can manage it afterwards via the owner
 * exception in ListeningAccessControlHandler.
 *
 * The payload may hold a single {listening_passage, questions} object (the
 * original shape ListeningImportValidator expects), or several of them at
 * once — either a bare top-level JSON array, or that array wrapped in a
 * "listenings" key — to create many passages from one paste. Each item is
 * validated independently but the whole batch imports as one all-or-nothing
 * unit: if any item fails validation, nothing is created.
 */
final class ListeningImportForm extends FormBase {

  public function __construct(
    protected ListeningImportValidator $importValidator,
    protected ListeningImportBatchService $importBatch,
    protected AccountProxyInterface $currentUser,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('lms_listening.import_validator'),
      $container->get('lms_listening.import_batch'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_listening_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'lms_listening/listening_import';
    $form['#attributes']['class'][] = 'lms-listening-import-form';

    $form['example'] = [
      '#markup' => $this->buildExampleTip(),
    ];

    $form['json_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('JSON file'),
      '#upload_location' => 'temporary://listening-imports',
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'json'],
      ],
      '#description' => $this->t('After choosing a file, click its "Upload" button below — then click Import.'),
    ];

    $form['divider'] = [
      '#markup' => '<div class="lms-listening-import__divider">' . $this->t('or') . '</div>',
    ];

    $form['json_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Paste JSON'),
      '#rows' => 16,
      '#description' => $this->t('Paste JSON copied directly from ChatGPT (or any other source) matching the schema shown above. To import several passages at once, wrap them in a "listenings" array (or paste a bare JSON array) of {listening_passage, questions} objects.'),
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

    // Accept either one {listening_passage, questions} object, or several of
    // them — as a bare top-level array, or that array under a "listenings"
    // key (friendlier when the payload is otherwise a single object, and
    // matches how sources like ChatGPT tend to name a wrapped list).
    if (isset($data['listenings']) && is_array($data['listenings'])) {
      $items = $data['listenings'];
    }
    elseif (array_is_list($data) && $data !== []) {
      $items = $data;
    }
    else {
      $items = [$data];
    }

    $is_batch = count($items) > 1;

    $validated_items = [];
    foreach ($items as $index => $item) {
      if (!is_array($item)) {
        // Each item gets its own error name — FormState::setErrorByName()
        // silently drops any error whose name it has already seen, so
        // reusing 'json_text' for every item would only ever surface the
        // first one and hide the rest.
        $form_state->setErrorByName($is_batch ? 'json_text_item_' . $index : 'json_text', $this->t('Item @position must be a JSON object.', ['@position' => $index + 1]));
        continue;
      }

      $validated = $this->importValidator->validate($item, $this->currentUser);
      $validated_items[] = $validated;

      $prefix = '';
      if ($is_batch) {
        $label = $item['listening_passage']['title'] ?? '';
        $prefix = (string) $this->t('Item @position (@label): ', [
          '@position' => $index + 1,
          '@label' => $label !== '' ? $label : (string) $this->t('untitled'),
        ]);
      }

      foreach ($validated['errors'] as $error_index => $error) {
        $form_state->setErrorByName($is_batch ? 'json_text_item_' . $index . '_' . $error_index : 'json_text', $prefix . $error);
      }
    }

    $form_state->set('lms_listening_validated_import', $validated_items);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    /** @var array[] $validated_items */
    $validated_items = $form_state->get('lms_listening_validated_import');

    $passage_ids = [];
    $titles = [];
    foreach ($validated_items as $validated) {
      $passage_ids[] = $this->importBatch->import($validated, (int) $this->currentUser->id());
      $titles[] = $validated['passage']['title'];
    }

    $fids = $form_state->getValue('json_file');
    if (!empty($fids)) {
      $file = File::load(reset($fids));
      $file?->delete();
    }

    if (count($passage_ids) === 1) {
      $this->messenger()->addStatus($this->t('Listening passage "@title" imported.', ['@title' => $titles[0]]));
      $form_state->setRedirect('lms_listening.listen', ['lms_listening_passage' => $passage_ids[0]]);
      return;
    }

    $this->messenger()->addStatus($this->formatPlural(
      count($passage_ids),
      '1 listening passage imported: @titles',
      '@count listening passages imported: @titles',
      ['@titles' => implode(', ', $titles)],
    ));
    $form_state->setRedirect('lms_listening.my_passages');
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
   * Builds the "example JSON" tip block — a 3-tab switcher (Sentence /
   * Narrative / Dialogue, see js/listening-import.js for the click
   * handling), same pattern as ReadingImportForm::buildExampleTip().
   */
  private function buildExampleTip(): Markup {
    $html = '<div class="lms-listening-import__tip">';
    // Called out on its own, above the format tabs, rather than only
    // inside each sample's own <code> block — a pasted-in ChatGPT payload
    // commonly drifts to something like "level": "medium" or "beginner"
    // (values that look plausible but aren't accepted), and that only
    // ever surfaced as an error message after Import was clicked. Stating
    // the exact accepted values up front, matching
    // ListeningImportValidator::LEVEL_VALUES/FORMAT_VALUES, catches that
    // before it happens instead of after.
    $html .= '<p class="lms-listening-import__tip-hint lms-listening-import__tip-hint--schema">';
    $html .= '<i class="fas fa-circle-info"></i> ';
    $html .= $this->t('<code>listening_passage.level</code> must be exactly one of: <code>easy</code>, <code>normal</code>, <code>challenge</code>. <code>listening_passage.format</code> must be exactly one of: <code>sentence</code>, <code>narrative</code>, <code>dialogue</code> (optional, defaults to <code>sentence</code>).');
    $html .= '</p>';
    $html .= '<div class="lms-listening-import__example-tabs" role="tablist" aria-label="' . $this->t('Example JSON format') . '">';
    $html .= '<button type="button" class="lms-listening-import__example-tab is-active" data-listening-example-tab="sentence" role="tab" aria-selected="true">';
    $html .= '<i class="fas fa-comment-dots"></i> ' . $this->t('Sentence');
    $html .= '</button>';
    $html .= '<button type="button" class="lms-listening-import__example-tab" data-listening-example-tab="narrative" role="tab" aria-selected="false">';
    $html .= '<i class="fas fa-align-left"></i> ' . $this->t('Narrative');
    $html .= '</button>';
    $html .= '<button type="button" class="lms-listening-import__example-tab" data-listening-example-tab="dialogue" role="tab" aria-selected="false">';
    $html .= '<i class="fas fa-comments"></i> ' . $this->t('Dialogue');
    $html .= '</button>';
    $html .= '</div>';

    $html .= $this->buildExamplePanel(
      'sentence',
      $this->t('One short utterance + a single comprehension question — "Level 3" in the listening plan. Good for turning a Flashcard example sentence into a quick listening check.'),
      $this->buildSentenceSample(),
      FALSE,
    );
    $html .= $this->buildExamplePanel(
      'narrative',
      $this->t('A short story built from the deck\'s vocabulary, with progressive hints. "Level 4" in the listening plan.'),
      $this->buildNarrativeSample(),
      TRUE,
    );
    $html .= $this->buildExamplePanel(
      'dialogue',
      $this->t('A back-and-forth conversation instead of a paragraph. Also "Level 4".'),
      $this->buildDialogueSample(),
      TRUE,
    );
    $html .= '</div>';

    return Markup::create($html);
  }

  private function buildSentenceSample(): array {
    return [
      'listening_passage' => [
        'title' => 'Listening: salmon for dinner',
        'deck_machine_name' => 'everyday-english-meat-fish-fruit-vegetables',
        'format' => 'sentence',
        'level' => 'easy',
        'body' => 'I usually eat salmon for dinner on Fridays.',
        'translation' => 'Tôi thường ăn cá hồi vào bữa tối các ngày thứ Sáu.',
        'card_terms' => ['salmon'],
      ],
      'questions' => [
        [
          'type' => 'multiple_choice',
          'text' => 'What does the speaker usually eat for dinner on Fridays?',
          'choices' => [
            ['text' => 'Salmon', 'correct' => TRUE],
            ['text' => 'Sausage', 'correct' => FALSE],
            ['text' => 'Squid', 'correct' => FALSE],
          ],
        ],
      ],
    ];
  }

  private function buildNarrativeSample(): array {
    return [
      'listening_passage' => [
        'title' => 'Listening: A trip to the supermarket',
        'deck_machine_name' => 'everyday-english-meat-fish-fruit-vegetables',
        'format' => 'narrative',
        'level' => 'normal',
        'body' => "Yesterday I went to the supermarket because we didn't have any vegetables left. I bought some carrots, onions, and a fresh avocado. I wanted to make a healthy dinner, but I forgot to buy tomatoes.",
        'hints' => ['supermarket', 'vegetables', 'forgot something'],
        'translation' => 'Hôm qua tôi đã đi siêu thị vì nhà hết rau. Tôi đã mua cà rốt, hành tây và một quả bơ tươi. Tôi muốn nấu một bữa tối lành mạnh, nhưng tôi quên mua cà chua.',
        'card_terms' => ['carrot', 'onion', 'avocado', 'tomato'],
      ],
      'questions' => [
        [
          'type' => 'multiple_choice',
          'text' => 'Why did the speaker go to the supermarket?',
          'choices' => [
            ['text' => 'To buy meat', 'correct' => FALSE],
            ['text' => 'To buy vegetables', 'correct' => TRUE],
            ['text' => 'To meet a friend', 'correct' => FALSE],
          ],
        ],
        [
          'type' => 'true_false',
          'text' => 'The speaker remembered to buy tomatoes.',
          'correct_answer' => FALSE,
        ],
      ],
    ];
  }

  private function buildDialogueSample(): array {
    return [
      'listening_passage' => [
        'title' => "Listening: At the Chemist's",
        'deck_machine_name' => 'everyday-english-at-the-chemists',
        'format' => 'dialogue',
        'level' => 'challenge',
        'body' => "David goes to the chemist's because he has a bad cough.",
        'dialogue' => [
          ['speaker' => 'Chemist', 'line' => 'Good morning! How can I help you today?'],
          ['speaker' => 'David', 'line' => "I've had a bad cough for three days. Do you have anything for it?"],
          ['speaker' => 'Chemist', 'line' => 'I can recommend this cough medicine. Take it twice a day, after meals.'],
        ],
        'hints' => ['a health problem', 'something to take twice a day'],
        'translation' => "Dược sĩ: Chào buổi sáng! Tôi có thể giúp gì cho bạn?\nDavid: Tôi bị ho nặng đã ba ngày rồi. Anh có thuốc gì cho việc này không?\nDược sĩ: Tôi có thể giới thiệu loại thuốc ho này. Uống hai lần một ngày, sau bữa ăn.",
        'card_terms' => ['chemist', 'cough medicine', 'medicine'],
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
      ],
    ];
  }

  /**
   * Renders one `data-listening-example-panel` tab panel for a sample
   * payload — shared by the Sentence/Narrative/Dialogue examples above.
   */
  private function buildExamplePanel(string $key, $description, array $sample, bool $hidden): string {
    $json = json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $code_id = 'lms-listening-example-json-' . $key;

    $html = '<div class="lms-listening-import__example-panel" data-listening-example-panel="' . $key . '"' . ($hidden ? ' hidden' : '') . '>';
    $html .= '<p class="lms-listening-import__tip-hint"><i class="fas fa-lightbulb"></i> ' . $description . '</p>';
    if ($key === 'sentence') {
      $html .= '<p class="lms-listening-import__tip-hint">' . $this->t('Every term in <code>card_terms</code> must already exist as a card in the deck named by <code>deck_machine_name</code> — import the vocabulary into Flashcards first, then build the listening passage around it. Exactly one <code>questions</code> item is typical for Sentence.') . '</p>';
    }
    elseif ($key === 'narrative') {
      $html .= '<p class="lms-listening-import__tip-hint">' . $this->t('<code>hints</code> and <code>translation</code> are both optional. <code>hints</code> are revealed one at a time only after the student has listened, never shown up front. <code>translation</code> is independent of that — a "Show translation" button appears on the Listen page whenever a passage has one, defaulting to hidden either way.') . '</p>';
    }
    else {
      $html .= '<p class="lms-listening-import__tip-hint">' . $this->t('Set <code>listening_passage.format</code> to <code>"dialogue"</code> and replace <code>body</code> with <code>dialogue</code> — an ordered list of <code>{"speaker", "line"}</code> turns (at least 2). <code>body</code> becomes optional: a one-line scene-setting intro, or omit it entirely.') . '</p>';
    }
    $html .= '<div class="lms-listening-import__tip-actions">';
    $html .= '<button type="button" class="lms-listening-import__tip-btn" data-listening-use-example="' . $code_id . '">' . $this->t('Use this example') . '</button>';
    $html .= '<button type="button" class="lms-listening-import__tip-btn" data-listening-copy-example="' . $code_id . '">' . $this->t('Copy') . '</button>';
    $html .= '</div>';
    $html .= '<pre class="lms-listening-import__tip-code"><code id="' . $code_id . '">' . htmlspecialchars($json, ENT_QUOTES, 'UTF-8') . '</code></pre>';
    $html .= '</div>';

    return $html;
  }

}
