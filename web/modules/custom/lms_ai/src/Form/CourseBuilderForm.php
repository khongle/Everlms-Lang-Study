<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\lms_ai\Plugin\AiProviderManager;
use Drupal\lms_ai\Service\CourseBuilderService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interactive wizard form for AI Course Building.
 */
class CourseBuilderForm extends FormBase {

  protected CourseBuilderService $courseBuilderService;

  protected AiProviderManager $aiProviderManager;

  protected ConfigFactoryInterface $aiConfigFactory;

  protected PrivateTempStoreFactory $tempStoreFactory;

  protected TimeInterface $time;

  public function __construct(
    CourseBuilderService $courseBuilderService,
    AiProviderManager $aiProviderManager,
    ConfigFactoryInterface $aiConfigFactory,
    PrivateTempStoreFactory $tempStoreFactory,
    TimeInterface $time
  ) {
    $this->courseBuilderService = $courseBuilderService;
    $this->aiProviderManager = $aiProviderManager;
    $this->aiConfigFactory = $aiConfigFactory;
    $this->tempStoreFactory = $tempStoreFactory;
    $this->time = $time;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('lms_ai.course_builder'),
      $container->get('plugin.manager.lms_ai_provider'),
      $container->get('config.factory'),
      $container->get('tempstore.private'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_ai_course_builder_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attributes']['class'][] = 'lms-ai-builder-form';

    $form['intro'] = [
      '#markup' => '<h2>Build a course draft with AI</h2><p>Generate a reviewable outline first, then save it as draft course content when it is ready.</p>',
    ];

    $provider_options = $this->providerOptions();
    $default_provider = $this->defaultProvider($provider_options);
    $draft_key = $this->currentDraftKey($form_state);
    $stored_draft = $draft_key ? $this->draftStore()->get($draft_key) : NULL;

    $form['topic'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Course Topic'),
      '#description' => $this->t('Example: Safe wheelchair transfers for new caregivers'),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#default_value' => $stored_draft['topic'] ?? '',
    ];

    $form['audience'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Target Audience'),
      '#description' => $this->t('Example: Beginner caregivers'),
      '#default_value' => 'Beginners',
      '#required' => TRUE,
    ];

    if (!empty($stored_draft['audience'])) {
      $form['audience']['#default_value'] = $stored_draft['audience'];
    }

    $form['tone'] = [
      '#type' => 'select',
      '#title' => $this->t('Tone of Voice'),
      '#options' => [
        'professional' => 'Professional & Academic',
        'casual' => 'Casual & Enthusiastic',
        'storytelling' => 'Storytelling & Anecdotal',
      ],
      '#default_value' => $stored_draft['tone'] ?? 'professional',
    ];

    $form['provider'] = [
      '#type' => 'select',
      '#title' => $this->t('AI Provider'),
      '#options' => $provider_options,
      '#default_value' => $stored_draft['provider_id'] ?? $default_provider,
      '#required' => TRUE,
      '#description' => $this->t('Course drafts require a provider that supports JSON and text generation.'),
    ];

    $form['draft_key'] = [
      '#type' => 'hidden',
      '#value' => $draft_key,
    ];

    if (!empty($stored_draft['draft'])) {
      $form['draft_preview'] = $this->buildDraftPreview($stored_draft['draft'], $stored_draft['provider_id'] ?? '');
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['generate'] = [
      '#type' => 'submit',
      '#name' => 'generate_draft',
      '#value' => $this->t('Generate draft'),
      '#button_type' => 'primary',
      '#submit' => ['::generateDraftSubmit'],
    ];

    if (!empty($stored_draft['draft'])) {
      $form['actions']['save'] = [
        '#type' => 'submit',
        '#name' => 'save_draft',
        '#value' => $this->t('Save draft course'),
        '#button_type' => 'primary',
        '#submit' => ['::saveDraftSubmit'],
      ];

      $form['actions']['discard'] = [
        '#type' => 'submit',
        '#name' => 'discard_draft',
        '#value' => $this->t('Discard draft'),
        '#submit' => ['::discardDraftSubmit'],
        '#limit_validation_errors' => [],
      ];
    }

    return $form;
  }

  /**
   * Generates a reviewable draft and stores it privately for the current user.
   */
  public function generateDraftSubmit(array &$form, FormStateInterface $form_state): void {
    $topic = $form_state->getValue('topic');
    $audience = $form_state->getValue('audience');
    $tone = $form_state->getValue('tone');
    $provider_id = $form_state->getValue('provider');

    $draft = $this->courseBuilderService->generateDraft($topic, $audience, $tone, $provider_id);
    if (!$draft) {
      $this->messenger()->addError($this->t('AI could not generate a valid course draft. Try again or switch provider.'));
      $form_state->setRebuild(TRUE);
      return;
    }

    $draft_key = 'course_builder_' . hash('sha256', Crypt::randomBytesBase64(32));
    $this->draftStore()->set($draft_key, [
      'draft' => $draft,
      'topic' => $topic,
      'audience' => $audience,
      'tone' => $tone,
      'provider_id' => $provider_id,
      'created' => $this->time->getRequestTime(),
    ]);

    $form_state->set('draft_key', $draft_key);
    $this->messenger()->addStatus($this->t('Draft generated. Review the outline, then save it as a draft course.'));
    $form_state->setRebuild(TRUE);
  }

  /**
   * Saves the reviewed draft into real course entities.
   */
  public function saveDraftSubmit(array &$form, FormStateInterface $form_state): void {
    $draft_key = $this->currentDraftKey($form_state);
    $stored_draft = $draft_key ? $this->draftStore()->get($draft_key) : NULL;

    if (empty($stored_draft['draft'])) {
      $this->messenger()->addError($this->t('The generated draft is no longer available. Generate it again before saving.'));
      $form_state->setRebuild(TRUE);
      return;
    }

    $course = $this->courseBuilderService->createCourseFromDraft(
      $stored_draft['draft'],
      $stored_draft['tone'] ?? 'professional',
      $stored_draft['provider_id'] ?? NULL
    );

    if (!$course) {
      $this->messenger()->addError($this->t('The reviewed draft could not be saved. No partial course was kept.'));
      $form_state->setRebuild(TRUE);
      return;
    }

    $this->draftStore()->delete($draft_key);
    $this->messenger()->addStatus($this->t('Draft course "%title" was saved. Review and publish it from the course editor.', ['%title' => $course->label()]));
    $form_state->setRedirectUrl($course->toUrl('edit-form'));
  }

  /**
   * Discards the current private draft.
   */
  public function discardDraftSubmit(array &$form, FormStateInterface $form_state): void {
    $draft_key = $this->currentDraftKey($form_state);
    if ($draft_key) {
      $this->draftStore()->delete($draft_key);
    }
    $form_state->set('draft_key', NULL);
    $this->messenger()->addStatus($this->t('Draft discarded.'));
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Submission is handled by button-specific callbacks.
  }

  /**
   * Builds provider select options from plugin discovery.
   */
  private function providerOptions(): array {
    $options = [];
    foreach ($this->aiProviderManager->getDefinitions() as $id => $definition) {
      $options[$id] = (string) $definition['label'];
    }
    return $options;
  }

  /**
   * Returns the configured default provider when available.
   */
  private function defaultProvider(array $provider_options): ?string {
    $configured = $this->aiConfigFactory->get('lms_ai.settings')->get('default_provider') ?: 'openai';
    if (isset($provider_options[$configured])) {
      return $configured;
    }

    return array_key_first($provider_options);
  }

  /**
   * Gets the active draft key from rebuild state or submitted values.
   */
  private function currentDraftKey(FormStateInterface $form_state): string {
    return (string) ($form_state->get('draft_key') ?: $form_state->getValue('draft_key') ?: '');
  }

  /**
   * Returns the private tempstore collection for AI course drafts.
   */
  private function draftStore(): PrivateTempStore {
    return $this->tempStoreFactory->get('lms_ai_course_builder');
  }

  /**
   * Builds a sanitized review preview for the generated draft.
   */
  private function buildDraftPreview(array $draft, string $provider_id): array {
    $items = [];

    foreach ($draft['sections'] ?? [] as $section) {
      $lessons = [];
      foreach ($section['lessons'] ?? [] as $lesson) {
        $lessons[] = ['#plain_text' => $lesson['title'] ?? ''];
      }

      $items[] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['lms-ai-builder-preview__section']],
        'title' => [
          '#markup' => '<h4>' . Html::escape($section['title'] ?? '') . '</h4>',
        ],
        'lessons' => [
          '#theme' => 'item_list',
          '#items' => $lessons,
        ],
      ];
    }

    $preview = [
      '#type' => 'container',
      '#attributes' => ['class' => ['lms-ai-builder-preview']],
      'heading' => [
        '#markup' => '<h3>' . $this->t('Review generated draft') . '</h3>',
      ],
      'provider' => [
        '#markup' => '<p><strong>' . $this->t('Provider') . ':</strong> ' . Html::escape($provider_id) . '</p>',
      ],
      'title' => [
        '#markup' => '<h4>' . Html::escape($draft['title'] ?? '') . '</h4>',
      ],
      'description' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['lms-ai-builder-preview__description']],
        'text' => [
          '#plain_text' => $draft['description'] ?? '',
        ],
      ],
      'sections' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['lms-ai-builder-preview__sections']],
        'items' => $items,
      ],
    ];

    if (!empty($draft['quiz']['questions'])) {
      $questions = [];
      foreach ($draft['quiz']['questions'] as $question) {
        $questions[] = ['#plain_text' => $question['question'] ?? ''];
      }

      $preview['quiz'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['lms-ai-builder-preview__quiz']],
        'title' => [
          '#markup' => '<h4>' . Html::escape((string) ($draft['quiz']['title'] ?? $this->t('Knowledge check'))) . '</h4>',
        ],
        'questions' => [
          '#theme' => 'item_list',
          '#items' => $questions,
        ],
      ];
    }

    return $preview;
  }

}
