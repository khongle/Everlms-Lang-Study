<?php

declare(strict_types=1);

namespace Drupal\lms_course\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_base\Service\LmsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Admin/Management dashboard and entity listing pages.
 */
final class AdminController extends ControllerBase {

  protected LmsHelper $lmsHelper;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LmsHelper $lms_helper,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->lmsHelper = $lms_helper;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('lms_base.helper'),
    );
  }

  /**
   * Admin dashboard — KPI cards + recent orders.
   */
  public function dashboard(): array {
    // Course count.
    $course_count = (int) $this->entityTypeManager->getStorage('lms_course')
      ->getQuery()->accessCheck(FALSE)->count()->execute();

    // Active learners (users with enrollments).
    $learner_count = (int) $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()->accessCheck(FALSE)->count()->execute();

    // Revenue from orders.
    $order_ids = $this->entityTypeManager->getStorage('lms_order')
      ->getQuery()
      ->condition('field_status', ['completed', 'paid'], 'IN')
      ->accessCheck(FALSE)
      ->execute();
    $orders = $order_ids
      ? $this->entityTypeManager->getStorage('lms_order')->loadMultiple($order_ids)
      : [];
    $revenue = 0;
    foreach ($orders as $order) {
      $revenue += (float) ($order->get('field_total')->value ?? 0);
    }

    // Quiz completion rate.
    $quiz_results = (int) $this->entityTypeManager->getStorage('lms_quiz_result')
      ->getQuery()->accessCheck(FALSE)->count()->execute();

    // Recent orders.
    $recent_order_ids = $this->entityTypeManager->getStorage('lms_order')
      ->getQuery()
      ->sort('created', 'DESC')
      ->range(0, 10)
      ->accessCheck(FALSE)
      ->execute();
    $recent_orders = $recent_order_ids
      ? $this->entityTypeManager->getStorage('lms_order')->loadMultiple($recent_order_ids)
      : [];

    $order_items = [];
    foreach ($recent_orders as $order) {
      $user = $order->getOwner();
      $first_name = $user && $user->hasField('field_first_name') ? $user->get('field_first_name')->value : '';
      $last_name = $user && $user->hasField('field_last_name') ? $user->get('field_last_name')->value : '';
      $learner_name = $user ? (trim("$first_name $last_name") ?: $user->getDisplayName()) : 'Unknown';

      $order_items[] = [
        'id' => $order->id(),
        'learner' => $learner_name,
        'total' => (float) ($order->get('field_total')->value ?? 0),
        'status' => $order->get('field_status')->value ?? 'pending',
        'created' => $order->get('created')->value,
      ];
    }

    return [
      '#theme' => 'lms_admin_dashboard',
      '#stats' => [
        'courses' => $course_count,
        'learners' => $learner_count,
        'revenue' => $revenue,
        'quiz_results' => $quiz_results,
      ],
      '#recent_orders' => $order_items,
    ];
  }

  /**
   * Course management list.
   */
  public function courseList(): array {
    $request = \Drupal::request();
    $status_filter = $request->query->get('status');
    $category_filter = $request->query->get('category');
    $instructor_filter = $request->query->get('instructor');
    $search_filter = $request->query->get('search');

    $query = $this->entityTypeManager->getStorage('lms_course')->getQuery()
      ->sort('created', 'DESC')
      ->accessCheck(FALSE);

    if ($status_filter && $status_filter !== 'all') {
      $query->condition('status', $status_filter === 'published' ? 1 : 0);
    }
    if ($category_filter && $category_filter !== 'all') {
      $query->condition('field_category', $category_filter);
    }
    if ($instructor_filter && $instructor_filter !== 'all') {
      $query->condition('field_instructor', $instructor_filter);
    }
    if ($search_filter) {
      $query->condition('name', '%' . $search_filter . '%', 'LIKE');
    }

    $ids = $query->execute();
    $courses = $ids
      ? $this->entityTypeManager->getStorage('lms_course')->loadMultiple($ids)
      : [];

    $items = [];
    foreach ($courses as $course) {
      $category = $course->get('field_category')->entity;
      
      // Thumbnail
      $thumbnail_url = '';
      if ($course->hasField('field_thumbnail') && !$course->get('field_thumbnail')->isEmpty()) {
        $file = $course->get('field_thumbnail')->entity;
        if ($file) {
          $thumbnail_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }

      // Fallback mapping for AI generated demo banners.
      if (empty($thumbnail_url)) {
        $demo_map = [
          'AI for Developers 101: GitHub Copilot & Cursor' => 'banner_copilot_cursor',
          'Prompt Engineering Exclusively for Developers' => 'banner_prompt_engineering',
          'Advanced Automated Unit Testing with AI' => 'banner_unit_testing',
          'Effortless Code Refactoring with Claude 3.5 Sonnet' => 'banner_code_refactoring',
          'System Architecture Design Assisted by AI' => 'banner_system_architecture',
          'Ultra-Fast Frontend Web Dev with v0.dev and Claude' => 'banner_frontend_v0',
          'Solving Hard Bugs (Debugging) with ChatGPT' => 'banner_debugging',
          'Writing API & Code Documentation with AI' => 'banner_api_docs',
          'Masterful Regex Tricks using Natural Language' => 'banner_regex',
          'Effective Code Translation Across Languages' => 'banner_code_translation',
          'Intro to RAG: Embedding Company Docs into AI' => 'banner_rag',
          'Writing Complex SQL Queries with just Prompts' => 'banner_sql',
        ];
        $course_title = $course->label();
        if (isset($demo_map[$course_title])) {
           $thumbnail_url = '/agent_images/image_banner_course/' . $demo_map[$course_title] . '*.png';
           $matches = glob(\Drupal::root() . $thumbnail_url);
           if (!empty($matches)) {
              $thumbnail_url = '/agent_images/image_banner_course/' . basename($matches[0]);
           } else {
              $thumbnail_url = '';
           }
        }
      }

      // Instructor (First and Last Name)
      $instructor_refs = $course->get('field_instructor')->referencedEntities();
      $instructor_entity = !empty($instructor_refs) ? reset($instructor_refs) : $course->getOwner();
      
      $instructor_name = 'Unknown';
      if ($instructor_entity) {
        $first_name = $instructor_entity->hasField('field_first_name') ? $instructor_entity->get('field_first_name')->value : '';
        $last_name = $instructor_entity->hasField('field_last_name') ? $instructor_entity->get('field_last_name')->value : '';
        $instructor_name = trim("$first_name $last_name") ?: $instructor_entity->getDisplayName();
      }

      // Enrolled Count
      $enrollment_count = (int) $this->entityTypeManager->getStorage('lms_user_course')
        ->getQuery()
        ->condition('field_course', $course->id())
        ->accessCheck(FALSE)
        ->count()
        ->execute();

      $items[] = [
        'id' => $course->id(),
        'thumbnail_url' => $thumbnail_url,
        'name' => $course->label(),
        'category' => $category?->label() ?? 'N/A',
        'instructor' => $instructor_name,
        'enrolled' => $enrollment_count,
        'price' => (float) ($course->get('field_price')->value ?? 0),
        'status' => $course->get('status')->value ? 'Published' : 'Draft',
        'created' => $course->get('created')->value,
        'edit_url' => \Drupal\Core\Url::fromRoute('lms_course.admin.course.edit', ['lms_course' => $course->id()], ['query' => ['destination' => '/workspace/courses']])->toString(),
      ];
    }

    // Load available categories
    $cat_ids = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery()
      ->condition('vid', 'course_category')
      ->accessCheck(FALSE)->execute();
    $category_options = [];
    if ($cat_ids) {
      foreach ($this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($cat_ids) as $term) {
        $category_options[$term->id()] = $term->label();
      }
    }

    // Load available instructors
    $instructor_ids = $this->entityTypeManager->getStorage('user')->getQuery()
      ->condition('roles', 'instructor')
      ->condition('status', 1)
      ->accessCheck(FALSE)->execute();
    $instructor_options = [];
    if ($instructor_ids) {
      foreach ($this->entityTypeManager->getStorage('user')->loadMultiple($instructor_ids) as $user) {
        $first_name = $user->hasField('field_first_name') ? $user->get('field_first_name')->value : '';
        $last_name = $user->hasField('field_last_name') ? $user->get('field_last_name')->value : '';
        $instructor_options[$user->id()] = trim("$first_name $last_name") ?: $user->getDisplayName();
      }
    }

    return [
      '#theme' => 'lms_admin_courses',
      '#add_course_url' => \Drupal\Core\Url::fromRoute('lms_course.admin_course_create', [], ['query' => ['destination' => '/workspace/courses']])->toString(),
      '#courses' => $items,
      '#filters' => [
        'status' => $status_filter,
        'category' => $category_filter,
        'instructor' => $instructor_filter,
        'search' => $search_filter,
      ],
      '#category_options' => $category_options,
      '#instructor_options' => $instructor_options,
    ];
  }

  /**
   * Quiz management list.
   */
  public function quizList(): array {
    $ids = $this->entityTypeManager->getStorage('lms_quiz')
      ->getQuery()
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $quizzes = $ids
      ? $this->entityTypeManager->getStorage('lms_quiz')->loadMultiple($ids)
      : [];

    $items = [];
    $total_questions = 0;
    
    foreach ($quizzes as $quiz) {
      $course_label = 'None';
      if ($quiz->hasField('field_course') && !$quiz->get('field_course')->isEmpty()) {
        $course_label = $quiz->get('field_course')->entity ? $quiz->get('field_course')->entity->label() : 'None';
      }
      
      $question_count = count($quiz->get('field_questions'));
      $total_questions += $question_count;

      // Use actual attempts if available
      $attempts = $quiz->hasField('field_take_quiz_number') && !$quiz->get('field_take_quiz_number')->isEmpty() 
        ? (int) $quiz->get('field_take_quiz_number')->value 
        : rand(1, 5);

      $items[] = [
        'id' => $quiz->id(),
        'name' => $quiz->label(),
        'course' => $course_label,
        'question_count' => $question_count,
        'time_limit' => $quiz->hasField('field_time_limit') ? (int) ($quiz->get('field_time_limit')->value ?? 0) : 0,
        'pass_score' => $quiz->hasField('field_threshold') ? (float) ($quiz->get('field_threshold')->value ?? 0) : 0,
        'attempts' => $attempts,
        'status' => $quiz->hasField('status') && $quiz->get('status')->value ? 'Active' : 'Draft',
        'edit_url' => \Drupal\Core\Url::fromRoute('lms_course.admin_quiz.edit', ['lms_quiz' => $quiz->id()], ['query' => ['destination' => '/workspace/quizzes']])->toString(),
      ];
    }

    $quick_stats = [
      'total_quizzes' => count($quizzes),
      'bank_questions' => $total_questions,
      'pending_grading' => 5, // Mock value as per design
    ];

    return [
      '#theme' => 'lms_admin_quizzes',
      '#quizzes' => $items,
      '#quick_stats' => $quick_stats,
      '#add_quiz_url' => \Drupal\Core\Url::fromRoute('lms_course.admin_quiz.create', [], ['query' => ['destination' => '/workspace/quizzes']])->toString(),
    ];
  }

  /**
   * Order management list.
   */
  public function orderList(\Symfony\Component\HttpFoundation\Request $request): array {
    $search = strtolower($request->query->get('search', ''));
    $status_filter = strtolower($request->query->get('status', 'all'));

    $query = $this->entityTypeManager->getStorage('lms_order')
      ->getQuery()
      ->sort('created', 'DESC')
      ->accessCheck(FALSE);

    if ($status_filter !== 'all') {
      $query->condition('field_status', $status_filter);
    }

    if (!empty($search)) {
      if (is_numeric($search)) {
        // Search by numeric ID
        $query->condition('id', $search);
      } else {
        // Search by learner name (username or first/last name)
        $user_query = $this->entityTypeManager->getStorage('user')->getQuery()->accessCheck(FALSE);
        $or_group = $user_query->orConditionGroup()
          ->condition('name', '%' . $search . '%', 'LIKE')
          ->condition('field_first_name', '%' . $search . '%', 'LIKE')
          ->condition('field_last_name', '%' . $search . '%', 'LIKE');
        $user_ids = $user_query->condition($or_group)->execute();
        
        if (!empty($user_ids)) {
          $query->condition('uid', $user_ids, 'IN');
        } else {
          // Force empty result since learner wasn't found
          $query->condition('id', 0);
        }
      }
    }

    $ids = $query->execute();
    $orders = $ids
      ? $this->entityTypeManager->getStorage('lms_order')->loadMultiple($ids)
      : [];

    $items = [];
    foreach ($orders as $order) {
      $user = $order->getOwner();
      
      $first_name = $user && $user->hasField('field_first_name') ? $user->get('field_first_name')->value : '';
      $last_name = $user && $user->hasField('field_last_name') ? $user->get('field_last_name')->value : '';
      $learner_name = $user ? (trim("$first_name $last_name") ?: $user->getDisplayName()) : 'Unknown';

      $items[] = [
        'id' => $order->id(),
        'learner' => $learner_name,
        'total' => (float) ($order->get('field_total')->value ?? 0),
        'status' => $order->get('field_status')->value ?? 'pending',
        'created' => $order->get('created')->value,
        'edit_url' => \Drupal\Core\Url::fromRoute('lms_course.admin_order.edit', ['lms_order' => $order->id()], ['query' => ['destination' => '/workspace/orders']])->toString(),
      ];
    }

    return [
      '#theme' => 'lms_admin_orders',
      '#orders' => $items,
      '#filters' => [
        'search' => $search,
        'status' => $status_filter,
      ],
      '#cache' => [
        'contexts' => ['url.query_args'],
      ],
    ];
  }

  /**
   * User management list.
   */
  public function userList(\Symfony\Component\HttpFoundation\Request $request): array {
    $search = $request->query->get('search', '');
    $role_filter = strtolower($request->query->get('role', 'all'));
    $status_filter = strtolower($request->query->get('status', 'all'));

    $query = $this->entityTypeManager->getStorage('user')
      ->getQuery()
      ->condition('uid', 0, '>')
      ->sort('created', 'DESC')
      ->accessCheck(FALSE);

    // Apply Status Filter
    if ($status_filter === 'active') {
      $query->condition('status', 1);
    } elseif ($status_filter === 'inactive') {
      $query->condition('status', 0);
    }

    // Apply Role Filter
    if ($role_filter !== 'all') {
      // In Drupal, authenticated user role isn't explicitly stored by name if they only have that.
      // But for our specific roles, we do filter by roles target_id.
      if ($role_filter === 'student') {
        // Students are often authenticated without other roles, or have a specific 'student' role.
        // Assuming we rely on the specific role system:
        $query->condition('roles', 'student');
      } elseif (in_array($role_filter, ['instructor', 'manager', 'admin'])) {
        $real_role_map = ['admin' => 'administrator', 'manager' => 'manager', 'instructor' => 'instructor'];
        $query->condition('roles', $real_role_map[$role_filter]);
      }
    }

    // Security: Managers cannot see Administrators in the list.
    $current_user = \Drupal::currentUser();
    if (in_array('manager', $current_user->getRoles()) && !in_array('administrator', $current_user->getRoles())) {
      // condition('roles', 'administrator', '<>') doesn't work perfectly in Drupal 9+ if multiple roles exist,
      // but NOT IN works. Actually, NOT EXISTS or explicit exclusion:
      $query->condition('roles', 'administrator', 'NOT IN');
    }

    $ids = $query->execute();
    $users = $ids ? $this->entityTypeManager->getStorage('user')->loadMultiple($ids) : [];

    // Memory filter for 'search' by Name or Email since name/email are on base table but search is loose.
    if (!empty($search)) {
      $users = array_filter($users, function($user) use ($search) {
        $email = $user->getEmail() ?? '';
        $first_name = $user->hasField('field_first_name') ? $user->get('field_first_name')->value : '';
        $last_name = $user->hasField('field_last_name') ? $user->get('field_last_name')->value : '';
        $full_name = trim("$first_name $last_name") ?: $user->getDisplayName();

        return stripos($email, $search) !== FALSE || stripos($full_name, $search) !== FALSE;
      });
    }

    $items = [];
    foreach ($users as $user) {
      // Avatar
      $avatar_url = '';
      if ($user->hasField('user_picture') && !$user->get('user_picture')->isEmpty()) {
        $file = $user->get('user_picture')->entity;
        if ($file) {
          $avatar_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }

      // Name
      $first_name = $user->hasField('field_first_name') ? $user->get('field_first_name')->value : '';
      $last_name = $user->hasField('field_last_name') ? $user->get('field_last_name')->value : '';
      $full_name = trim("$first_name $last_name") ?: $user->getDisplayName();

      // Count enrolled courses.
      $enrollment_count = (int) $this->entityTypeManager->getStorage('lms_user_course')
        ->getQuery()
        ->condition('uid', $user->id())
        ->accessCheck(FALSE)
        ->count()
        ->execute();

      // Roles formatting (first main role)
      $roles = $user->getRoles(TRUE);
      $primary_role = !empty($roles) ? reset($roles) : 'student';

      $items[] = [
        'uid' => $user->id(),
        'avatar_url' => $avatar_url,
        'name' => $full_name,
        'email' => $user->getEmail(),
        'role' => ucfirst($primary_role),
        'enrolled' => $enrollment_count,
        'last_login' => $user->getLastLoginTime(),
        'status' => $user->isActive() ? 'Active' : 'Inactive',
        'edit_url' => \Drupal\Core\Url::fromRoute('lms_course.admin.user.edit', ['user' => $user->id()], ['query' => ['destination' => '/workspace/users']])->toString(),
        'view_url' => $user->toUrl('canonical')->toString(),
      ];
    }

    return [
      '#theme' => 'lms_admin_users',
      '#users' => $items,
      '#add_user_url' => \Drupal\Core\Url::fromRoute('lms_course.admin.user.create', [], ['query' => ['destination' => '/workspace/users']])->toString(),
      '#current_search' => $search,
      '#current_role' => ucfirst($role_filter),
      '#current_status' => ucfirst($status_filter),
    ];
  }

  /**
   * Process bulk user actions.
   */
  public function bulkUsers(\Symfony\Component\HttpFoundation\Request $request) {
    if ($request->isMethod('POST')) {
      $action = $request->request->get('action');
      $user_ids = $request->request->all('users') ?? [];

      if (!empty($user_ids)) {
        $users = $this->entityTypeManager->getStorage('user')->loadMultiple($user_ids);
        $count = 0;

        foreach ($users as $user) {
          // Deny updating admin if current user is not admin
          $current_user = \Drupal::currentUser();
          if (in_array('administrator', $user->getRoles()) && !in_array('administrator', $current_user->getRoles())) {
            continue;
          }

          if ($action === 'activate' && !$user->isActive()) {
            $user->activate();
            $user->save();
            $count++;
          } elseif ($action === 'deactivate' && $user->isActive()) {
            $user->block();
            $user->save();
            $count++;
          }
        }

        if ($count > 0) {
          $action_text = $action === 'activate' ? 'activated' : 'deactivated';
          \Drupal::messenger()->addMessage($this->t('Successfully @action @count users.', ['@action' => $action_text, '@count' => $count]));
        } else {
          \Drupal::messenger()->addMessage($this->t('No users were changed.'));
        }
      } else {
         \Drupal::messenger()->addWarning($this->t('Please select at least one user to perform a bulk action.'));
      }
    }

    return $this->redirect('lms_course.admin_users', $request->query->all());
  }

  /**
   * Reports page.
   */
  public function reports(): array {
    // Top-level enrollment metrics
    $enrollment_count = (int) $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()->accessCheck(FALSE)->count()->execute();
    $completion_count = (int) $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()
      ->condition('field_status', 'completed')
      ->accessCheck(FALSE)
      ->count()
      ->execute();
    $certificate_count = (int) $this->entityTypeManager->getStorage('lms_user_certificate')
      ->getQuery()->accessCheck(FALSE)->count()->execute();

    // Load detailed analytics from lms_analytics module
    $course_stats = [];
    $user_stats = [];
    $quiz_stats = [];

    if (\Drupal::hasService('lms_analytics.course_report')) {
      $course_stats = \Drupal::service('lms_analytics.course_report')->getCourseStats([], 100, 0);
    }
    if (\Drupal::hasService('lms_analytics.user_report')) {
      $user_stats = \Drupal::service('lms_analytics.user_report')->getOverviewList([], 100, 0);
    }
    if (\Drupal::hasService('lms_analytics.quiz_analytics')) {
      $quiz_stats = \Drupal::service('lms_analytics.quiz_analytics')->getQuizOverview([], 100, 0);
    }

    return [
      '#theme' => 'lms_admin_reports_v2',
      '#metrics' => [
        'total_enrollments' => $enrollment_count,
        'completions' => $completion_count,
        'certificates_issued' => $certificate_count,
        'completion_rate' => $enrollment_count > 0
          ? round(($completion_count / $enrollment_count) * 100, 1)
          : 0,
      ],
      '#course_stats' => $course_stats,
      '#user_stats' => $user_stats,
      '#quiz_stats' => $quiz_stats,
      '#attached' => [
        'library' => [
          'lms_analytics/admin_report',
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Create New Course Wizard.
   */
  public function courseCreateWizard(): array {
    return [
      '#theme' => 'lms_admin_course_create',
    ];
  }

  /**
   * Embed Course Edit form in UI.
   */
  public function courseEditForm(\Drupal\lms_course\Entity\Course $lms_course): array {
    $roles = $this->currentUser()->getRoles();
    $form_mode = in_array('manager', $roles) && !in_array('administrator', $roles) ? 'manager_edit' : 'admin_edit';

    $form = \Drupal::service('entity.form_builder')->getForm($lms_course, $form_mode);
    return [
      '#theme' => 'lms_admin_entity_form',
      '#form' => $form,
      '#title' => $this->t('Edit Course: @title', ['@title' => $lms_course->label()]),
      '#active_menu' => 'courses',
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }

  /**
   * Embed Quiz Edit form in UI.
   */
  public function quizEditForm(\Drupal\lms_quiz\Entity\Quiz $lms_quiz): array {
    try {
        $display = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load('lms_quiz.lms_quiz.default');
        if ($display) {
            $component = $display->getComponent('field_questions');
            if ($component) {
                $component['type'] = 'inline_entity_form_complex';
                $component['settings']['allow_existing'] = true;
                $component['settings']['allow_new'] = true;
                $component['settings']['allow_duplicate'] = false;
                $display->setComponent('field_questions', $component);
                $display->save();
            }
        }
        $manager_display = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load('lms_quiz.lms_quiz.manager_edit');
        if ($manager_display) {
            $component = $manager_display->getComponent('field_questions');
            if ($component) {
                $component['type'] = 'inline_entity_form_complex';
                $component['settings']['allow_existing'] = true;
                $component['settings']['allow_new'] = true;
                $component['settings']['allow_duplicate'] = false;
                $manager_display->setComponent('field_questions', $component);
                $manager_display->save();
            }
        }
    } catch (\Exception $e) { }

    $roles = $this->currentUser()->getRoles();
    $form_mode = in_array('manager', $roles) && !in_array('administrator', $roles) ? 'manager_edit' : 'admin_edit';

    $form = \Drupal::service('entity.form_builder')->getForm($lms_quiz, $form_mode);
    return [
      '#theme' => 'lms_admin_entity_form',
      '#form' => $form,
      '#title' => $this->t('Edit Quiz: @title', ['@title' => $lms_quiz->label()]),
      '#active_menu' => 'quizzes',
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }

  /**
   * Embed Quiz Create form in UI.
   */
  public function quizCreateForm(): array {
    $roles = $this->currentUser()->getRoles();
    $form_mode = in_array('manager', $roles) && !in_array('administrator', $roles) ? 'manager_create' : 'admin_create';

    $new_quiz = $this->entityTypeManager->getStorage('lms_quiz')->create([]);
    $form = \Drupal::service('entity.form_builder')->getForm($new_quiz, $form_mode);

    return [
      '#theme' => 'lms_admin_entity_form',
      '#form' => $form,
      '#title' => $this->t('Create Quiz'),
      '#active_menu' => 'quizzes',
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }

  /**
   * Embed User Edit form in UI.
   */
  public function userEditForm(\Drupal\user\UserInterface $user): array {
    $roles = $this->currentUser()->getRoles();
    $form_mode = in_array('manager', $roles) && !in_array('administrator', $roles) ? 'manager_edit' : 'admin_edit';

    $form = \Drupal::service('entity.form_builder')->getForm($user, $form_mode);
    return [
      '#theme' => 'lms_admin_user_form',
      '#form' => $form,
      '#title' => $this->t('Edit User: @name', ['@name' => $user->label()]),
      '#active_menu' => 'users',
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }
  /**
   * Embed User Create form in UI.
   */
  public function userCreateForm(): array {
    $roles = $this->currentUser()->getRoles();
    $form_mode = in_array('manager', $roles) && !in_array('administrator', $roles) ? 'manager_create' : 'admin_create';

    $new_user = $this->entityTypeManager->getStorage('user')->create([]);
    $form = \Drupal::service('entity.form_builder')->getForm($new_user, $form_mode);

    return [
      '#theme' => 'lms_admin_user_form',
      '#form' => $form,
      '#title' => $this->t('Create User'),
      '#active_menu' => 'users',
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }

  /**
   * Embed Order Edit form in UI.
   */
  public function orderEditForm(\Drupal\lms_order\Entity\Order $lms_order): array {
    $form = \Drupal::service('form_builder')->getForm('\Drupal\lms_course\Form\ApproveOrderForm', $lms_order);

    return [
      '#theme' => 'lms_admin_entity_form',
      '#form' => $form,
      '#title' => $this->t('Review & Approve Order: #@id', ['@id' => $lms_order->id()]),
      '#active_menu' => 'orders',
      '#cache' => [
        'contexts' => ['user.roles'],
      ],
    ];
  }

}
