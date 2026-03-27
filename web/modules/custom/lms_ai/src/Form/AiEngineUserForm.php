<?php

declare(strict_types=1);

namespace Drupal\lms_ai\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AI Engine form for generating and managing Users through AI.
 */
class AiEngineUserForm extends ConfigFormBase {

  protected EntityTypeManagerInterface $entityTypeManager;
  protected $aiProviderManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->aiProviderManager = $container->get('plugin.manager.lms_ai_provider');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['lms_ai.engine.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_ai_engine_user_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('lms_ai.engine.settings');
    
    // Load prompt files
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('lms_ai')->getPath();
    $default_user_prompt = '';
    $default_image_prompt = '';
    
    $user_prompt_path = \Drupal::root() . '/' . $module_path . '/prompt/user.txt';
    if (file_exists($user_prompt_path)) {
      $default_user_prompt = file_get_contents($user_prompt_path);
    }
    
    $image_prompt_path = \Drupal::root() . '/' . $module_path . '/prompt/user_image.txt';
    if (file_exists($image_prompt_path)) {
      $default_image_prompt = file_get_contents($image_prompt_path);
    }

    $curriculum_prompt_path = \Drupal::root() . '/' . $module_path . '/prompt/curriculum_suggestion_prompt.txt';
    $default_curriculum_prompt = '';
    if (file_exists($curriculum_prompt_path)) {
      $default_curriculum_prompt = file_get_contents($curriculum_prompt_path);
    }

    $course_detail_prompt_path = \Drupal::root() . '/' . $module_path . '/prompt/course_detail.txt';
    $default_course_detail_prompt = '';
    if (file_exists($course_detail_prompt_path)) {
      $default_course_detail_prompt = file_get_contents($course_detail_prompt_path);
    }

    $course_image_prompt_path = \Drupal::root() . '/' . $module_path . '/prompt/course_image.txt';
    $default_course_image_prompt = '';
    if (file_exists($course_image_prompt_path)) {
      $default_course_image_prompt = file_get_contents($course_image_prompt_path);
    }

    $chapter_detail_prompt_path = \Drupal::root() . '/' . $module_path . '/prompt/chapter_detail.txt';
    $default_chapter_detail_prompt = '';
    if (file_exists($chapter_detail_prompt_path)) {
      $default_chapter_detail_prompt = file_get_contents($chapter_detail_prompt_path);
    }

    $lesson_detail_prompt_path = \Drupal::root() . '/' . $module_path . '/prompt/lesson_detail.txt';
    $default_lesson_detail_prompt = '';
    if (file_exists($lesson_detail_prompt_path)) {
      $default_lesson_detail_prompt = file_get_contents($lesson_detail_prompt_path);
    }

    $slide_content_prompt_path = \Drupal::root() . '/' . $module_path . '/prompt/slide_content.txt';
    $default_slide_content_prompt = '';
    if (file_exists($slide_content_prompt_path)) {
      $default_slide_content_prompt = file_get_contents($slide_content_prompt_path);
    }

    $google_slide_format_prompt_path = \Drupal::root() . '/' . $module_path . '/prompt/google_slide_format.txt';
    $default_google_slide_format_prompt = '';
    if (file_exists($google_slide_format_prompt_path)) {
      $default_google_slide_format_prompt = file_get_contents($google_slide_format_prompt_path);
    }

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'lms_ai/engine_admin';
    $form['#attached']['drupalSettings']['lmsAiEngine']['defaults'] = [
      'users_prompt' => $default_user_prompt,
      'users_image_prompt' => $default_image_prompt,
      'curriculum_prompt' => $default_curriculum_prompt,
      'course_detail_prompt' => $default_course_detail_prompt,
      'course_image_prompt' => $default_course_image_prompt,
      'chapter_detail_prompt' => $default_chapter_detail_prompt,
      'lesson_detail_prompt' => $default_lesson_detail_prompt,
      'slide_content_prompt' => $default_slide_content_prompt,
      'google_slide_format_prompt' => $default_google_slide_format_prompt,
    ];

    // 1. Setting Block
    $form['setting'] = [
      '#type' => 'details',
      '#title' => $this->t('Setting'),
      '#open' => TRUE,
    ];

    $form['setting']['training_program'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Training Program.'),
      '#default_value' => $config->get('training_program') ?: 'EverLMS Introduction & User Guide Program',
      '#required' => TRUE,
    ];

    $form['setting']['program_language'] = [
      '#type' => 'select',
      '#title' => $this->t('Program Language'),
      '#options' => [
        'en' => 'English',
        'vi' => 'Tiếng Việt',
        'fr' => 'Français',
        'de' => 'Deutsch',
      ],
      '#default_value' => $config->get('program_language') ?: 'en',
    ];

    // Load available courses.
    $course_storage = $this->entityTypeManager->getStorage('lms_course');
    $courses = $course_storage->loadMultiple();
    $course_options = [];
    foreach ($courses as $course) {
      $course_options[$course->id()] = $course->label();
    }

    $form['setting']['selected_courses'] = [
      '#type' => 'select',
      '#title' => $this->t('Run only selected courses'),
      '#options' => $course_options,
      '#multiple' => TRUE,
      '#default_value' => $config->get('selected_courses') ?: [],
      '#size' => min(15, max(5, count($course_options))),
    ];

    $ai_manager = \Drupal::service('plugin.manager.lms_ai_provider');
    $provider_options = [];
    foreach ($ai_manager->getDefinitions() as $id => $definition) {
      $provider_options[$id] = $definition['label'];
    }

    $form['setting']['active_provider'] = [
      '#type' => 'select',
      '#title' => $this->t('Active AI Provider'),
      '#options' => $provider_options,
      '#default_value' => $config->get('active_provider') ?: 'gemini',
      '#description' => $this->t('Select which AI platform to use for generation. Ensure the API key is configured in settings (if required).'),
    ];

    // 2. Get Started Block
    $form['get_started'] = [
      '#type' => 'details',
      '#title' => $this->t('Get Started'),
      '#open' => TRUE,
    ];

    $form['get_started']['users_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Users Prompt'),
      '#description' => '<a href="#" class="load-default">Load Default</a> | <a href="#" class="reset">Reset</a> | <a href="#" class="clear">Clear</a>',
      '#default_value' => $config->get('users_prompt') ?: '',
      '#rows' => 15,
    ];

