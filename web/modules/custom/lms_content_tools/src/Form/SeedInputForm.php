<?php

declare(strict_types=1);

namespace Drupal\lms_content_tools\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lms_content_tools\Service\ContentToolsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for entering seed input data for AI content generation.
 */
final class SeedInputForm extends FormBase {

  public function __construct(
    protected ContentToolsManager $contentToolsManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('lms_content_tools.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lms_content_tools_seed_input_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Load existing data.
    $data = $this->contentToolsManager->loadSeedInput() ?? [];

    $form['#tree'] = TRUE;

    // Notification if seed data already exists.
    if (!empty($data)) {
      $form['existing_notice'] = [
        '#type' => 'markup',
        '#markup' => '<div class="messages messages--status">' . $this->t('Loaded existing seed input data (saved at @date).', [
          '@date' => $data['generated_at'] ?? 'unknown',
        ]) . '</div>',
      ];
    }

    // === Section 1: Organization ===
    $org = $data['organization'] ?? [];
    $form['organization'] = [
      '#type' => 'details',
      '#title' => $this->t('1. Organization Information'),
      '#open' => TRUE,
    ];
    $form['organization']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Organization / Company Name'),
      '#default_value' => $org['name'] ?? '',
      '#required' => TRUE,
      '#maxlength' => 255,
    ];
    $form['organization']['short_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Short Name'),
      '#default_value' => $org['short_name'] ?? '',
      '#maxlength' => 100,
    ];
    $form['organization']['industry'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Industry'),
      '#default_value' => $org['industry'] ?? '',
      '#required' => TRUE,
    ];
    $form['organization']['website'] = [
      '#type' => 'url',
      '#title' => $this->t('Website'),
      '#default_value' => $org['website'] ?? '',
    ];
    $form['organization']['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Organization Description'),
      '#default_value' => $org['description'] ?? '',
      '#rows' => 3,
    ];
    $form['organization']['logo_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Logo URL'),
      '#default_value' => $org['logo_url'] ?? '',
    ];

    // === Section 2: Training Program ===
    $training = $data['training_program'] ?? [];
    $form['training_program'] = [
      '#type' => 'details',
      '#title' => $this->t('2. Training Program Configuration'),
      '#open' => TRUE,
    ];
    $form['training_program']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Training Program Name'),
      '#default_value' => $training['name'] ?? '',
      '#required' => TRUE,
    ];
    $form['training_program']['objectives'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Training Objectives'),
      '#default_value' => $training['objectives'] ?? '',
      '#required' => TRUE,
      '#rows' => 3,
    ];
    $form['training_program']['target_audience'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Target Audience'),
      '#default_value' => $training['target_audience'] ?? '',
      '#required' => TRUE,
      '#rows' => 2,
    ];
    $form['training_program']['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Language'),
      '#options' => [
        'vi' => $this->t('Vietnamese'),
        'en' => $this->t('English'),
        'bilingual' => $this->t('Bilingual (VI + EN)'),
      ],
      '#default_value' => $training['language'] ?? 'vi',
      '#required' => TRUE,
    ];
    $form['training_program']['tone'] = [
      '#type' => 'select',
      '#title' => $this->t('Tone & Style'),
      '#options' => [
        'professional' => $this->t('Professional'),
        'friendly' => $this->t('Friendly'),
        'academic' => $this->t('Academic'),
      ],
      '#default_value' => $training['tone'] ?? 'professional',
      '#required' => TRUE,
    ];
    $form['training_program']['detail_level'] = [
      '#type' => 'select',
      '#title' => $this->t('Detail Level'),
      '#options' => [
        'basic' => $this->t('Basic'),
        'intermediate' => $this->t('Intermediate'),
        'detailed' => $this->t('Detailed'),
      ],
      '#default_value' => $training['detail_level'] ?? 'intermediate',
      '#required' => TRUE,
    ];

    // === Section 3: Courses ===
    $courses = $data['courses'] ?? [];
    $form['courses'] = [
      '#type' => 'details',
      '#title' => $this->t('3. Course List'),
      '#open' => TRUE,
    ];

    $courseCount = $form_state->get('course_count');
    if ($courseCount === NULL) {
      $courseCount = max(1, count($courses));
      $form_state->set('course_count', $courseCount);
    }

    $form['courses']['items'] = [
      '#type' => 'container',
      '#prefix' => '<div id="courses-wrapper">',
      '#suffix' => '</div>',
    ];

    for ($i = 0; $i < $courseCount; $i++) {
      $course = $courses[$i] ?? [];
      $form['courses']['items'][$i] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Course @num', ['@num' => $i + 1]),
        '#attributes' => ['style' => 'border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius: 5px;'],
      ];
      $form['courses']['items'][$i]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Course Title'),
        '#default_value' => $course['title'] ?? '',
        '#required' => TRUE,
      ];
      $form['courses']['items'][$i]['description'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Short Description'),
        '#default_value' => $course['description'] ?? '',
        '#required' => TRUE,
        '#rows' => 2,
      ];
      $form['courses']['items'][$i]['category'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Category'),
        '#description' => $this->t('Enter a course category (e.g., Programming, Healthcare).'),
        '#default_value' => $course['category'] ?? 'Programming',
        '#required' => TRUE,
      ];
      $form['courses']['items'][$i]['level'] = [
        '#type' => 'select',
        '#title' => $this->t('Level'),
        '#options' => [
          'beginner' => $this->t('Beginner'),
          'intermediate' => $this->t('Intermediate'),
          'advanced' => $this->t('Advanced'),
        ],
        '#default_value' => $course['level'] ?? 'beginner',
        '#required' => TRUE,
      ];

      $row = [
        '#type' => 'container',
        '#attributes' => ['style' => 'display: flex; gap: 15px; flex-wrap: wrap;'],
      ];
      $row['chapters_count'] = [
        '#type' => 'number',
        '#title' => $this->t('Chapters'),
        '#default_value' => $course['chapters_count'] ?? 5,
        '#min' => 2,
        '#max' => 20,
        '#required' => TRUE,
        '#attributes' => ['style' => 'width: 80px;'],
      ];
      $row['lessons_per_chapter'] = [
        '#type' => 'number',
        '#title' => $this->t('Lessons/Chapter'),
        '#default_value' => $course['lessons_per_chapter'] ?? 4,
        '#min' => 2,
        '#max' => 15,
        '#required' => TRUE,
        '#attributes' => ['style' => 'width: 80px;'],
      ];
      $form['courses']['items'][$i]['counts'] = $row;

      $form['courses']['items'][$i]['has_final_quiz'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Has final quiz'),
        '#default_value' => $course['has_final_quiz'] ?? TRUE,
      ];
      $form['courses']['items'][$i]['has_certificate'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Has certificate'),
        '#default_value' => $course['has_certificate'] ?? TRUE,
      ];
      $form['courses']['items'][$i]['pricing_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Pricing'),
        '#options' => [
          'free' => $this->t('Free'),
          'paid' => $this->t('Paid'),
          'membership' => $this->t('Membership Only'),
        ],
        '#default_value' => $course['pricing_type'] ?? 'free',
      ];
      $form['courses']['items'][$i]['price'] = [
        '#type' => 'number',
        '#title' => $this->t('Price (if paid)'),
        '#default_value' => $course['price'] ?? '',
        '#min' => 0,
      ];
      $form['courses']['items'][$i]['keywords'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Keywords / Main Topics'),
        '#default_value' => $course['keywords'] ?? '',
        '#rows' => 2,
        '#description' => $this->t('Comma-separated keywords to guide AI content generation.'),
      ];
      $form['courses']['items'][$i]['notes'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Additional Notes'),
        '#default_value' => $course['notes'] ?? '',
        '#rows' => 2,
      ];
    }

    $form['courses']['add_course'] = [
      '#type' => 'submit',
      '#value' => $this->t('+ Add Course'),
      '#submit' => ['::addCourse'],
      '#ajax' => [
        'callback' => '::ajaxCoursesCallback',
        'wrapper' => 'courses-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($courseCount > 1) {
      $form['courses']['remove_course'] = [
        '#type' => 'submit',
        '#value' => $this->t('- Remove Last Course'),
        '#submit' => ['::removeCourse'],
        '#ajax' => [
          'callback' => '::ajaxCoursesCallback',
          'wrapper' => 'courses-wrapper',
        ],
        '#limit_validation_errors' => [],
      ];
    }

    // === Section 4: Instructors ===
    $instructors = $data['instructors'] ?? [];
    $form['instructors'] = [
      '#type' => 'details',
      '#title' => $this->t('4. Instructors'),
      '#open' => TRUE,
    ];

    $instructorCount = $form_state->get('instructor_count');
    if ($instructorCount === NULL) {
      $instructorCount = max(1, count($instructors));
      $form_state->set('instructor_count', $instructorCount);
    }

    $form['instructors']['items'] = [
      '#type' => 'container',
      '#prefix' => '<div id="instructors-wrapper">',
      '#suffix' => '</div>',
    ];

    for ($i = 0; $i < $instructorCount; $i++) {
      $instructor = $instructors[$i] ?? [];
      $form['instructors']['items'][$i] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Instructor @num', ['@num' => $i + 1]),
        '#attributes' => ['style' => 'border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius: 5px;'],
      ];
      $form['instructors']['items'][$i]['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Full Name'),
        '#default_value' => $instructor['name'] ?? '',
        '#required' => TRUE,
      ];
      $form['instructors']['items'][$i]['email'] = [
        '#type' => 'email',
        '#title' => $this->t('Email'),
        '#default_value' => $instructor['email'] ?? '',
        '#required' => TRUE,
      ];
      $form['instructors']['items'][$i]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Job Title'),
        '#default_value' => $instructor['title'] ?? '',
      ];
      $form['instructors']['items'][$i]['bio'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Short Bio'),
        '#default_value' => $instructor['bio'] ?? '',
        '#rows' => 2,
      ];
      $form['instructors']['items'][$i]['expertise'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Expertise'),
        '#default_value' => $instructor['expertise'] ?? '',
        '#rows' => 2,
      ];
    }

    $form['instructors']['add_instructor'] = [
      '#type' => 'submit',
      '#value' => $this->t('+ Add Instructor'),
      '#submit' => ['::addInstructor'],
      '#ajax' => [
        'callback' => '::ajaxInstructorsCallback',
        'wrapper' => 'instructors-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($instructorCount > 1) {
      $form['instructors']['remove_instructor'] = [
        '#type' => 'submit',
        '#value' => $this->t('- Remove Last Instructor'),
        '#submit' => ['::removeInstructor'],
        '#ajax' => [
          'callback' => '::ajaxInstructorsCallback',
          'wrapper' => 'instructors-wrapper',
        ],
        '#limit_validation_errors' => [],
      ];
    }

    // === Section 5: Sample Data Config ===
    $sampleData = $data['sample_data'] ?? [];
    $form['sample_data'] = [
      '#type' => 'details',
      '#title' => $this->t('5. Sample Data Configuration'),
      '#open' => FALSE,
    ];
    $form['sample_data']['students_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of sample students'),
      '#default_value' => $sampleData['students_count'] ?? 20,
      '#min' => 0,
      '#max' => 100,
    ];
    $form['sample_data']['create_enrollments'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample enrollments'),
      '#default_value' => $sampleData['create_enrollments'] ?? TRUE,
    ];
    $form['sample_data']['create_progress'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample progress'),
      '#default_value' => $sampleData['create_progress'] ?? TRUE,
    ];
    $form['sample_data']['create_quiz_results'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample quiz results'),
      '#default_value' => $sampleData['create_quiz_results'] ?? TRUE,
    ];
    $form['sample_data']['create_ratings'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample ratings & reviews'),
      '#default_value' => $sampleData['create_ratings'] ?? TRUE,
    ];
    $form['sample_data']['create_forum_topics'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample forum topics'),
      '#default_value' => $sampleData['create_forum_topics'] ?? TRUE,
    ];
    $form['sample_data']['create_orders'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample orders'),
      '#default_value' => $sampleData['create_orders'] ?? TRUE,
    ];
    $form['sample_data']['create_membership_plans'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create membership plans (Basic/Premium/Enterprise)'),
      '#default_value' => $sampleData['create_membership_plans'] ?? TRUE,
    ];
    $form['sample_data']['create_notifications'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample notifications'),
      '#default_value' => $sampleData['create_notifications'] ?? FALSE,
    ];
    $form['sample_data']['create_badges'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample badges'),
      '#default_value' => $sampleData['create_badges'] ?? TRUE,
    ];
    $form['sample_data']['create_slideshows'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample slideshows (Home banners)'),
      '#default_value' => $sampleData['create_slideshows'] ?? TRUE,
    ];
    $form['sample_data']['create_introductions'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample introductions (Landing blocks)'),
      '#default_value' => $sampleData['create_introductions'] ?? TRUE,
    ];
    $form['sample_data']['create_glossaries'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample glossaries (Vocabulary)'),
      '#default_value' => $sampleData['create_glossaries'] ?? TRUE,
    ];
    $form['sample_data']['create_blogs'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample blog articles'),
      '#default_value' => $sampleData['create_blogs'] ?? TRUE,
    ];
    $form['sample_data']['create_faqs'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create sample FAQs (Course & Global)'),
      '#default_value' => $sampleData['create_faqs'] ?? TRUE,
    ];
    $form['sample_data']['currency'] = [
      '#type' => 'select',
      '#title' => $this->t('Currency'),
      '#options' => [
        'VND' => 'VND',
        'USD' => 'USD',
        'EUR' => 'EUR',
      ],
      '#default_value' => $sampleData['currency'] ?? 'VND',
    ];

    // === Section 6: Advanced ===
    $advanced = $data['advanced'] ?? [];
    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('6. Advanced Settings'),
      '#open' => FALSE,
    ];
    $form['advanced']['default_lesson_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Lesson Content Type'),
      '#options' => [
        'text' => $this->t('Text'),
        'video' => $this->t('Video'),
        'mixed' => $this->t('Mixed'),
      ],
      '#default_value' => $advanced['default_lesson_type'] ?? 'text',
    ];
    $form['advanced']['video_placeholder_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Video Placeholder URL'),
      '#default_value' => $advanced['video_placeholder_url'] ?? '',
      '#description' => $this->t('YouTube/Vimeo URL used as placeholder for video lessons.'),
    ];
    $form['advanced']['create_prerequisites'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create prerequisite chains between courses/chapters'),
      '#default_value' => $advanced['create_prerequisites'] ?? FALSE,
    ];
    $form['advanced']['create_drip_content'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create drip content schedules'),
      '#default_value' => $advanced['create_drip_content'] ?? FALSE,
    ];
    $form['advanced']['create_bundles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create course bundles'),
      '#default_value' => $advanced['create_bundles'] ?? FALSE,
    ];
    $form['advanced']['additional_ai_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Additional AI Prompt'),
      '#default_value' => $advanced['additional_ai_prompt'] ?? '',
      '#rows' => 4,
      '#description' => $this->t('Extra instructions for AI when generating content.'),
    ];

    // === Actions ===
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Seed Input'),
      '#attributes' => ['class' => ['button--primary']],
    ];
    $form['actions']['view_json'] = [
      '#type' => 'submit',
      '#value' => $this->t('View JSON'),
      '#submit' => ['::viewJson'],
    ];

    // Show JSON preview if requested.
    if ($form_state->get('show_json')) {
      $form['json_preview'] = [
        '#type' => 'details',
        '#title' => $this->t('JSON Preview'),
        '#open' => TRUE,
      ];
      $form['json_preview']['content'] = [
        '#type' => 'textarea',
        '#value' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        '#rows' => 30,
        '#attributes' => ['readonly' => 'readonly', 'style' => 'font-family: monospace; font-size: 12px;'],
      ];
    }

    return $form;
  }

  /**
   * AJAX callback for courses section.
   */
  public function ajaxCoursesCallback(array &$form, FormStateInterface $form_state): array {
    return $form['courses']['items'];
  }

  /**
   * AJAX callback for instructors section.
   */
  public function ajaxInstructorsCallback(array &$form, FormStateInterface $form_state): array {
    return $form['instructors']['items'];
  }

  /**
   * Add course handler.
   */
  public function addCourse(array &$form, FormStateInterface $form_state): void {
    $count = $form_state->get('course_count') ?? 1;
    $form_state->set('course_count', $count + 1);
    $form_state->setRebuild();
  }

  /**
   * Remove last course handler.
   */
  public function removeCourse(array &$form, FormStateInterface $form_state): void {
    $count = $form_state->get('course_count') ?? 1;
    if ($count > 1) {
      $form_state->set('course_count', $count - 1);
    }
    $form_state->setRebuild();
  }

  /**
   * Add instructor handler.
   */
  public function addInstructor(array &$form, FormStateInterface $form_state): void {
    $count = $form_state->get('instructor_count') ?? 1;
    $form_state->set('instructor_count', $count + 1);
    $form_state->setRebuild();
  }

  /**
   * Remove last instructor handler.
   */
  public function removeInstructor(array &$form, FormStateInterface $form_state): void {
    $count = $form_state->get('instructor_count') ?? 1;
    if ($count > 1) {
      $form_state->set('instructor_count', $count - 1);
    }
    $form_state->setRebuild();
  }

  /**
   * View JSON handler.
   */
  public function viewJson(array &$form, FormStateInterface $form_state): void {
    $form_state->set('show_json', TRUE);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $data = $this->buildSeedData($form_state);

    if ($this->contentToolsManager->saveSeedInput($data)) {
      $this->messenger()->addStatus($this->t('Seed input saved successfully to @path', [
        '@path' => $this->contentToolsManager->getSeedInputPath(),
      ]));
    }
    else {
      $this->messenger()->addError($this->t('Failed to save seed input file.'));
    }
  }

  /**
   * Builds the seed data array from form state values.
   */
  protected function buildSeedData(FormStateInterface $form_state): array {
    $values = $form_state->getValues();

    $data = [
      'organization' => [
        'name' => $values['organization']['name'] ?? '',
        'short_name' => $values['organization']['short_name'] ?? '',
        'industry' => $values['organization']['industry'] ?? '',
        'website' => $values['organization']['website'] ?? '',
        'description' => $values['organization']['description'] ?? '',
        'logo_url' => $values['organization']['logo_url'] ?? '',
      ],
      'training_program' => [
        'name' => $values['training_program']['name'] ?? '',
        'objectives' => $values['training_program']['objectives'] ?? '',
        'target_audience' => $values['training_program']['target_audience'] ?? '',
        'language' => $values['training_program']['language'] ?? 'vi',
        'tone' => $values['training_program']['tone'] ?? 'professional',
        'detail_level' => $values['training_program']['detail_level'] ?? 'intermediate',
      ],
      'courses' => [],
      'instructors' => [],
      'sample_data' => [
        'students_count' => (int) ($values['sample_data']['students_count'] ?? 20),
        'create_enrollments' => (bool) ($values['sample_data']['create_enrollments'] ?? TRUE),
        'create_progress' => (bool) ($values['sample_data']['create_progress'] ?? TRUE),
        'create_quiz_results' => (bool) ($values['sample_data']['create_quiz_results'] ?? TRUE),
        'create_ratings' => (bool) ($values['sample_data']['create_ratings'] ?? TRUE),
        'create_forum_topics' => (bool) ($values['sample_data']['create_forum_topics'] ?? TRUE),
        'create_orders' => (bool) ($values['sample_data']['create_orders'] ?? TRUE),
        'create_membership_plans' => (bool) ($values['sample_data']['create_membership_plans'] ?? TRUE),
        'create_notifications' => (bool) ($values['sample_data']['create_notifications'] ?? FALSE),
        'create_badges' => (bool) ($values['sample_data']['create_badges'] ?? TRUE),
        'create_slideshows' => (bool) ($values['sample_data']['create_slideshows'] ?? TRUE),
        'create_introductions' => (bool) ($values['sample_data']['create_introductions'] ?? TRUE),
        'create_glossaries' => (bool) ($values['sample_data']['create_glossaries'] ?? TRUE),
        'create_blogs' => (bool) ($values['sample_data']['create_blogs'] ?? TRUE),
        'create_faqs' => (bool) ($values['sample_data']['create_faqs'] ?? TRUE),
        'currency' => $values['sample_data']['currency'] ?? 'VND',
      ],
      'advanced' => [
        'default_lesson_type' => $values['advanced']['default_lesson_type'] ?? 'text',
        'video_placeholder_url' => $values['advanced']['video_placeholder_url'] ?? '',
        'create_prerequisites' => (bool) ($values['advanced']['create_prerequisites'] ?? FALSE),
        'create_drip_content' => (bool) ($values['advanced']['create_drip_content'] ?? FALSE),
        'create_bundles' => (bool) ($values['advanced']['create_bundles'] ?? FALSE),
        'additional_ai_prompt' => $values['advanced']['additional_ai_prompt'] ?? '',
      ],
    ];

    // Build courses array.
    $courseItems = $values['courses']['items'] ?? [];
    $uniqueCategories = [];
    foreach ($courseItems as $i => $course) {
      if (is_array($course) && !empty($course['title'])) {
        $category = trim($course['category'] ?? 'Programming');
        if (!in_array($category, $uniqueCategories)) {
          $uniqueCategories[] = $category;
        }

        $data['courses'][] = [
          'id' => $i + 1,
          'title' => $course['title'],
          'description' => $course['description'] ?? '',
          'category' => $category,
          'level' => $course['level'] ?? 'beginner',
          'chapters_count' => (int) ($course['counts']['chapters_count'] ?? 5),
          'lessons_per_chapter' => (int) ($course['counts']['lessons_per_chapter'] ?? 4),
          'has_final_quiz' => (bool) ($course['has_final_quiz'] ?? TRUE),
          'has_certificate' => (bool) ($course['has_certificate'] ?? TRUE),
          'pricing_type' => $course['pricing_type'] ?? 'free',
          'price' => !empty($course['price']) ? (int) $course['price'] : NULL,
          'keywords' => $course['keywords'] ?? '',
          'notes' => $course['notes'] ?? '',
        ];
      }
    }
    
    // Attach unique categories
    $data['categories'] = $uniqueCategories;

    // Build instructors array.
    $instructorItems = $values['instructors']['items'] ?? [];
    foreach ($instructorItems as $i => $instructor) {
      if (is_array($instructor) && !empty($instructor['name'])) {
        $data['instructors'][] = [
          'id' => $i + 1,
          'name' => $instructor['name'],
          'email' => $instructor['email'] ?? '',
          'title' => $instructor['title'] ?? '',
          'bio' => $instructor['bio'] ?? '',
          'expertise' => $instructor['expertise'] ?? '',
        ];
      }
    }

    return $data;
  }

}