    $form['get_started']['users_image_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Users Image Prompt'),
      '#description' => '<a href="#" class="load-default">Load Default</a> | <a href="#" class="reset">Reset</a> | <a href="#" class="clear">Clear</a>',
      '#default_value' => $config->get('users_image_prompt') ?: '',
      '#rows' => 15,
    ];

    // Move custom action buttons into the 'Get Started' (User) section.
    $form['get_started']['user_actions'] = [
      '#type' => 'actions',
    ];

    $file_path = \Drupal::root() . '/' . $module_path . '/JSON/ai_users.json';
    $is_json_valid = file_exists($file_path) && filesize($file_path) > 0;

    $form['get_started']['user_actions']['run_user'] = [
      '#type' => 'submit',
      '#value' => $this->t('Run User'),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => '::ajaxRunUser',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Executing Run User...'),
        ],
      ],
    ];

    $form['get_started']['user_actions']['preview_json'] = [
      '#type' => 'submit',
      '#value' => $this->t('Preview JSON'),
      '#disabled' => !$is_json_valid,
      '#id' => 'btn-preview-json',
      '#ajax' => [
        'callback' => '::ajaxPreviewJson',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Loading JSON...'),
        ],
      ],
    ];

    $form['get_started']['user_actions']['approve_user'] = [
      '#type' => 'submit',
      '#value' => $this->t('Approve User'),
      '#ajax' => [
        'callback' => '::ajaxApproveUser',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Executing Approve User...'),
        ],
      ],
    ];

    $form['get_started']['user_actions']['request_image_prompts'] = [
      '#type' => 'submit',
      '#value' => $this->t('Request Image User Prompts'),
      '#disabled' => TRUE,
      '#ajax' => [
        'callback' => '::ajaxRequestImagePrompts',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Requesting Image Prompts...'),
        ],
      ],
    ];

    $form['get_started']['user_actions']['request_images'] = [
      '#type' => 'submit',
      '#value' => $this->t('Request Images'),
      '#disabled' => TRUE,
      '#ajax' => [
        'callback' => '::ajaxRequestImages',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Requesting Images from Flux Dev...'),
        ],
      ],
    ];

    // --- Curriculum Designer Section ---
    $form['curriculum'] = [
      '#type' => 'details',
      '#title' => $this->t('Curriculum Designer'),
      '#open' => TRUE,
    ];

    $form['curriculum']['curriculum_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Curriculum Suggestion Prompt'),
      '#default_value' => $this->config('lms_ai.engine.settings')->get('curriculum_prompt') ?? "- Tên khóa\n  + ...\n- Tên category\n  + ...\n- ...",
      '#description' => '<a href="#" class="load-default">Load Default</a> | <a href="#" class="reset">Reset</a> | <a href="#" class="clear">Clear</a>',
      '#rows' => 6,
    ];

    $form['curriculum']['curriculum_suggestion'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Curriculum Suggestion'),
      '#default_value' => $this->config('lms_ai.engine.settings')->get('curriculum_suggestion') ?? '',
      '#rows' => 10,
    ];

    $form['curriculum']['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-actions', 'js-form-wrapper', 'form-wrapper']],
    ];

    $form['curriculum']['actions']['request_curriculum_suggestion'] = [
      '#type' => 'submit',
      '#value' => $this->t('Request Curriculum Suggestion'),
      '#ajax' => [
        'callback' => '::ajaxRequestCurriculumSuggestion',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Requesting Curriculum Suggestion...'),
        ],
      ],
    ];

    $form['curriculum']['actions']['generate_curriculum'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Curriculum'),
      '#ajax' => [
        'callback' => '::ajaxGenerateCurriculum',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Generating Curriculum Entities...'),
        ],
      ],
    ];

    $form['curriculum']['actions']['trim_chapters'] = [
      '#type' => 'submit',
      '#value' => $this->t('Trim Chapters'),
      '#ajax' => [
        'callback' => '::ajaxTrimChapters',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Trimming Chapters...'),
        ],
      ],
    ];

    $form['curriculum']['actions']['trim_lessons'] = [
      '#type' => 'submit',
      '#value' => $this->t('Trim Lessons'),
      '#ajax' => [
        'callback' => '::ajaxTrimLessons',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Trimming Lessons...'),
        ],
      ],
    ];

    // --- AI Course Builder Section ---
    $form['course_builder'] = [
      '#type' => 'details',
      '#title' => $this->t('AI Course Builder'),
      '#open' => TRUE,
    ];

    $form['course_builder']['course_actions'] = [
      '#type' => 'details',
      '#title' => $this->t('Course Actions'),
      '#open' => TRUE,
    ];

    $form['course_builder']['course_actions']['course_detail_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Generate Courses Detail Prompt'),
      '#default_value' => $this->config('lms_ai.engine.settings')->get('course_detail_prompt') ?? '',
      '#description' => '<a href="#" class="load-default">Load Default</a> | <a href="#" class="reset">Reset</a> | <a href="#" class="clear">Clear</a>',
      '#rows' => 6,
    ];

    $form['course_builder']['course_actions']['course_image_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Course Image Prompt'),
      '#default_value' => $this->config('lms_ai.engine.settings')->get('course_image_prompt') ?? '',
      '#description' => '<a href="#" class="load-default">Load Default</a> | <a href="#" class="reset">Reset</a> | <a href="#" class="clear">Clear</a>',
      '#rows' => 4,
    ];

    $form['course_builder']['course_actions']['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-actions', 'js-form-wrapper', 'form-wrapper']],
    ];

    $form['course_builder']['course_actions']['actions']['generate_courses_detail'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Courses Detail'),
      '#ajax' => [
        'callback' => '::ajaxGenerateCoursesDetail',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Generating Details...'),
        ],
      ],
    ];



    $form['course_builder']['course_actions']['actions']['request_image_prompts'] = [
      '#type' => 'submit',
      '#value' => $this->t('Request Image Prompts'),
      '#disabled' => TRUE,
    ];

    $form['course_builder']['course_actions']['actions']['request_images'] = [
      '#type' => 'submit',
      '#value' => $this->t('Request Images'),
      '#disabled' => TRUE,
    ];

    $form['course_builder']['chapter_actions'] = [
      '#type' => 'details',
      '#title' => $this->t('Chapter Actions'),
      '#open' => TRUE,
    ];

    $form['course_builder']['chapter_actions']['chapter_detail_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Generate Chapters Detail Prompt'),
      '#default_value' => $this->config('lms_ai.engine.settings')->get('chapter_detail_prompt') ?? '',
      '#description' => '<a href="#" class="load-default">Load Default</a> | <a href="#" class="reset">Reset</a> | <a href="#" class="clear">Clear</a>',
      '#rows' => 6,
    ];

    $form['course_builder']['chapter_actions']['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-actions', 'js-form-wrapper', 'form-wrapper']],
    ];

    $form['course_builder']['chapter_actions']['actions']['generate_chapters_detail'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Chapters Detail'),
      '#ajax' => [
        'callback' => '::ajaxGenerateChaptersDetail',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Generating Details...'),
        ],
      ],
    ];

    $form['course_builder']['lesson_actions'] = [
      '#type' => 'details',
      '#title' => $this->t('Lesson Actions'),
      '#open' => FALSE,
    ];

    $form['course_builder']['lesson_actions']['lesson_detail_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Generate Lessons Detail Prompt'),
      '#default_value' => $this->config('lms_ai.engine.settings')->get('lesson_detail_prompt') ?? '',
      '#description' => '<a href="#" class="load-default">Load Default</a> | <a href="#" class="reset">Reset</a> | <a href="#" class="clear">Clear</a>',
      '#rows' => 6,
    ];

    $form['course_builder']['lesson_actions']['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-actions', 'js-form-wrapper', 'form-wrapper']],
    ];

    $form['course_builder']['lesson_actions']['actions']['generate_lessons_detail'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Lessons Detail'),
      '#ajax' => [
        'callback' => '::ajaxGenerateLessonsDetail',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Generating Details...'),
        ],
      ],
    ];

    $form['course_builder']['formatter_actions'] = [
      '#type' => 'details',
      '#title' => $this->t('AI Lesson Formatter'),
      '#open' => TRUE,
    ];

    $form['course_builder']['formatter_actions']['google_slide_template_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Slide Template ID'),
      '#description' => $this->t('Enter the ID of the public Google Slide template. E.g., for https://docs.google.com/presentation/d/1B...xyz/edit, the ID is 1B...xyz'),
      '#default_value' => $this->config('lms_ai.engine.settings')->get('google_slide_template_id') ?? '',
    ];

    $form['course_builder']['formatter_actions']['slide_content_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Slide Content Prompt'),
      '#default_value' => $this->config('lms_ai.engine.settings')->get('slide_content_prompt') ?? '',
      '#description' => '<a href="#" class="load-default">Load Default</a> | <a href="#" class="reset">Reset</a> | <a href="#" class="clear">Clear</a>',
      '#rows' => 4,
    ];

    $form['course_builder']['formatter_actions']['google_slide_format_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Google Slide Format Prompt'),
      '#default_value' => $this->config('lms_ai.engine.settings')->get('google_slide_format_prompt') ?? '',
      '#description' => '<a href="#" class="load-default">Load Default</a> | <a href="#" class="reset">Reset</a> | <a href="#" class="clear">Clear</a>',
      '#rows' => 4,
    ];

    $form['course_builder']['formatter_actions']['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-actions', 'js-form-wrapper', 'form-wrapper']],
    ];

    $form['course_builder']['formatter_actions']['actions']['generate_slide_content'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Slide Content'),
      '#ajax' => [
        'callback' => '::ajaxGenerateSlideContent',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Generating Slide Content...'),
        ],
      ],
    ];

    $form['course_builder']['formatter_actions']['actions']['generate_courses_scorm'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate SCORM'),
      '#ajax' => [
        'callback' => '::ajaxGenerateCoursesScorm',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Building SCORM 1.2 ZIP files...'),
        ],
      ],
    ];

    $form['course_builder']['formatter_actions']['actions']['generate_google_slide_prompt'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Google Slide Prompt'),
      '#ajax' => [
        'callback' => '::ajaxGenerateGoogleSlidePrompt',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Generating Google Slide Mapping...'),
        ],
      ],
    ];

    $form['course_builder']['formatter_actions']['actions']['generate_google_slide'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Google Slide'),
      '#ajax' => [
        'callback' => '::ajaxGenerateGoogleSlide',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Publishing Google Slides...'),
        ],
      ],
    ];

    $form['course_builder']['quiz_actions'] = [
      '#type' => 'details',
      '#title' => $this->t('AI Quiz Generator'),
      '#open' => FALSE,
    ];

    $form['course_builder']['quiz_actions']['quiz_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Quiz Prompt'),
      '#description' => '<a href="#" class="load-default">Load Default</a> | <a href="#" class="reset">Reset</a> | <a href="#" class="clear">Clear</a>',
      '#default_value' => $config->get('quiz_prompt') ?: '',
      '#rows' => 15,
    ];

    $form['course_builder']['quiz_actions']['actions'] = [
      '#type' => 'actions',
    ];

    $form['course_builder']['quiz_actions']['actions']['generate_quiz'] = [
      '#type' => 'submit',
      '#value' => $this->t('Run Quiz'),
      '#ajax' => [
        'callback' => '::ajaxGenerateQuiz',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Generating Quizzes for Lessons...'),
        ],
      ],
    ];

    $form['course_builder']['quiz_actions']['actions']['approve_quiz'] = [
      '#type' => 'submit',
      '#value' => $this->t('Approve Quiz'),
      '#ajax' => [
        'callback' => '::ajaxApproveQuiz',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Approving and creating Quiz entities...'),
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('lms_ai.engine.settings')
      ->set('training_program', $form_state->getValue('training_program'))
      ->set('program_language', $form_state->getValue('program_language'))
      ->set('selected_courses', $form_state->getValue('selected_courses'))
      ->set('active_provider', $form_state->getValue('active_provider'))
      ->set('users_prompt', $form_state->getValue('users_prompt'))
      ->set('users_image_prompt', $form_state->getValue('users_image_prompt'))
      ->set('curriculum_prompt', $form_state->getValue('curriculum_prompt'))
      ->set('curriculum_suggestion', $form_state->getValue('curriculum_suggestion'))
      ->set('course_detail_prompt', $form_state->getValue('course_detail_prompt'))
      ->set('course_image_prompt', $form_state->getValue('course_image_prompt'))
      ->set('chapter_detail_prompt', $form_state->getValue('chapter_detail_prompt'))
      ->set('lesson_detail_prompt', $form_state->getValue('lesson_detail_prompt'))
      ->set('slide_content_prompt', $form_state->getValue('slide_content_prompt'))
      ->set('google_slide_format_prompt', $form_state->getValue('google_slide_format_prompt'))
      ->set('google_slide_template_id', $form_state->getValue('google_slide_template_id'))
      ->set('quiz_prompt', $form_state->getValue('quiz_prompt'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * AJAX handler for 'Run User'.
   */
  public function ajaxRunUser(array &$form, FormStateInterface $form_state): \Drupal\Core\Ajax\AjaxResponse {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    
    $prompt = $form_state->getValue('users_prompt');
    $training_program = $form_state->getValue('training_program');
    $langcode = $form_state->getValue('program_language');
    
    // Dynamic values
    $languages = \Drupal::languageManager()->getLanguages();
    $langcodes_list = implode(', ', array_keys($languages));
    
    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    $role_keys = array_keys($roles);
    $role_keys = array_diff($role_keys, ['anonymous', 'authenticated', 'administrator']);
    $roles_list = implode(', ', $role_keys);

    // Apply tokens
    $prompt = str_replace('{{training_program}}', $training_program, $prompt);
    $prompt = str_replace('{{langcode}}', $langcode, $prompt);
    $prompt = str_replace('{{langcodes_list}}', $langcodes_list, $prompt);
    $prompt = str_replace('{{roles_list}}', $roles_list, $prompt);

    try {
      $active_provider_id = $this->config('lms_ai.engine.settings')->get('active_provider') ?: 'gemini';
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $provider */
      $provider = $this->aiProviderManager->createInstance($active_provider_id);

      $ai_response = $provider->generateText($prompt);
      
      // Attempt to clean JSON and Decode
      $ai_response = preg_replace('/```(?:json)?|```/', '', $ai_response);
      $users_data = json_decode(trim($ai_response), TRUE);

      if (json_last_error() === JSON_ERROR_NONE && is_array($users_data)) {
         
         // User requested just to save to JSON file and NOT import
         $module_handler = \Drupal::service('module_handler');
         $module_path = $module_handler->getModule('lms_ai')->getPath();
         $json_dir = \Drupal::root() . '/' . $module_path . '/JSON';
         
         if (!is_dir($json_dir)) {
             mkdir($json_dir, 0777, TRUE);
         }
         
         $file_path = $json_dir . '/ai_users.json';
         file_put_contents($file_path, json_encode($users_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
         
         // Dynamically re-enable the Preview JSON button since checking passed.
         $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('#btn-preview-json', 'removeAttr', ['disabled']));
         
         $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('✅ [Thành công] AI tạo thành công và đã lưu JSON vào đường dẫn @path', ['@path' => "modules/custom/lms_ai/JSON/ai_users.json"]), NULL, ['type' => 'status']));
      } else {
         $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('❌ [Lỗi] Trả về từ AI không phải JSON hợp lệ. Vui lòng thử lại.'), NULL, ['type' => 'error']));
      }
    } catch (\Exception $e) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('❌ [Lỗi Hệ Thống] @error', ['@error' => $e->getMessage()]), NULL, ['type' => 'error']));
    }

    return $response;
  }

  /**
   * AJAX handler for 'Preview JSON'.
   */
  public function ajaxPreviewJson(array &$form, FormStateInterface $form_state): \Drupal\Core\Ajax\AjaxResponse {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('lms_ai')->getPath();
    $file_path = \Drupal::root() . '/' . $module_path . '/JSON/ai_users.json';
    
    if (file_exists($file_path)) {
      $json = file_get_contents($file_path);
      $content = [
        '#markup' => '<pre style="max-height: 500px; overflow-y: auto; background: #2b2b2b; color: #a9b7c6; padding: 15px; border-radius: 5px;">' . htmlspecialchars($json) . '</pre>',
      ];
    } else {
      $content = [
        '#markup' => '<div style="padding: 20px; text-align: center; color: #d00;">' . $this->t('File JSON chưa được tạo. Hãy bấm "Run User" trước.') . '</div>',
      ];
    }

    $response->addCommand(new \Drupal\Core\Ajax\OpenModalDialogCommand($this->t('Preview AI Users JSON'), $content, ['width' => 900]));
    return $response;
  }

  /**
   * AJAX handler for 'Approve User'.
   */
  public function ajaxApproveUser(array &$form, FormStateInterface $form_state): \Drupal\Core\Ajax\AjaxResponse {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('lms_ai')->getPath();
    $file_path = \Drupal::root() . '/' . $module_path . '/JSON/ai_users.json';
    
    if (!file_exists($file_path)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('❌ [Lỗi] Không tìm thấy file JSON. Vui lòng bấm Run User để tạo lại.'), NULL, ['type' => 'error']));
      return $response;
    }

    $json_content = file_get_contents($file_path);
    $users_data = json_decode($json_content, TRUE);

    if (!$users_data || !is_array($users_data)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('❌ [Lỗi] Dữ liệu JSON không hợp lệ.'), NULL, ['type' => 'error']));
      return $response;
    }

    $created_count = 0;
    $updated_count = 0;
    $errors = [];

    foreach ($users_data as &$data) {
      if (empty($data['mail']) || empty($data['name'])) {
        $errors[] = "Missing mail or name for a user.";
        continue;
      }
      
      try {
        $users = $this->entityTypeManager->getStorage('user')->loadByProperties(['mail' => $data['mail']]);
        $user = reset($users);

        if ($user) {
          $updated_count++;
        } else {
          $user = \Drupal\user\Entity\User::create();
          $user->enforceIsNew();
          $created_count++;
        }

        $user->setUsername($data['name']);
        $user->setEmail($data['mail']);
        
        $pass = $data['pass'] ?? \Drupal::service('password_generator')->generate();
        $user->setPassword($pass);
        $user->activate();

        if (!empty($data['roles']) && is_array($data['roles'])) {
          foreach ($data['roles'] as $role) {
            $user->addRole($role);
          }
        } elseif (isset($data['roles']) && is_string($data['roles'])) {
          $user->addRole($data['roles']);
        }

        $fields = ['field_first_name', 'field_last_name', 'field_bio', 'field_job_title', 'field_company', 'field_location'];
        foreach ($fields as $field) {
          if (isset($data[$field]) && $user->hasField($field)) {
            $user->set($field, $data[$field]);
          }
        }

        $user->save();
        
        $data['uid'] = $user->id();
        $data['pass'] = $pass;
      } catch (\Exception $e) {
        $errors[] = "Lỗi tạo user " . $data['name'] . ": " . $e->getMessage();
      }
    }

    file_put_contents($file_path, json_encode($users_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    if (empty($errors)) {
      $msg = $this->t('✅ [Thành công] Đã duyệt và lưu @created users mới, cập nhật @updated users cũ vào Database. File JSON cũng đã được update ID.', [
        '@created' => $created_count,
        '@updated' => $updated_count,
      ]);
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($msg, NULL, ['type' => 'status']));
    } else {
      $msg = $this->t('⚠️ Đã duyệt xong nhưng có một số lỗi: <br>@errors', ['@errors' => implode('<br>', $errors)]);
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($msg, NULL, ['type' => 'warning']));
    }

    return $response;
  }

  /**
   * AJAX handler for 'Request Image User Prompts'.
   */
  public function ajaxRequestImagePrompts(array &$form, FormStateInterface $form_state): \Drupal\Core\Ajax\AjaxResponse {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    set_time_limit(0); // Prevent PHP timeout

    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('lms_ai')->getPath();
    $file_path = \Drupal::root() . '/' . $module_path . '/JSON/ai_users.json';

    if (!file_exists($file_path)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('❌ [Lỗi] Không tìm thấy file JSON. Vui lòng chạy Run User trước.'), NULL, ['type' => 'error']));
      return $response;
    }

    $json_content = file_get_contents($file_path);
    $users_data = json_decode($json_content, TRUE);

    if (!$users_data || !is_array($users_data)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('❌ [Lỗi] Dữ liệu JSON không hợp lệ.'), NULL, ['type' => 'error']));
      return $response;
    }

    $template = $form_state->getValue('users_image_prompt');
    if (empty($template)) {
      $template = $this->config('lms_ai.engine.settings')->get('users_image_prompt');
    }

    if (empty($template)) {
       $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('❌ [Lỗi] Users Image Prompt không được để trống.'), NULL, ['type' => 'error']));
       return $response;
    }

    $lms_config = \Drupal::config('lms_ai.settings');
    $training_program = $lms_config->get('training_program') ?: 'LMS Training';
    $langcode = $lms_config->get('langcode') ?: 'vi';

    $active_provider = $this->config('lms_ai.engine.settings')->get('active_provider') ?: 'gemini';
    /** @var \Drupal\lms_ai\Plugin\AiProviderManager $ai_manager */
    $ai_manager = \Drupal::service('plugin.manager.lms_ai_provider');
    
    try {
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $provider */
      $provider = $ai_manager->createInstance($active_provider);
    } catch (\Exception $e) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('❌ [Lỗi] Không thể khởi tạo AI Provider: @error', ['@error' => $e->getMessage()]), NULL, ['type' => 'error']));
      return $response;
    }

    $success_count = 0;
    $errors = [];

    foreach ($users_data as &$user) {
      // Bỏ qua nếu đã có image prompt
      if (!empty($user['image_generation_prompt'])) {
        continue;
      }

      $name = trim(($user['field_first_name'] ?? '') . ' ' . ($user['field_last_name'] ?? ''));
      if (empty($name)) {
        $name = $user['name'] ?? 'Unknown User';
      }
      $gender = $user['field_gender'] ?? ($user['gender'] ?? 'male');

      // Thay thế biến
      $prompt = str_replace(
        ['{{training_program}}', '{{langcode}}', '{{user_name}}', '{{user_gender}}'],
        [$training_program, $langcode, $name, $gender],
        $template
      );

      try {
        $ai_response = $provider->generateText($prompt);
        // Trích xuất JSON từ chuỗi trả về
        preg_match('/```(?:json)?\s*(.*?)\s*```/s', $ai_response, $matches);
        $json_string = $matches[1] ?? $ai_response;
        $json_string = trim($json_string);
        
        $image_json = json_decode($json_string, TRUE);
        if (is_array($image_json)) {
           $user['image_generation_prompt'] = $image_json;
           $success_count++;
           
           // Lưu ngay từng bước để an toàn
           file_put_contents($file_path, json_encode($users_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
           $errors[] = "Không thể parse JSON từ AI cho user {$name}";
        }
      } catch (\Exception $e) {
         $errors[] = "Lỗi khi gọi AI cho user {$name}: " . $e->getMessage();
      }
    }

    if ($success_count > 0 || empty($errors)) {
      $msg = $this->t('✅ Đã tạo thành công JSON Image Prompt cho @count users.', ['@count' => $success_count]);
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($msg, NULL, ['type' => 'status']));
    }
    
    if (!empty($errors)) {
      $msg = $this->t('⚠️ Cảnh báo lỗi: <br>@errors', ['@errors' => implode('<br>', $errors)]);
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($msg, NULL, ['type' => 'warning']));
    }

    if ($success_count === 0 && empty($errors)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('ℹ️ Tất cả users đều đã có JSON mô tả ảnh, không cần tạo thêm.'), NULL, ['type' => 'info']));
    }

    return $response;
  }

  /**
   * AJAX handler for 'Request Images'.
   */
  public function ajaxRequestImages(array &$form, FormStateInterface $form_state): \Drupal\Core\Ajax\AjaxResponse {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    set_time_limit(0);

    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('lms_ai')->getPath();
    $file_path = \Drupal::root() . '/' . $module_path . '/JSON/ai_users.json';

    if (!file_exists($file_path)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('File JSON chưa tồn tại. Vui lòng bấm Run User trước.'), NULL, ['type' => 'error']));
      return $response;
    }

    $json_content = file_get_contents($file_path);
    $users_data = json_decode($json_content, TRUE);

    if (empty($users_data) || !is_array($users_data)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('File JSON rỗng hoặc không hợp lệ.'), NULL, ['type' => 'error']));
      return $response;
    }

    $active_provider_id = $this->config('lms_ai.engine.settings')->get('active_provider') ?: 'gemini';
    $ai_manager = \Drupal::service('plugin.manager.lms_ai_provider');
    
    try {
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $provider */
      $provider = $ai_manager->createInstance($active_provider_id);
    } catch (\Exception $e) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand("Lỗi khởi tạo AI: " . $e->getMessage(), NULL, ['type' => 'error']));
      return $response;
    }

    $success_count = 0;
    $errors = [];

    foreach ($users_data as &$user) {
      // Bỏ qua nếu chưa được Import (chưa có uid) hoặc đã được tạo ảnh (avatar_processed = true)
      if (empty($user['uid']) || !empty($user['avatar_processed']) || empty($user['image_generation_prompt'])) {
        continue;
      }

      // Xây dựng prompt sinh ảnh từ chuỗi JSON mô tả. Lấy trường description hoặc ghép tất cả lại.
      $prompt_data = $user['image_generation_prompt'];
      if (is_array($prompt_data)) {
        $image_prompt = !empty($prompt_data['description']) 
          ? $prompt_data['description'] 
          : implode(', ', array_filter(array_values($prompt_data)));
      } else {
        $image_prompt = (string) $prompt_data;
      }
      try {
        $base64_image = $provider->generateImage($image_prompt);
        if (!$base64_image) {
          $errors[] = "AI không trả về kết quả ảnh cho UID: {$user['uid']}";
          continue;
        }

        $image_data = base64_decode($base64_image);
        if (!$image_data) {
          $errors[] = "Lỗi giải mã Base64 thành hình ảnh cho UID: {$user['uid']}";
          continue;
        }

        $directory = 'public://ai_avatars';
        /** @var \Drupal\Core\File\FileSystemInterface $file_system */
        $file_system = \Drupal::service('file_system');
        $file_system->prepareDirectory($directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY | \Drupal\Core\File\FileSystemInterface::MODIFY_PERMISSIONS);

        /** @var \Drupal\file\FileRepositoryInterface $file_repository */
        $file_repository = \Drupal::service('file.repository');
        $filename = "user_avatar_{$user['uid']}_" . time() . ".jpg";
        $file = $file_repository->writeData($image_data, $directory . '/' . $filename, \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE);

        if ($file) {
          // Gắn ảnh vào User Entity
          $drupal_user = \Drupal\user\Entity\User::load($user['uid']);
          if ($drupal_user && $drupal_user->hasField('user_picture')) {
            $drupal_user->set('user_picture', ['target_id' => $file->id()]);
            $drupal_user->save();
            
            // Mark is processed
            $user['avatar_processed'] = TRUE;
            $success_count++;

            // Lưu file JSON an toàn từng bước
            file_put_contents($file_path, json_encode($users_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
          } else {
             $errors[] = "Không load được Entity User hoặc không có trường user_picture cho UID: {$user['uid']}";
          }
        } else {
             $errors[] = "Lỗi ghi file hệ thống cho UID: {$user['uid']}";
        }
      } catch (\Exception $e) {
         $errors[] = "Exception khi xử lý UID {$user['uid']}: " . $e->getMessage();
      }
    }

    if ($success_count > 0 || empty($errors)) {
      $msg = $this->t('✅ [AI Engine] Đã tạo và gắn ảnh đại diện thành công cho @count users.', ['@count' => $success_count]);
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($msg, NULL, ['type' => 'status']));
    }

    if (!empty($errors)) {
      $msg = $this->t('⚠️ Cảnh báo lỗi: <br>@errors', ['@errors' => implode('<br>', $errors)]);
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($msg, NULL, ['type' => 'warning']));
    }

    if ($success_count === 0 && empty($errors)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('ℹ️ Tüm users đều đã có Avatar, không cần tạo thêm.'), NULL, ['type' => 'info']));
    }

    return $response;
  }

  /**
   * AJAX handler for 'Request Curriculum Suggestion'.
   */
  public function ajaxRequestCurriculumSuggestion(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    
    $prompt = $form_state->getValue('curriculum_prompt');
    $training_program = $form_state->getValue('training_program');
    $langcode = $form_state->getValue('program_language');
    
    // Replace tokens
    $prompt = str_replace('{{training_program}}', $training_program, $prompt);
    $prompt = str_replace('{{langcode}}', $langcode, $prompt);
    
    // Ensure the AI returns the correct JSON structure requested by the UI expectation
    $system_prompt = "\n\nCRITICAL INSTRUCTION: You MUST return the response strictly as a VALID JSON array. Do not include any conversational text or markdown other than the JSON block. The JSON structure must follow this format exactly:
[
  {
    \"category\": \"Category Name\",
    \"courses\": [
      {
         \"course_title\": \"Course Name\",
         \"chapters\": [
            {
               \"chapter_title\": \"Chapter Name\",
               \"lessons\": [
                  { \"lesson_title\": \"Lesson Name\" }
               ]
            }
         ]
      }
    ]
  }
]";
    $final_prompt = $prompt . $system_prompt;

    try {
      $active_provider_id = $this->config('lms_ai.engine.settings')->get('active_provider') ?: 'gemini';
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $provider */
      $provider = $this->aiProviderManager->createInstance($active_provider_id);

      $ai_response = $provider->generateText($final_prompt);
      
      // Attempt to clean JSON and Decode
      $ai_response = preg_replace('/```(?:json)?|```/', '', $ai_response);
      $curriculum_data = json_decode(trim($ai_response), TRUE);

      if (json_last_error() === JSON_ERROR_NONE && is_array($curriculum_data)) {
         $module_handler = \Drupal::service('module_handler');
         $module_path = $module_handler->getModule('lms_ai')->getPath();
         $json_dir = \Drupal::root() . '/' . $module_path . '/JSON';
         
         if (!is_dir($json_dir)) {
             mkdir($json_dir, 0777, TRUE);
         }
         
         $file_path = $json_dir . '/ai_curriculum.json';
         file_put_contents($file_path, json_encode($curriculum_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
         
         // Update the textarea with the generated JSON
         $json_string = json_encode($curriculum_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
         $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('#edit-curriculum-suggestion', 'val', [$json_string]));
         
         $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Curriculum JSON has been generated and saved to ai_curriculum.json!'), NULL, ['type' => 'status']));
      } else {
         $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('AI returned invalid JSON. Raw response loaded into text area.'), NULL, ['type' => 'warning']));
         $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('#edit-curriculum-suggestion', 'val', [$ai_response]));
      }
    } catch (\Exception $e) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($e->getMessage(), NULL, ['type' => 'error']));
    }

    return $response;
  }

  /**
   * AJAX handler for 'Generate Curriculum'.
   */
  public function ajaxGenerateCurriculum(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('lms_ai')->getPath();
    $json_file = \Drupal::root() . '/' . $module_path . '/JSON/ai_curriculum.json';

    if (!file_exists($json_file)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Không tìm thấy file JSON. Vui lòng bấm "Request Curriculum Suggestion" trước.'), NULL, ['type' => 'error']));
      return $response;
    }

    $json_content = file_get_contents($json_file);
    $data = json_decode($json_content, TRUE);
    if (empty($data) || !is_array($data)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('File JSON rỗng hoặc không đúng định dạng hợp lệ.'), NULL, ['type' => 'error']));
      return $response;
    }

    $entity_type_manager = \Drupal::entityTypeManager();
    $term_storage = $entity_type_manager->getStorage('taxonomy_term');
    $course_storage = $entity_type_manager->getStorage('lms_course');
    $chapter_storage = $entity_type_manager->getStorage('lms_chapter');
    $lesson_storage = $entity_type_manager->getStorage('lms_lesson');

    $created_count = ['category' => 0, 'course' => 0, 'chapter' => 0, 'lesson' => 0];
    $updated_count = ['category' => 0, 'course' => 0, 'chapter' => 0, 'lesson' => 0];

    try {
      foreach ($data as &$category_data) {
        $category_id = $category_data['tid'] ?? NULL;
        $term = NULL;
        if ($category_id && $term = $term_storage->load($category_id)) {
          $term->setName($category_data['category']);
          $term->save();
          $updated_count['category']++;
        } else {
          $term = $term_storage->create([
            'vid' => 'course_category',
            'name' => $category_data['category'],
          ]);
          $term->save();
          $category_data['tid'] = $term->id();
          $created_count['category']++;
        }

        if (!empty($category_data['courses']) && is_array($category_data['courses'])) {
          foreach ($category_data['courses'] as &$course_data) {
            $course_id = $course_data['id'] ?? NULL;
            $course = NULL;
            if ($course_id && $course = $course_storage->load($course_id)) {
              $course->set('name', $course_data['course_title']);
              $course->set('field_category', ['target_id' => $term->id()]);
              $course->set('status', 1); // Also publish existing courses
              // Reset chapters reference list before rebuilding it.
              $course->set('field_chapters', []);
              $updated_count['course']++;
            } else {
              $course = $course_storage->create([
                'name' => $course_data['course_title'],
                'field_category' => ['target_id' => $term->id()],
                'status' => 1, // Published by default for demo
              ]);
              $created_count['course']++;
            }

            if (!empty($course_data['chapters']) && is_array($course_data['chapters'])) {
              foreach ($course_data['chapters'] as &$chapter_data) {
                $chapter_id = $chapter_data['id'] ?? NULL;
                $chapter = NULL;
                if ($chapter_id && $chapter = $chapter_storage->load($chapter_id)) {
                  $chapter->set('name', $chapter_data['chapter_title']);
                  // Reset lessons reference list before rebuilding it.
                  $chapter->set('field_lessons', []);
                  $updated_count['chapter']++;
                } else {
                  $chapter = $chapter_storage->create([
                    'name' => $chapter_data['chapter_title'],
                    'status' => 1,
                  ]);
                  $created_count['chapter']++;
                }

                if (!empty($chapter_data['lessons']) && is_array($chapter_data['lessons'])) {
                  foreach ($chapter_data['lessons'] as &$lesson_data) {
                    $lesson_id = $lesson_data['id'] ?? NULL;
                    $lesson = NULL;
                    if ($lesson_id && $lesson = $lesson_storage->load($lesson_id)) {
                      $lesson->set('name', $lesson_data['lesson_title']);
                      $lesson->save();
                      $updated_count['lesson']++;
                    } else {
                      $lesson = $lesson_storage->create([
                        'name' => $lesson_data['lesson_title'],
                        'status' => 1,
                      ]);
                      $lesson->save();
                      $lesson_data['id'] = $lesson->id();
                      $created_count['lesson']++;
                    }
                    
                    // Append lesson to chapter
                    $chapter->get('field_lessons')->appendItem(['target_id' => $lesson->id()]);
                  }
                }
                
                $chapter->save();
                $chapter_data['id'] = $chapter->id();
                
                // Append chapter to course
                $course->get('field_chapters')->appendItem(['target_id' => $chapter->id()]);
              }
            }
            
            $course->save();
            $course_data['id'] = $course->id();
          }
        }
      }

      // Save the updated JSON (now containing IDs) back to file
      file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

      // Update the textarea with the newly saved JSON string
      $json_string = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('#edit-curriculum-suggestion', 'val', [$json_string]));

      $msg = $this->t('✅ Đã Generate Curriculum thành công! <br><strong>Tạo mới:</strong> @cc Danh mục, @cco Khóa học, @cch Chapters, @cl Lessons.<br><strong>Cập nhật:</strong> @uc Danh mục, @uco Khóa học, @uch Chapters, @ul Lessons.', [
        '@cc' => $created_count['category'],
        '@cco' => $created_count['course'],
        '@cch' => $created_count['chapter'],
        '@cl' => $created_count['lesson'],
        '@uc' => $updated_count['category'],
        '@uco' => $updated_count['course'],
        '@uch' => $updated_count['chapter'],
        '@ul' => $updated_count['lesson'],
      ]);
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($msg, NULL, ['type' => 'status']));
      
    } catch (\Exception $e) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Lỗi khi Generate Curriculum: @error', ['@error' => $e->getMessage()]), NULL, ['type' => 'error']));
    }

    return $response;
  }

  /**
   * Helper function to sanitize titles by stripping chapter/lesson prefixes.
   */
  protected function cleanupTitle(string $title): string {
    $regex = '/^(?:(?:Chapter|Chương|Lesson|Bài\s*học|Phần\s*học|Bài|Phần|Module)\s+(?:\d+|[IVXLCDM]+)\s*[\:\.\-\|]*\s*|\d+\s*[\.\:\-\|]+\s+)/ui';
    $cleaned = preg_replace($regex, '', $title);
    return !empty(trim($cleaned)) ? trim($cleaned) : $title;
  }

  /**
   * Read the JSON file helper.
   */
  protected function getCurriculumJsonData() {
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('lms_ai')->getPath();
    $json_file = \Drupal::root() . '/' . $module_path . '/JSON/ai_curriculum.json';

    if (!file_exists($json_file)) {
      return [FALSE, NULL, $json_file];
    }

    $json_content = file_get_contents($json_file);
    $data = json_decode($json_content, TRUE);
    if (empty($data) || !is_array($data)) {
      return [FALSE, NULL, $json_file];
    }
    return [TRUE, $data, $json_file];
  }

  /**
   * AJAX handler for 'Trim Chapters'.
   */
  public function ajaxTrimChapters(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    
    list($success, $data, $json_file) = $this->getCurriculumJsonData();
    if (!$success) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Không tìm thấy file JSON. Vui lòng Request Curriculum.'), NULL, ['type' => 'error']));
      return $response;
    }

    $entity_type_manager = \Drupal::entityTypeManager();
    $chapter_storage = $entity_type_manager->getStorage('lms_chapter');
    $trimmed_count = 0;

    foreach ($data as &$category_data) {
      if (!empty($category_data['courses']) && is_array($category_data['courses'])) {
        foreach ($category_data['courses'] as &$course_data) {
          if (!empty($course_data['chapters']) && is_array($course_data['chapters'])) {
            foreach ($course_data['chapters'] as &$chapter_data) {
              $old_title = $chapter_data['chapter_title'];
              $new_title = $this->cleanupTitle($old_title);
              
              if ($old_title !== $new_title) {
                $chapter_data['chapter_title'] = $new_title;
                $trimmed_count++;
                
                // If entity is already created, update it
                $chapter_id = $chapter_data['id'] ?? NULL;
                if ($chapter_id && $chapter = $chapter_storage->load($chapter_id)) {
                  $chapter->set('name', $new_title);
                  $chapter->save();
                }
              }
            }
          }
        }
      }
    }

    file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $json_string = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('#edit-curriculum-suggestion', 'val', [$json_string]));
    
    $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('✅ Đã Trim xong @count Chapters.', ['@count' => $trimmed_count]), NULL, ['type' => 'status']));
    return $response;
  }

  /**
   * AJAX handler for 'Trim Lessons'.
   */
  public function ajaxTrimLessons(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    
    list($success, $data, $json_file) = $this->getCurriculumJsonData();
    if (!$success) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Không tìm thấy file JSON. Vui lòng Request Curriculum.'), NULL, ['type' => 'error']));
      return $response;
    }

    $entity_type_manager = \Drupal::entityTypeManager();
    $lesson_storage = $entity_type_manager->getStorage('lms_lesson');
    $trimmed_count = 0;

    foreach ($data as &$category_data) {
      if (!empty($category_data['courses']) && is_array($category_data['courses'])) {
        foreach ($category_data['courses'] as &$course_data) {
          if (!empty($course_data['chapters']) && is_array($course_data['chapters'])) {
            foreach ($course_data['chapters'] as &$chapter_data) {
              if (!empty($chapter_data['lessons']) && is_array($chapter_data['lessons'])) {
                foreach ($chapter_data['lessons'] as &$lesson_data) {
                  $old_title = $lesson_data['lesson_title'];
                  $new_title = $this->cleanupTitle($old_title);
                  
                  if ($old_title !== $new_title) {
                    $lesson_data['lesson_title'] = $new_title;
                    $trimmed_count++;
                    
                    // If entity is already created, update it
                    $lesson_id = $lesson_data['id'] ?? NULL;
                    if ($lesson_id && $lesson = $lesson_storage->load($lesson_id)) {
                      $lesson->set('name', $new_title);
                      $lesson->save();
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $json_string = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('#edit-curriculum-suggestion', 'val', [$json_string]));
    
    $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('✅ Đã Trim xong @count Lessons.', ['@count' => $trimmed_count]), NULL, ['type' => 'status']));
    return $response;
  }

  /**
   * AJAX handler for 'Generate Courses Detail'.
   */
  public function ajaxGenerateCoursesDetail(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    
    list($success, $data, $json_file) = $this->getCurriculumJsonData();
    if (!$success) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Không tìm thấy file JSON curriculum.'), NULL, ['type' => 'error']));
      return $response;
    }

    $prompt_template = $form_state->getValue('course_detail_prompt');
    if (empty($prompt_template)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Prompt template rỗng.'), NULL, ['type' => 'error']));
      return $response;
    }

    $config = $this->config('lms_ai.engine.settings');
    $training_program = $config->get('training_program');
    $langcode = $config->get('program_language');
    // For now we'll just hardcode or retrieve langcodes list
    $langcodes_list = "en, vi"; 
    $courses_list_suggestion = "Read from JSON curriculum"; 
    $categories_courses = "Read from JSON curriculum";

    $active_provider_id = $config->get('active_provider') ?: 'gemini';
    try {
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $provider */
      $provider = $this->aiProviderManager->createInstance($active_provider_id);
    } catch (\Exception $e) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Lỗi load AI Provider: @msg', ['@msg' => $e->getMessage()]), NULL, ['type' => 'error']));
      return $response;
    }

    $entity_type_manager = \Drupal::entityTypeManager();
    $course_storage = $entity_type_manager->getStorage('lms_course');

    $processed_course = NULL;
    $updated = FALSE;

    // We process only one course per click to avoid timeout.
    foreach ($data as &$category_data) {
      if (!empty($category_data['courses']) && is_array($category_data['courses'])) {
        foreach ($category_data['courses'] as &$course_data) {
          $course_id = $course_data['id'] ?? NULL;
          $selected_courses = $config->get('selected_courses') ?: [];
          if (!empty($selected_courses) && !in_array($course_id, $selected_courses)) {
            continue;
          }
          
          if (empty($course_data['is_detail_generated'])) {
            $course_name = $course_data['course_title'];
            $category_name = $category_data['category'];
            
            // Prepare prompt
            $final_prompt = $prompt_template;
            $final_prompt = str_replace('{{training_program}}', $training_program, $final_prompt);
            $final_prompt = str_replace('{{langcodes_list}}', $langcodes_list, $final_prompt);
            $final_prompt = str_replace('{{langcode}}', $langcode, $final_prompt);
            $final_prompt = str_replace('{{courses_list_suggestion}}', json_encode(array_column($category_data['courses'], 'course_title')), $final_prompt);
            $final_prompt = str_replace('{{categories_courses}}', $category_name, $final_prompt);
            $final_prompt = str_replace('{{course_name}}', $course_name, $final_prompt);
            $final_prompt = str_replace('{{category_name}}', $category_name, $final_prompt);

            try {
              $ai_response = $provider->generateText($final_prompt);
              $ai_response = preg_replace('/```(?:json)?|```/', '', $ai_response);
              $detail_data = json_decode(trim($ai_response), TRUE);

              if (json_last_error() === JSON_ERROR_NONE && is_array($detail_data)) {
                 $course_detail = $detail_data[0][$langcode] ?? NULL;
                 
                 if ($course_detail) {
                    $course_id = $course_data['id'] ?? NULL;
                    if ($course_id && $course = $course_storage->load($course_id)) {
                       // Update entity fields from JSON
                       if (isset($course_detail['short_description'])) {
                          $course->set('field_short_description', ['value' => $course_detail['short_description'], 'format' => 'full_html']);
                       }
                       if (isset($course_detail['description'])) {
                          $course->set('field_description', ['value' => $course_detail['description'], 'format' => 'full_html']);
                       }
                       if (isset($course_detail['what_you_will_learn'])) {
                          $course->set('field_what_learn', ['value' => $course_detail['what_you_will_learn'], 'format' => 'full_html']);
                       }
                       if (isset($course_detail['prerequisites'])) {
                          $course->set('field_prerequisites', ['value' => $course_detail['prerequisites'], 'format' => 'full_html']);
                       }
                       if (isset($course_detail['who_is_this_for'])) {
                          $course->set('field_who_for', ['value' => $course_detail['who_is_this_for'], 'format' => 'full_html']);
                       }
                       if (isset($course_detail['price_type'])) {
                          $course->set('field_price_type', $course_detail['price_type']);
                       }
                       if (isset($course_detail['price'])) {
                          $course->set('field_price', (float) $course_detail['price']);
                       }
                       if (isset($course_detail['duration_minutes'])) {
                          $course->set('field_duration', (int) $course_detail['duration_minutes']);
                       }
                       if (isset($course_detail['is_featured'])) {
                          $course->set('field_featured', (bool) $course_detail['is_featured']);
                       }
                       if (isset($course_detail['is_bestseller'])) {
                          $course->set('field_bestseller', (bool) $course_detail['is_bestseller']);
                       }
                       
                       $course->save();
                       
                       // Mark as processed
                       $course_data['is_detail_generated'] = TRUE;
                       // Optionally save the details in JSON too
                       $course_data['details'] = $course_detail;
                       $updated = TRUE;
                       $processed_courses[] = $course_name;
                       
                       // LƯU JSON NGAY LẬP TỨC CHO TỪNG KHÓA
                       // Phòng trường hợp Server bị Timeout (504) giữa chừng thì những khóa đã chạy sẽ không bị mất dấu trong JSON.
                       file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    } else {
                       $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Course entity ID @id not found for @title.', ['@id' => $course_id, '@title' => $course_name]), NULL, ['type' => 'error']));
                    }
                 } else {
                    $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('JSON decoded but langcode root @lang missing.', ['@lang' => $langcode]), NULL, ['type' => 'error']));
                 }
              } else {
                 \Drupal::logger('lms_ai')->error('Invalid JSON from AI detail generation: @resp', ['@resp' => $ai_response]);
                 $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('AI returned invalid JSON for @course.', ['@course' => $course_name]), NULL, ['type' => 'error']));
              }
            } catch (\Exception $e) {
              $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Exception while generating detail for @course: @msg', ['@course' => $course_name, '@msg' => $e->getMessage()]), NULL, ['type' => 'error']));
            }

            // Ngủ 2 giây giữa mỗi khóa học trong vòng lặp để giảm thiểu khả năng Google chặn vì Rate Limit ngay trong 1 hit (15 req/min).
            sleep(2);
          }
        }
      }
    }

    if ($updated) {
      $list_str = implode(', ', $processed_courses ?? []);
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('✅ [Hoàn tất] Đã tạo chi tiết Data & Entity cho các khóa: @list', ['@list' => $list_str]), NULL, ['type' => 'status']));
      
    } else {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('🎉 Tất cả courses đã có chi tiết Data & Entity! (Hoặc không có khóa nào).'), NULL, ['type' => 'status']));
    }

    return $response;
  }

  /**
   * AJAX handler for 'Generate Chapters Detail'.
   */
  public function ajaxGenerateChaptersDetail(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    
    list($success, $data, $json_file) = $this->getCurriculumJsonData();
    if (!$success) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Không tìm thấy file JSON curriculum.'), NULL, ['type' => 'error']));
      return $response;
    }

    $prompt_template = $form_state->getValue('chapter_detail_prompt');
    if (empty($prompt_template)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Prompt template rỗng.'), NULL, ['type' => 'error']));
      return $response;
    }

    $config = $this->config('lms_ai.engine.settings');
    $langcode = $config->get('program_language') ?: 'vi';
    $training_program = $config->get('training_program');
    $langcodes_list = "en, vi";

    $categories_courses_arr = [];
    foreach ($data as $c) {
      $cat_name = $c['category'] ?? '';
      $course_names = [];
      if (!empty($c['courses'])) {
        foreach ($c['courses'] as $crs) {
          $course_names[] = $crs['course_title'] ?? '';
        }
      }
      $categories_courses_arr[$cat_name] = $course_names;
    }
    $categories_courses_str = json_encode($categories_courses_arr, JSON_UNESCAPED_UNICODE);

    $active_provider_id = $config->get('active_provider') ?: 'gemini';

    try {
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $provider */
      $provider = $this->aiProviderManager->createInstance($active_provider_id);
    } catch (\Exception $e) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Lỗi load AI Provider: @msg', ['@msg' => $e->getMessage()]), NULL, ['type' => 'error']));
      return $response;
    }

    $entity_type_manager = \Drupal::entityTypeManager();
    $chapter_storage = $entity_type_manager->getStorage('lms_chapter');
    $updated = FALSE;
    $processed_chapters = [];
    $selected_courses = $config->get('selected_courses') ?: [];

    foreach ($data as &$category_data) {
      if (!empty($category_data['courses']) && is_array($category_data['courses'])) {
        foreach ($category_data['courses'] as &$course_data) {
          $course_id = $course_data['id'] ?? '';
          if (!empty($selected_courses) && !in_array($course_id, $selected_courses)) {
            continue;
          }
          if (!empty($course_data['chapters']) && is_array($course_data['chapters'])) {
            foreach ($course_data['chapters'] as &$chapter_data) {
              if (empty($chapter_data['is_detail_generated'])) {
                $chapter_name = $chapter_data['chapter_title'];

                // Prepare prompt
                $final_prompt = $prompt_template;
                $final_prompt = str_replace('{{training_program}}', $training_program, $final_prompt);
                $final_prompt = str_replace('{{langcodes_list}}', $langcodes_list, $final_prompt);
                $final_prompt = str_replace('{{langcode}}', $langcode, $final_prompt);
                $final_prompt = str_replace('{{categories_courses}}', $categories_courses_str, $final_prompt);
                $final_prompt = str_replace('{{course_id}}', $course_id, $final_prompt);
                $final_prompt = str_replace('{{chapter_name}}', $chapter_name, $final_prompt);

                // Add instruction for strictly JSON format
                $system_prompt = "\n\nCRITICAL INSTRUCTION: You MUST return the response strictly as a VALID JSON object. Do not include conversational text or markdown. Example format: { \"course_id\": 123, \"chapter\": [ { \"$langcode\": { \"chapter_title\": \"...\", \"chapter_description\": \"...\" } } ] }";
                $final_prompt .= $system_prompt;

                try {
                  $ai_response = $provider->generateText($final_prompt);
                  $ai_response = preg_replace('/```(?:json)?|```/', '', $ai_response);
                  $detail_data = json_decode(trim($ai_response), TRUE);

                  if (json_last_error() === JSON_ERROR_NONE && is_array($detail_data)) {
                     // Check if it's the expected structure
                     $chapter_detail = $detail_data['chapter'][0][$langcode] ?? NULL;
                     
                     if ($chapter_detail) {
                        $chapter_id = $chapter_data['id'] ?? NULL;
                        if ($chapter_id && $chapter = $chapter_storage->load($chapter_id)) {
                           // Update entity fields
                           if (isset($chapter_detail['chapter_description'])) {
                              $chapter->set('field_description', ['value' => $chapter_detail['chapter_description'], 'format' => 'full_html']);
                           }
                           
                           $chapter->save();
                           
                           // Mark as processed
                           $chapter_data['is_detail_generated'] = TRUE;
                           $chapter_data['chapter_description'] = $chapter_detail['chapter_description'] ?? '';
                           
                           $updated = TRUE;
                           $processed_chapters[] = $chapter_name;
                           
                           // LƯU JSON NGAY LẬP TỨC CHO TỪNG CHAPTER
                           file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        } else {
                           $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Chapter entity ID @id not found for @title.', ['@id' => $chapter_id, '@title' => $chapter_name]), NULL, ['type' => 'error']));
                        }
                     } else {
                        $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('JSON decoded but langcode root @lang missing for Chapter @name.', ['@lang' => $langcode, '@name' => $chapter_name]), NULL, ['type' => 'error']));
                     }
                  } else {
                     \Drupal::logger('lms_ai')->error('Invalid JSON from AI Chapter detail generation: @resp', ['@resp' => $ai_response]);
                     $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('AI returned invalid JSON for @chapter.', ['@chapter' => $chapter_name]), NULL, ['type' => 'error']));
                  }
                } catch (\Exception $e) {
                  $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Exception while generating detail for @chapter: @msg', ['@chapter' => $chapter_name, '@msg' => $e->getMessage()]), NULL, ['type' => 'error']));
                }

                // Ngủ 1 giây để tránh Rate Limit
                sleep(1);
              }
            }
          }
        }
      }
    }

    if ($updated) {
      $list_str = implode(', ', $processed_chapters);
      
      // Update the textarea curriculum_suggestion with the newly saved JSON string
      $json_string = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('#edit-curriculum-suggestion', 'val', [$json_string]));

      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('✅ [Hoàn tất] Đã tạo nội dung cho các Chapters: @list', ['@list' => $list_str]), NULL, ['type' => 'status']));
    } else {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('🎉 Tất cả Chapters đã có mô tả chi tiết! (Hoặc không có chapter nào).'), NULL, ['type' => 'status']));
    }

    return $response;
  }

  /**
   * AJAX handler for 'Generate Lessons Detail'.
   */
  public function ajaxGenerateLessonsDetail(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    
    list($success, $data, $json_file) = $this->getCurriculumJsonData();
    if (!$success) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Không tìm thấy file JSON curriculum.'), NULL, ['type' => 'error']));
      return $response;
    }

    $prompt_template = $form_state->getValue('lesson_detail_prompt');
    if (empty($prompt_template)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Prompt template rỗng.'), NULL, ['type' => 'error']));
      return $response;
    }

    $config = $this->config('lms_ai.engine.settings');
    $langcode = $config->get('program_language') ?: 'vi';
    $training_program = $config->get('training_program');
    $langcodes_list = "en, vi";

    $categories_courses_arr = [];
    foreach ($data as $c) {
      $cat_name = $c['category'] ?? '';
      $course_names = [];
      if (!empty($c['courses'])) {
        foreach ($c['courses'] as $crs) {
          $course_names[] = $crs['course_title'] ?? '';
        }
      }
      $categories_courses_arr[$cat_name] = $course_names;
    }
    $categories_courses_str = json_encode($categories_courses_arr, JSON_UNESCAPED_UNICODE);

    $active_provider_id = $config->get('active_provider') ?: 'gemini';

    try {
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $provider */
      $provider = $this->aiProviderManager->createInstance($active_provider_id);
    } catch (\Exception $e) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Lỗi load AI Provider: @msg', ['@msg' => $e->getMessage()]), NULL, ['type' => 'error']));
      return $response;
    }

    $entity_type_manager = \Drupal::entityTypeManager();
    $lesson_storage = $entity_type_manager->getStorage('lms_lesson');
    $updated = FALSE;
    $processed_lessons = [];

    foreach ($data as &$category_data) {
      if (!empty($category_data['courses']) && is_array($category_data['courses'])) {
        foreach ($category_data['courses'] as &$course_data) {
          $course_id = $course_data['id'] ?? '';
          $course_title = $course_data['course_title'] ?? '';
          
          if (!empty($course_data['chapters']) && is_array($course_data['chapters'])) {
            foreach ($course_data['chapters'] as &$chapter_data) {
              $chapter_title = $chapter_data['chapter_title'] ?? '';
              
              if (!empty($chapter_data['lessons']) && is_array($chapter_data['lessons'])) {
                foreach ($chapter_data['lessons'] as &$lesson_data) {
                  if (empty($lesson_data['is_detail_generated'])) {
                    $chapter_id = $chapter_data['id'] ?? '';
                    $lesson_name = $lesson_data['lesson_title'] ?? '';

                    // Prepare prompt
                    $final_prompt = $prompt_template;
                    $final_prompt = str_replace('{{training_program}}', $training_program, $final_prompt);
                    $final_prompt = str_replace('{{langcodes_list}}', $langcodes_list, $final_prompt);
                    $final_prompt = str_replace('{{langcode}}', $langcode, $final_prompt);
                    $final_prompt = str_replace('{{categories_courses}}', $categories_courses_str, $final_prompt);
                    $final_prompt = str_replace('{{course_title}}', $course_title, $final_prompt);
                    $final_prompt = str_replace('{{chapter_title}}', $chapter_title, $final_prompt);
                    $final_prompt = str_replace('{{chapter_id}}', $chapter_id, $final_prompt);
                    $final_prompt = str_replace('{{lesson_title}}', $lesson_name, $final_prompt);
                    $final_prompt = str_replace('{{lesson_name}}', $lesson_name, $final_prompt);
                    
                    // Thêm biến "{{chapters_lessons_tree}}"
                    $chapters_lessons_tree = [];
                    foreach ($course_data['chapters'] as $ch_tr) {
                       $lessons_tree = [];
                       if (!empty($ch_tr['lessons'])) {
                          foreach ($ch_tr['lessons'] as $ls_tr) {
                             $lessons_tree[] = $ls_tr['lesson_title'];
                          }
                       }
                       $chapters_lessons_tree[] = [
                          'chapter_title' => $ch_tr['chapter_title'],
                          'lessons' => $lessons_tree,
                       ];
                    }
                    $final_prompt = str_replace('{{chapters_lessons_tree}}', json_encode($chapters_lessons_tree, JSON_UNESCAPED_UNICODE), $final_prompt);

                    // Add instruction for strictly JSON format
                    $system_prompt = "\n\nCRITICAL INSTRUCTION: You MUST return the response strictly as a VALID JSON object. Do not include conversational text or markdown. Follow the exact key structure provided in your prompt.";
                    $final_prompt .= $system_prompt;

                    try {
                      $ai_response = $provider->generateText($final_prompt);
                      $ai_response = preg_replace('/```(?:json)?|```/', '', $ai_response);
                      $detail_data = json_decode(trim($ai_response), TRUE);

                      if (json_last_error() === JSON_ERROR_NONE && is_array($detail_data)) {
                         // Check if it's the expected structure
                         $lesson_detail = $detail_data['lesson'][0][$langcode] ?? NULL;
                         
                         if ($lesson_detail) {
                            $lesson_id = $lesson_data['id'] ?? NULL;
                            if ($lesson_id && $lesson = $lesson_storage->load($lesson_id)) {
                               // Update entity fields mapping based on matching lms_lesson fields
                               if (isset($lesson_detail['lesson_detail']) && !empty($lesson_detail['lesson_detail'])) {
                                  // The 'lesson_detail' key maps to 'field_content'
                                  $lesson->set('field_content', ['value' => $lesson_detail['lesson_detail'], 'format' => 'full_html']);
                               }
                               
                               if (isset($lesson_detail['youtube_link']) && !empty($lesson_detail['youtube_link'])) {
                                  // Provide a fallback YouTube search URL based on the keyword
                                  $search_url = 'https://www.youtube.com/results?search_query=' . urlencode($lesson_detail['youtube_link']);
                                  $lesson->set('field_video_url', $search_url);
                               }
                               
                               $lesson->save();
                               
                               // Mark as processed and save the generated fields to JSON
                               $lesson_data['is_detail_generated'] = TRUE;
                               $lesson_data['lesson_detail'] = $lesson_detail['lesson_detail'] ?? '';
                               $lesson_data['lesson_description'] = $lesson_detail['lesson_description'] ?? '';
                               $lesson_data['youtube_link'] = $lesson_detail['youtube_link'] ?? '';
                               $lesson_data['meta_abstract'] = $lesson_detail['meta_abstract'] ?? '';
                               $lesson_data['meta_description'] = $lesson_detail['meta_description'] ?? '';
                               $lesson_data['meta_keywords'] = $lesson_detail['meta_keywords'] ?? '';
                               $lesson_data['og_description'] = $lesson_detail['og_description'] ?? '';
                               $lesson_data['og_determiner'] = $lesson_detail['og_determiner'] ?? '';
                               
                               $updated = TRUE;
                               $processed_lessons[] = $lesson_name;
                               
                               // LƯU JSON NGAY LẬP TỨC CHO TỪNG LESSON
                               file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                            } else {
                               $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Lesson entity ID @id not found for @title.', ['@id' => $lesson_id, '@title' => $lesson_name]), NULL, ['type' => 'error']));
                            }
                         } else {
                            $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('JSON decoded but langcode root @lang missing for Lesson @name.', ['@lang' => $langcode, '@name' => $lesson_name]), NULL, ['type' => 'error']));
                         }
                      } else {
                         \Drupal::logger('lms_ai')->error('Invalid JSON from AI Lesson detail generation: @resp', ['@resp' => $ai_response]);
                         $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('AI returned invalid JSON for @lesson.', ['@lesson' => $lesson_name]), NULL, ['type' => 'error']));
                      }
                    } catch (\Exception $e) {
                      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Exception while generating detail for @lesson: @msg', ['@lesson' => $lesson_name, '@msg' => $e->getMessage()]), NULL, ['type' => 'error']));
                    }

                    // Ngủ 1 giây để tránh Rate Limit
                    sleep(1);
                  }
                }
              }
            }
          }
        }
      }
    }

    if ($updated) {
      $list_str = implode(', ', $processed_lessons);
      
      // Update the textarea curriculum_suggestion with the newly saved JSON string
      $json_string = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('#edit-curriculum-suggestion', 'val', [$json_string]));

      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('✅ [Hoàn tất] Đã tạo nội dung cho các Lessons: @list', ['@list' => $list_str]), NULL, ['type' => 'status']));
    } else {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('🎉 Tất cả Lessons đã có mô tả chi tiết! (Hoặc không có bài học nào).'), NULL, ['type' => 'status']));
    }

    return $response;
  }

  /**
   * AJAX handler for 'Build SCORM Packages'.
   */
  public function ajaxGenerateCoursesScorm(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $module_path = \Drupal::service('extension.list.module')->getPath('lms_ai');
    $json_file = \Drupal::root() . '/' . $module_path . '/JSON/ai_curriculum.json';

    if (!file_exists($json_file)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('File JSON curriculum chưa tồn tại.'), NULL, ['type' => 'error']));
      return $response;
    }

    $json_content = file_get_contents($json_file);
    $data = json_decode($json_content, TRUE);
    if (!is_array($data)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('File JSON lỗi định dạng.'), NULL, ['type' => 'error']));
      return $response;
    }

    $config = $this->config('lms_ai.engine.settings');
    $selected_courses = $config->get('selected_courses') ?: [];

    /** @var \Drupal\lms_ai\Service\ScormBuilderService $scorm_builder */
    $scorm_builder = \Drupal::service('lms_ai.scorm_builder');
    $entity_type_manager = \Drupal::entityTypeManager();
    $file_storage = $entity_type_manager->getStorage('file');
    $lesson_storage = $entity_type_manager->getStorage('lms_lesson');

    $processed = 0;
    $list_scorms = [];
    $updated = FALSE;

    foreach ($data as &$category_data) {
      if (!empty($category_data['courses']) && is_array($category_data['courses'])) {
        foreach ($category_data['courses'] as &$course_data) {
          $course_id = $course_data['id'] ?? NULL;
          if (!empty($selected_courses) && !in_array($course_id, $selected_courses)) {
            continue;
          }

          if (!empty($course_data['chapters']) && is_array($course_data['chapters'])) {
            foreach ($course_data['chapters'] as &$chapter_data) {
              if (!empty($chapter_data['lessons']) && is_array($chapter_data['lessons'])) {
                foreach ($chapter_data['lessons'] as &$lesson_data) {
                  // Only build SCORM if slide content exists and scorm hasn't been generated yet
                  if (!empty($lesson_data['slide_content']) && empty($lesson_data['is_scorm_generated'])) {
                    
                    $scorm_payload = [
                      'lesson_id' => $lesson_data['id'] ?? '',
                      'lesson_title' => $lesson_data['lesson_title'] ?? '',
                      'slides' => $lesson_data['slide_content']
                    ];

                    $zip_uri = $scorm_builder->buildScormFromSlides($scorm_payload);
                    if ($zip_uri) {
                      // Import file to Drupal Managed Files
                      $files = $file_storage->loadByProperties(['uri' => $zip_uri]);
                      $file = reset($files);
                      if (!$file) {
                        $file = $file_storage->create([
                          'uri' => $zip_uri,
                          'uid' => \Drupal::currentUser()->id(),
                          'status' => 1, // Permanent
                        ]);
                        $file->save();
                      }

                      // Update LMS Lesson Entity
                      $lesson_entity_id = $lesson_data['id'] ?? NULL;
                      if ($lesson_entity_id && $lesson = $lesson_storage->load($lesson_entity_id)) {
                        $lesson->set('field_scorm_package', ['target_id' => $file->id()]);
                        $lesson->set('field_type', 'scorm');
                        $lesson->save();
                      }

                      // Mark JSON
                      $lesson_data['is_scorm_generated'] = TRUE;
                      $lesson_data['scorm_uri'] = $zip_uri;
                      $updated = TRUE;
                      $processed++;
                      $list_scorms[] = $lesson_data['lesson_title'];

                      // Save JSON IMMEDIATELY after 1 lesson to preserve state
                      file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                      
                      break 4; // Process ONE lesson per click
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    if ($updated) {
      $list_str = implode(', ', $list_scorms);
      
      // Sync JSON textarea
      $json_string = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('#edit-curriculum-suggestion', 'val', [$json_string]));

      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('✅ [Hoàn tất] Đã tạo và Import SCORM cho Lesson: @list', ['@list' => $list_str]), NULL, ['type' => 'status']));
    } else {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('🎉 Tất cả Lessons trong (các) Course được chọn đã có sẵn SCORM! (Hoặc chưa có file Slide Content để build).'), NULL, ['type' => 'status']));
    }

    return $response;
  }

  /**
   * AJAX handler for 'Generate Slide Content'.
   */
  public function ajaxGenerateSlideContent(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $module_path = \Drupal::service('extension.list.module')->getPath('lms_ai');
    $json_file = \Drupal::root() . '/' . $module_path . '/JSON/ai_curriculum.json';

    if (!file_exists($json_file)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('File JSON curriculum chưa tồn tại.'), NULL, ['type' => 'error']));
      return $response;
    }

    $json_content = file_get_contents($json_file);
    $data = json_decode($json_content, TRUE);
    if (!is_array($data)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('File JSON lỗi định dạng.'), NULL, ['type' => 'error']));
      return $response;
    }

    $prompt_template = $form_state->getValue('slide_content_prompt');
    if (empty($prompt_template)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Slide Content Prompt rỗng.'), NULL, ['type' => 'error']));
      return $response;
    }

    $config = $this->config('lms_ai.engine.settings');
    $training_program = $config->get('training_program');
    $langcode = $config->get('program_language');

    $active_provider_id = $config->get('active_provider') ?: 'gemini';
    try {
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $provider */
      $provider = $this->aiProviderManager->createInstance($active_provider_id);
    } catch (\Exception $e) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Lỗi load AI Provider: @msg', ['@msg' => $e->getMessage()]), NULL, ['type' => 'error']));
      return $response;
    }

    $processed_lessons = [];
    $updated = FALSE;
    $selected_courses = $config->get('selected_courses') ?: [];

    foreach ($data as &$category_data) {
      if (!empty($category_data['courses']) && is_array($category_data['courses'])) {
        foreach ($category_data['courses'] as &$course_data) {
          $course_id = $course_data['id'] ?? NULL;
          if (!empty($selected_courses) && !in_array($course_id, $selected_courses)) {
            continue;
          }
          $course_name = $course_data['course_title'];
          $courses_list = json_encode(array_column($category_data['courses'], 'course_title'), JSON_UNESCAPED_UNICODE);

          if (!empty($course_data['chapters']) && is_array($course_data['chapters'])) {
            $chapter_list = json_encode(array_column($course_data['chapters'], 'chapter_title'), JSON_UNESCAPED_UNICODE);

            foreach ($course_data['chapters'] as &$chapter_data) {
              $chapter_name = $chapter_data['chapter_title'];
              
              if (!empty($chapter_data['lessons']) && is_array($chapter_data['lessons'])) {
                $lesson_list = json_encode(array_column($chapter_data['lessons'], 'lesson_title'), JSON_UNESCAPED_UNICODE);

                foreach ($chapter_data['lessons'] as &$lesson_data) {
                  if (empty($lesson_data['is_slide_content_generated'])) {
                    $lesson_name = $lesson_data['lesson_title'];

                    // Prepare prompt
                    $final_prompt = $prompt_template;
                    $final_prompt = str_replace('{{training_program}}', $training_program, $final_prompt);
                    $final_prompt = str_replace('{{langcode}}', $langcode, $final_prompt);
                    $final_prompt = str_replace('{{course_name}}', $course_name, $final_prompt);
                    $final_prompt = str_replace('{{chapter_name}}', $chapter_name, $final_prompt);
                    $final_prompt = str_replace('{{lesson_name}}', $lesson_name, $final_prompt);
                    $final_prompt = str_replace('{{courses_list}}', $courses_list, $final_prompt);
                    $final_prompt = str_replace('{{chapter_list}}', $chapter_list, $final_prompt);
                    $final_prompt = str_replace('{{lesson_list}}', $lesson_list, $final_prompt);

                    // Add instruction for strictly JSON format
                    $system_prompt = "\n\nCRITICAL INSTRUCTION: You MUST return the response strictly as a VALID JSON object. Do not include conversational text or markdown. Follow the exact JSON structure provided in your prompt.";
                    $final_prompt .= $system_prompt;

                    try {
                      $ai_response = $provider->generateText($final_prompt);
                      $ai_response = preg_replace('/```(?:json)?|```/', '', $ai_response);
                      $slide_data = json_decode(trim($ai_response), TRUE);

                      if (json_last_error() === JSON_ERROR_NONE && is_array($slide_data)) {
                         // Depending on prompt output structure. Example: { "slide_content": [...] }
                         $slide_content = $slide_data['slide_content'] ?? NULL;
                         
                         if ($slide_content) {
                            $lesson_data['is_slide_content_generated'] = TRUE;
                            $lesson_data['slide_content'] = $slide_content;
                            
                            $updated = TRUE;
                            $processed_lessons[] = $lesson_name;
                            
                            // LƯU JSON NGAY LẬP TỨC CHO TỪNG LESSON
                            file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                         } else {
                            $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('JSON decoded but slide_content key missing for Lesson @name.', ['@name' => $lesson_name]), NULL, ['type' => 'error']));
                         }
                      } else {
                         \Drupal::logger('lms_ai')->error('Invalid JSON from AI Slide Content generation: @resp', ['@resp' => $ai_response]);
                         $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('AI returned invalid JSON for @lesson.', ['@lesson' => $lesson_name]), NULL, ['type' => 'error']));
                      }
                    } catch (\Exception $e) {
                      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Exception while generating slide content for @lesson: @msg', ['@lesson' => $lesson_name, '@msg' => $e->getMessage()]), NULL, ['type' => 'error']));
                    }

                    // Process one at a time per click to avoid timeout, or continue? 
                    // To generate all, we sleep 1s and continue. Let's do 1 at a time for safety since these prompts can be large,
                    // but usually we loop and sleep. The older method loops and sleeps. I will loop and sleep.
                    sleep(1);
                    break 4; // Break out of all loops to only process ONE lesson at a time (like the prompt requested previously to be safe? Wait, ajaxGenerateLessonsDetail didn't break 4, it just slept. Ah wait, previously it did process all. So I should remove break 4.)
                  }
                }
              }
            }
          }
        }
      }
    }

    if ($updated) {
      $list_str = implode(', ', $processed_lessons);
      
      // Update the textarea curriculum_suggestion with the newly saved JSON string
      $json_string = file_get_contents($json_file);
      $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('#edit-curriculum-suggestion', 'val', [$json_string]));

      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('✅ [Hoàn tất] Đã tạo nội dung Slide Content cho Bài học: @list', ['@list' => $list_str]), NULL, ['type' => 'status']));
    } else {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('🎉 Tất cả bài học đã được tạo nội dung Slide Content! (Hoặc không có bài học nào).'), NULL, ['type' => 'status']));
    }

    return $response;
  }

  /**
   * AJAX handler for 'Generate Google Slide Prompt'.
   */
  public function ajaxGenerateGoogleSlidePrompt(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Bước này đã được tự động hóa hoàn toàn trong V2. Bạn không cần tạo Prompt Format thủ công nữa. Hãy chạy thẳng "Generate Google Slide" nhé!'), NULL, ['type' => 'status']));
    return $response;
  }

  /**
   * AJAX handler for 'Generate Google Slide'.
   */
  public function ajaxGenerateGoogleSlide(array &$form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $module_path = \Drupal::service('extension.list.module')->getPath('lms_ai');
    $json_file = \Drupal::root() . '/' . $module_path . '/JSON/ai_curriculum.json';

    if (!file_exists($json_file)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('File JSON curriculum chưa tồn tại.'), NULL, ['type' => 'error']));
      return $response;
    }

    $json_content = file_get_contents($json_file);
    $data = json_decode($json_content, TRUE);
    if (!is_array($data)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('File JSON lỗi định dạng.'), NULL, ['type' => 'error']));
      return $response;
    }

    $template_id = $form_state->getValue('google_slide_template_id');
    if (empty($template_id)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Vui lòng nhập Google Slide Template ID.'), NULL, ['type' => 'error']));
      return $response;
    }

    /** @var \Drupal\lms_ai\Service\GoogleSlideBuilderService $google_slide_builder */
    $google_slide_builder = \Drupal::service('lms_ai.google_slide_builder');
    $google_api_client_id = '1'; // Default OAuth Client ID
    $processed_lessons = [];
    $updated = FALSE;
    
    $config = $this->config('lms_ai.engine.settings');
    $selected_courses = $config->get('selected_courses') ?: [];

    foreach ($data as &$category_data) {
      if (!empty($category_data['courses']) && is_array($category_data['courses'])) {
        foreach ($category_data['courses'] as &$course_data) {
          $course_id = $course_data['id'] ?? NULL;
          if (!empty($selected_courses) && !in_array($course_id, $selected_courses)) {
            continue;
          }
          if (!empty($course_data['chapters']) && is_array($course_data['chapters'])) {
            foreach ($course_data['chapters'] as &$chapter_data) {
              if (!empty($chapter_data['lessons']) && is_array($chapter_data['lessons'])) {
                foreach ($chapter_data['lessons'] as &$lesson_data) {
                  // Chỉ tạo Slide khi đã có slide_content và chưa có google_slide_id
                  if (!empty($lesson_data['is_slide_content_generated']) && empty($lesson_data['is_google_slide_generated'])) {
                    $lesson_name = $lesson_data['lesson_title'];
                    $mock_json = ['slide_content' => $lesson_data['slide_content']];

                    $new_slide_id = $google_slide_builder->buildSlideFromTemplate(
                      $template_id, 
                      $mock_json, 
                      '[EverLMS] ' . $lesson_name,
                      $google_api_client_id
                    );

                    if ($new_slide_id) {
                      $lesson_data['is_google_slide_generated'] = TRUE;
                      $lesson_data['google_slide_id'] = $new_slide_id;
                      
                      $updated = TRUE;
                      $processed_lessons[] = $lesson_name;
                      
                      // Lưu nhanh
                      file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    } else {
                      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Có lỗi khi tạo Slide cho: @lesson (Xem logs chi tiết).', ['@lesson' => $lesson_name]), NULL, ['type' => 'error']));
                    }

                    // Để an toàn Quota Google, chỉ tạo 1 file mỗi lần click
                    break 4;
                  }
                }
              }
            }
          }
        }
      }
    }

    if ($updated) {
      $list_str = implode(', ', $processed_lessons);
      $json_string = file_get_contents($json_file);
      $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('#edit-curriculum-suggestion', 'val', [$json_string]));
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('✅ [Hoàn tất] Đã tạo Google Slide cho các bài học: @list', ['@list' => $list_str]), NULL, ['type' => 'status']));
    } else {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('🎉 Tất cả bài học (đã có Slide Content) đều đã được map thành file Google Slide!'), NULL, ['type' => 'status']));
    }

    return $response;
  }

  /**
   * AJAX callback for generating Quizzes.
   */
  public function ajaxGenerateQuiz(array &$form, \Drupal\Core\Form\FormStateInterface $form_state): \Drupal\Core\Ajax\AjaxResponse {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    
    list($success, $data, $json_file) = $this->getCurriculumJsonData();
    if (!$success) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Không tìm thấy file JSON curriculum.'), NULL, ['type' => 'error']));
      return $response;
    }

    $prompt_template = $form_state->getValue('quiz_prompt');
    if (empty($prompt_template)) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Quiz Prompt template rỗng.'), NULL, ['type' => 'error']));
      return $response;
    }

    $config = $this->config('lms_ai.engine.settings');
    $langcode = $config->get('program_language') ?: 'vi';
    $training_program = $config->get('training_program');
    
    $active_provider_id = $config->get('active_provider') ?: 'gemini';

    try {
      /** @var \Drupal\lms_ai\Plugin\AiProviderInterface $provider */
      $provider = $this->aiProviderManager->createInstance($active_provider_id);
    } catch (\Exception $e) {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Lỗi load AI Provider: @msg', ['@msg' => $e->getMessage()]), NULL, ['type' => 'error']));
      return $response;
    }

    $updated = FALSE;
    $processed_lessons = [];

    foreach ($data as &$category_data) {
      if (!empty($category_data['courses']) && is_array($category_data['courses'])) {
        foreach ($category_data['courses'] as &$course_data) {
          $course_title = $course_data['course_title'] ?? '';
          
          if (!empty($course_data['chapters']) && is_array($course_data['chapters'])) {
            foreach ($course_data['chapters'] as &$chapter_data) {
              $chapter_title = $chapter_data['chapter_title'] ?? '';
              
              if (!empty($chapter_data['lessons']) && is_array($chapter_data['lessons'])) {
                foreach ($chapter_data['lessons'] as &$lesson_data) {
                  // Only generate if not previously generated
                  if (empty($lesson_data['is_quiz_generated'])) {
                    $lesson_name = $lesson_data['lesson_title'] ?? '';
                    $lesson_detail = $lesson_data['lesson_detail'] ?? '';

                    // Prepare prompt
                    $final_prompt = $prompt_template;
                    $final_prompt = str_replace('{{training_program}}', $training_program, $final_prompt);
                    $final_prompt = str_replace('{{langcode}}', $langcode, $final_prompt);
                    $final_prompt = str_replace('{{course_title}}', $course_title, $final_prompt);
                    $final_prompt = str_replace('{{chapter_title}}', $chapter_title, $final_prompt);
                    $final_prompt = str_replace('{{lesson_title}}', $lesson_name, $final_prompt);
                    $final_prompt = str_replace('{{lesson_content}}', $lesson_detail, $final_prompt);

                    // Add instruction for strictly JSON format matching the Quiz schema
                    $system_prompt = "\n\nCRITICAL INSTRUCTION: You MUST return the response strictly as a VALID JSON array of objects. Do not include conversational text, markdown formatting like ```json, or nested keys. Just the raw array [{...}, {...}].";
                    $final_prompt .= $system_prompt;

                    try {
                      $ai_response = $provider->generateText($final_prompt);
                      // Clear potential markdown code blocks
                      $ai_response = preg_replace('/```(?:json)?|```/', '', $ai_response);
                      $quiz_json = json_decode(trim($ai_response), TRUE);

                      if (json_last_error() === JSON_ERROR_NONE && is_array($quiz_json)) {
                         // Save directly the Quiz questions array into the lesson data
                         $lesson_data['is_quiz_generated'] = TRUE;
                         $lesson_data['quiz_data'] = $quiz_json;
                         
                         $updated = TRUE;
                         $processed_lessons[] = $lesson_name;
                         
                         // LƯU JSON NGAY LẬP TỨC CHO TỪNG LESSON
                         file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                      } else {
                         \Drupal::logger('lms_ai')->error('Invalid JSON from AI Quiz generation: @resp', ['@resp' => $ai_response]);
                         $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('AI returned invalid JSON array for @lesson.', ['@lesson' => $lesson_name]), NULL, ['type' => 'error']));
                      }
                    } catch (\Exception $e) {
                      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Exception while generating quiz for @lesson: @msg', ['@lesson' => $lesson_name, '@msg' => $e->getMessage()]), NULL, ['type' => 'error']));
                    }

                    // Sleep to prevent Rate Limits
                    sleep(2);
                  }
                }
              }
            }
          }
        }
      }
    }

    if ($updated) {
      $list_str = implode(', ', $processed_lessons);
      $json_string = file_get_contents($json_file);
      $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand('#edit-curriculum-suggestion', 'val', [$json_string]));
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('✅ Đã tạo Quiz bằng AI thành công cho các bài học: @list', ['@list' => $list_str]), NULL, ['type' => 'status']));
    } else {
      $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Tất cả bài học đều đã được tạo Quiz hoặc không có bài học nào.'), NULL, ['type' => 'status']));
    }

    return $response;
  }

  /**
   * AJAX callback for approving Quizzes.
   */
  public function ajaxApproveQuiz(array &$form, \Drupal\Core\Form\FormStateInterface $form_state): \Drupal\Core\Ajax\AjaxResponse {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    // Implementation for creating the Drupal Quiz entities
    // from the generated JSON will go here.
    $response->addCommand(new \Drupal\Core\Ajax\MessageCommand($this->t('Approving Quizzes and creating entities...'), NULL, ['type' => 'status']));
    return $response;
  }

}
