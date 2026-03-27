<?php

declare(strict_types=1);

namespace Drupal\lms_course\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_base\Service\LmsHelper;
use Drupal\lms_course\Entity\Course;
use Drupal\taxonomy\TermInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Public-facing course pages controller.
 */
final class CoursePublicController extends ControllerBase {

  /**
   * The LMS helper service.
   */
  protected LmsHelper $lmsHelper;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LmsHelper $lms_helper,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->lmsHelper = $lms_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('lms_base.helper'),
    );
  }

  /**
   * Homepage.
   */
  public function homepage(): array {
    // Featured courses.
    $featured = $this->entityTypeManager->getStorage('lms_course')
      ->getQuery()
      ->condition('field_featured', TRUE)
      ->condition('status', TRUE)
      ->range(0, 8)
      ->accessCheck(FALSE)
      ->execute();
    $featured_courses = $featured
      ? $this->entityTypeManager->getStorage('lms_course')->loadMultiple($featured)
      : [];

    // Categories with course count and thumbnail.
    $categories = $this->lmsHelper->getTermsByVocabulary('course_category');
    $top_term_objects = array_filter($categories, fn($term) => empty($term->parents) || $term->parents === [0] || (is_array($term->parents) && $term->parents[0] == 0));
    $top_term_objects = array_slice($top_term_objects, 0, 8);
    
    $top_categories = [];
    $course_storage = $this->entityTypeManager->getStorage('lms_course');
    foreach ($top_term_objects as $term) {
      $count = $course_storage->getQuery()
        ->condition('field_category', $term->id())
        ->condition('status', TRUE)
        ->accessCheck(FALSE)
        ->count()
        ->execute();

      $thumbnail_url = '';
      if ($term->hasField('field_category_thumbnail') && !$term->get('field_category_thumbnail')->isEmpty()) {
        $file = $term->get('field_category_thumbnail')->entity;
        if ($file) {
           $thumbnail_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }
      
      $top_categories[] = [
        'entity' => $term,
        'name' => $term->label(),
        'course_count' => $count,
        'thumbnail_url' => $thumbnail_url,
      ];
    }

    // Stats.
    $course_count = (int) $course_storage->getQuery()
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    // User Progress (My Courses) - 3 items max.
    $my_courses = [];
    $user = \Drupal::currentUser();
    if ($user->isAuthenticated()) {
      $uid = $user->id();
      $enrollment_ids = $this->entityTypeManager->getStorage('lms_user_course')
        ->getQuery()
        ->condition('uid', $uid)
        ->sort('id', 'DESC')
        ->range(0, 3)
        ->accessCheck(FALSE)
        ->execute();
        
      if ($enrollment_ids) {
        $enrollments = $this->entityTypeManager->getStorage('lms_user_course')->loadMultiple($enrollment_ids);
        foreach ($enrollments as $enrollment) {
          $course = $enrollment->get('field_course')->entity;
          if ($course) {
            $progress = 0;
            if ($enrollment->hasField('field_progress') && !$enrollment->get('field_progress')->isEmpty()) {
              $progress = (int) $enrollment->get('field_progress')->value;
            }
            $card = $this->buildCourseCards([$course])[0] ?? null;
            if ($card) {
              $my_courses[] = [
                'course' => $card,
                'progress' => $progress,
              ];
            }
          }
        }
      }
    }

    // Popular Bundles.
    $bundles = [];
    if (\Drupal::entityTypeManager()->hasDefinition('lms_bundle')) {
      $bundle_ids = \Drupal::entityTypeManager()->getStorage('lms_bundle')
        ->getQuery()
        ->condition('status', TRUE)
        ->range(0, 4)
        ->accessCheck(FALSE)
        ->execute();
        
      if ($bundle_ids) {
        $bundles = \Drupal::entityTypeManager()->getStorage('lms_bundle')->loadMultiple($bundle_ids);
      }
    }

    // Mock Testimonials (since entity does not exist yet)
    $testimonials = [
      [
        'name' => 'Sarah Nguyen',
        'role' => 'Software Engineer',
        'content' => 'Khóa học cực kỳ sát với thực tế doanh nghiệp. Mình đã ứng dụng bài giảng vào công việc ngay trong ngày đầu tiên.',
        'rating' => 5,
        'avatar' => 'https://i.pravatar.cc/150?u=sarah' 
      ],
      [
        'name' => 'Michael Tran',
        'role' => 'Data Analyst',
        'content' => 'Giao diện cực kỳ đẹp và trải nghiệm học tập tuyệt vời. Tiến trình học được theo dõi chi tiết.',
        'rating' => 5,
        'avatar' => 'https://i.pravatar.cc/150?u=michael'
      ],
      [
        'name' => 'Emily Le',
        'role' => 'UX/UI Designer',
        'content' => 'Tôi rất ấn tượng với chất lượng khóa học và hệ thống đánh giá. Đây là một trải nghiệm học tập hoàn hảo.',
        'rating' => 4,
        'avatar' => 'https://i.pravatar.cc/150?u=emily'
      ]
    ];

    return [
      '#theme' => 'lms_homepage',
      '#featured_courses' => $this->buildCourseCards($featured_courses),
      '#categories' => $top_categories,
      '#my_courses' => $my_courses,
      '#bundles' => $bundles,
      '#testimonials' => $testimonials,
      '#logged_in' => $user->isAuthenticated(),
      '#stats' => [
        'courses' => $course_count ?: '10+ ',
        'students' => '5.000+',
        'satisfaction' => '98%',
      ],
      '#attached' => [
        'library' => [
          // Will define this library for homepage aesthetics
          'lms_theme/homepage',
        ],
      ],
    ];
  }

  /**
   * Course catalog.
   */
  public function catalog(\Symfony\Component\HttpFoundation\Request $request): array {
    $search = trim($request->query->get('search', ''));
    $query_all = $request->query->all();

    $categories_input = $query_all['category'] ?? [];
    $categories = is_array($categories_input) ? $categories_input : [$categories_input];
    $categories = array_filter($categories);

    $levels_input = $query_all['level'] ?? [];
    $levels = is_array($levels_input) ? $levels_input : [$levels_input];
    $levels = array_filter($levels);

    $prices_input = $query_all['price'] ?? [];
    $prices = is_array($prices_input) ? $prices_input : [$prices_input];
    $prices = array_filter($prices);

    $ratings_input = $query_all['rating'] ?? [];
    $ratings = is_array($ratings_input) ? $ratings_input : [$ratings_input];
    $ratings = array_filter($ratings);

    $sort = $request->query->get('sort', 'Most Popular');

    $query = $this->entityTypeManager->getStorage('lms_course')
      ->getQuery()
      ->condition('status', TRUE)
      ->accessCheck(FALSE);

    if ($search !== '') {
      $query->condition('title', $search, 'CONTAINS');
    }

    if (!empty($categories)) {
      $query->condition('field_category', $categories, 'IN');
    }

    if (!empty($levels)) {
      $level_terms = $this->lmsHelper->getTermsByVocabulary('course_level');
      $level_tids = [];
      foreach ($level_terms as $term) {
        if (in_array(strtolower($term->label()), $levels)) {
          $level_tids[] = $term->id();
        }
      }
      if (!empty($level_tids)) {
        $query->condition('field_level', $level_tids, 'IN');
      }
    }

    if (!empty($prices) && count($prices) < 2) {
      if (in_array('free', $prices)) {
        $query->condition('field_price', 0);
      }
      elseif (in_array('paid', $prices)) {
        $query->condition('field_price', 0, '>');
      }
    }

    if (!empty($ratings)) {
      $rating_group = $query->orConditionGroup();
      foreach ($ratings as $r) {
        $r = (int) $r;
        $and = $query->andConditionGroup()
          ->condition('field_rating', $r, '>=')
          ->condition('field_rating', $r + 1, '<');
        $rating_group->condition($and);
      }
      $query->condition($rating_group);
    }

    if ($sort === 'Newest') {
      $query->sort('created', 'DESC');
    } elseif ($sort === 'Price Low-High') {
      $query->sort('field_price', 'ASC');
    } else {
      $query->sort('id', 'DESC');
    }

    $query->pager(12);
    $ids = $query->execute();

    $courses = $ids
      ? $this->entityTypeManager->getStorage('lms_course')->loadMultiple($ids)
      : [];

    $all_categories = $this->lmsHelper->getTermsByVocabulary('course_category');

    return [
      '#theme' => 'lms_catalog',
      '#courses' => $this->buildCourseCards($courses),
      '#categories' => $all_categories,
      '#pager' => ['#type' => 'pager'],
      '#filters' => [
        'search' => $search,
        'category' => $categories,
        'level' => $levels,
        'price' => $prices,
        'rating' => $ratings,
        'sort' => $sort,
      ],
      '#cache' => [
        'contexts' => ['url.query_args'],
      ],
    ];
  }

  /**
   * Course detail page.
   */
  public function detail(Course $lms_course): array {
    // Load chapters.
    $chapters = [];
    if (!$lms_course->get('field_chapters')->isEmpty()) {
      foreach ($lms_course->get('field_chapters')->referencedEntities() as $chapter) {
        if ($chapter->get('status')->value) {
          $chapters[] = $chapter;
        }
      }
    }

    // Load lessons per chapter.
    $curriculum = [];
    foreach ($chapters as $chapter) {
      $lessons = [];
      if (!$chapter->get('field_lessons')->isEmpty()) {
        foreach ($chapter->get('field_lessons')->referencedEntities() as $lesson) {
          if ($lesson->get('status')->value) {
            $lessons[] = $lesson;
          }
        }
      }
      $curriculum[] = [
        'chapter' => $chapter,
        'lessons' => $lessons,
      ];
    }

    // Category & level names.
    $category = $lms_course->get('field_category')->entity;
    $level = $lms_course->get('field_level')->entity;

    // Instructor.
    $instructor_refs = $lms_course->get('field_instructor')->referencedEntities();
    $instructor_entity = !empty($instructor_refs) ? reset($instructor_refs) : $lms_course->getOwner();

    $instructor_name = $instructor_entity ? $instructor_entity->getDisplayName() : '';
    if ($instructor_entity) {
      $first_name = $instructor_entity->hasField('field_first_name') ? $instructor_entity->get('field_first_name')->value : '';
      $last_name = $instructor_entity->hasField('field_last_name') ? $instructor_entity->get('field_last_name')->value : '';
      $full_name = trim("$first_name $last_name");
      if (!empty($full_name)) {
        $instructor_name = $full_name;
      }
    }
    $instructor_url = $instructor_entity ? \Drupal\Core\Url::fromRoute('lms_course.instructor_profile', ['user' => $instructor_entity->id()])->toString() : '';

    // Stats.
    $enrolled_students = $this->entityTypeManager->getStorage('lms_user_course')
      ->getQuery()
      ->condition('field_course', $lms_course->id())
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    $instructor_courses_count = 0;
    $instructor_students_count = 0;

    if ($instructor_entity) {
      $instructor_course_ids = $this->entityTypeManager->getStorage('lms_course')
        ->getQuery()
        ->condition('field_instructor', $instructor_entity->id())
        ->condition('status', TRUE)
        ->accessCheck(FALSE)
        ->execute();

      $instructor_courses_count = count($instructor_course_ids);

      if (!empty($instructor_course_ids)) {
        $instructor_students_count = $this->entityTypeManager->getStorage('lms_user_course')
          ->getQuery()
          ->condition('field_course', array_values($instructor_course_ids), 'IN')
          ->accessCheck(FALSE)
          ->count()
          ->execute();
      }
    }

    // FAQs.
    $faq_ids = $this->entityTypeManager->getStorage('lms_faq')
      ->getQuery()
      ->condition('field_course', $lms_course->id())
      ->sort('weight')
      ->accessCheck(FALSE)
      ->execute();
    $faqs = $faq_ids
      ? $this->entityTypeManager->getStorage('lms_faq')->loadMultiple($faq_ids)
      : [];

    $thumbnail_url = '';
    if ($lms_course->hasField('field_thumbnail') && !$lms_course->get('field_thumbnail')->isEmpty()) {
      $file = $lms_course->get('field_thumbnail')->entity;
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
      $course_title = $lms_course->label();
      if (isset($demo_map[$course_title])) {
         $thumbnail_url = '/agent_images/image_banner_course/' . $demo_map[$course_title] . '*.png';
         $matches = glob(\Drupal::root() . $thumbnail_url);
         if (!empty($matches)) {
            $thumbnail_url = '/agent_images/image_banner_course/' . basename($matches[0]);
         } else {
            $thumbnail_url = '';
         }
         \Drupal::logger('lms_course')->notice('Course: ' . $course_title . ' Banner resolves to: ' . (empty($thumbnail_url) ? 'EMPTY' : $thumbnail_url) . ' Matches: ' . print_r($matches, TRUE) . ' Path Checked: ' . \Drupal::root() . '/agent_images/image_banner_course/' . $demo_map[$course_title] . '*.png');
      }
    }

    $user = $this->currentUser();
    $logged_in = $user->isAuthenticated();
    $is_enrolled = FALSE;
    $continue_url = '';

    if ($logged_in) {
      $uid = (int) $user->id();
      
      // Check membership first.
      $has_membership = \Drupal::moduleHandler()->moduleExists('lms_membership') && \Drupal::service('lms_membership.manager')->hasActiveMembership($user);
      
      if ($has_membership) {
        $is_enrolled = TRUE;
      } else {
        $enrollment_storage = $this->entityTypeManager->getStorage('lms_user_course');
        $enrollments = $enrollment_storage->getQuery()
          ->condition('uid', $uid)
          ->condition('field_course', $lms_course->id())
          ->accessCheck(FALSE)
          ->execute();
        if (!empty($enrollments)) {
          $is_enrolled = TRUE;
        }
      }

      if ($is_enrolled) {

        // Calculate continue URL: improved logic for last accessed lesson.
        $continue_url = $lms_course->toUrl()->toString();
        if (!$lms_course->get('field_chapters')->isEmpty()) {
           $course_lesson_ids = [];
           foreach ($lms_course->get('field_chapters')->referencedEntities() as $chapter) {
             if ($chapter->hasField('field_lessons') && !$chapter->get('field_lessons')->isEmpty()) {
               foreach ($chapter->get('field_lessons')->referencedEntities() as $lesson) {
                 if ($lesson->get('status')->value) {
                   $course_lesson_ids[] = $lesson->id();
                 }
               }
             }
           }

           if (!empty($course_lesson_ids)) {
             $target_lesson_id = NULL;

             $last_progress = $this->entityTypeManager->getStorage('lms_user_lesson')
               ->getQuery()
               ->condition('uid', $uid)
               ->condition('field_lesson', $course_lesson_ids, 'IN')
               ->sort('id', 'DESC')
               ->range(0, 1)
               ->accessCheck(FALSE)
               ->execute();

             if ($last_progress) {
               $progress_record = $this->entityTypeManager->getStorage('lms_user_lesson')->load(reset($last_progress));
               if ($progress_record) {
                 $last_lesson_id = $progress_record->get('field_lesson')->target_id;
                 if ($progress_record->get('field_status')->value !== 'completed') {
                   $target_lesson_id = $last_lesson_id;
                 } else {
                   $found = FALSE;
                   foreach ($course_lesson_ids as $lid) {
                     if ($found) {
                       $target_lesson_id = $lid;
                       break;
                     }
                     if ($lid == $last_lesson_id) {
                       $found = TRUE;
                     }
                   }
                 }
               }
             }

             if (!$target_lesson_id) {
               $completed_progress = $this->entityTypeManager->getStorage('lms_user_lesson')
                 ->getQuery()
                 ->condition('uid', $uid)
                 ->condition('field_lesson', $course_lesson_ids, 'IN')
                 ->condition('field_status', 'completed')
                 ->accessCheck(FALSE)
                 ->execute();
               
               $completed_lesson_ids = [];
               if ($completed_progress) {
                 foreach ($this->entityTypeManager->getStorage('lms_user_lesson')->loadMultiple($completed_progress) as $record) {
                    $completed_lesson_ids[] = $record->get('field_lesson')->target_id;
                 }
               }
               foreach ($course_lesson_ids as $lid) {
                 if (!in_array($lid, $completed_lesson_ids)) {
                   $target_lesson_id = $lid;
                   break;
                 }
               }
             }

             if (!$target_lesson_id) {
               $target_lesson_id = $course_lesson_ids[0];
             }

             if ($target_lesson_id) {
                $continue_url = \Drupal\Core\Url::fromRoute('lms_progress.lesson_view', [
                  'lms_course' => $lms_course->id(), 
                  'lms_lesson' => $target_lesson_id
                ])->toString();
             }
           }
        }
      }
    }

    return [
      '#theme' => 'lms_course_detail',
      '#course' => $lms_course,
      '#thumbnail_url' => $thumbnail_url,
      '#banner_url' => $thumbnail_url,
      '#curriculum' => $curriculum,
      '#category' => $category,
      '#level' => $level,
      '#instructor' => $instructor_entity,
      '#instructor_name' => $instructor_name,
      '#instructor_url' => $instructor_url,
      '#enrolled_students' => $enrolled_students,
      '#instructor_courses_count' => $instructor_courses_count,
      '#instructor_students_count' => $instructor_students_count,
      '#faqs' => $faqs,
      '#logged_in' => $logged_in,
      '#is_enrolled' => $is_enrolled,
      '#continue_url' => $continue_url,
    ];
  }

  /**
   * Instructor profile page.
   */
  public function instructorProfile(UserInterface $user): array {
    $first_name = $user->hasField('field_first_name') ? $user->get('field_first_name')->value : '';
    $last_name = $user->hasField('field_last_name') ? $user->get('field_last_name')->value : '';
    $full_name = trim("$first_name $last_name");
    $instructor_name = $full_name ?: $user->getDisplayName();

    $instructor_avatar = '';
    if ($user->hasField('user_picture') && !$user->get('user_picture')->isEmpty()) {
      $file = $user->get('user_picture')->entity;
      if ($file) {
         $instructor_avatar = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
      }
    }

    $bio = $user->hasField('field_bio') ? $user->get('field_bio')->value : '';
    $job_title = $user->hasField('field_job_title') ? $user->get('field_job_title')->value : '';

    $social_links = [];
    $social_fields = ['field_linkedin' => 'linkedin', 'field_twitter' => 'twitter', 'field_youtube' => 'youtube', 'field_website' => 'website'];
    foreach ($social_fields as $field_name => $platform) {
      if ($user->hasField($field_name) && !$user->get($field_name)->isEmpty()) {
        $val = $user->get($field_name)->uri ?? $user->get($field_name)->value;
        if (!empty($val)) {
          $social_links[$platform] = $val;
        }
      }
    }

    $instructor_course_ids = $this->entityTypeManager->getStorage('lms_course')
      ->getQuery()
      ->condition('field_instructor', $user->id())
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->execute();

    $courses = [];
    if (!empty($instructor_course_ids)) {
      $courses = $this->entityTypeManager->getStorage('lms_course')->loadMultiple($instructor_course_ids);
    }
    
    $instructor_students_count = 0;
    if (!empty($instructor_course_ids)) {
      $instructor_students_count = $this->entityTypeManager->getStorage('lms_user_course')
        ->getQuery()
        ->condition('field_course', array_values($instructor_course_ids), 'IN')
        ->accessCheck(FALSE)
        ->count()
        ->execute();
    }
    
    $total_rating = 0;
    $rated_courses = 0;
    foreach ($courses as $c) {
       if ($c->hasField('field_rating') && !$c->get('field_rating')->isEmpty()) {
          $rating = (float) $c->get('field_rating')->value;
          if ($rating > 0) {
             $total_rating += $rating;
             $rated_courses++;
          }
       }
    }
    $average_rating = $rated_courses > 0 ? round($total_rating / $rated_courses, 1) : 0;

    return [
      '#theme' => 'lms_instructor_profile',
      '#instructor' => $user,
      '#instructor_name' => $instructor_name,
      '#instructor_avatar' => $instructor_avatar,
      '#bio' => $bio,
      '#job_title' => $job_title,
      '#social_links' => $social_links,
      '#stats' => [
        'courses' => count($courses),
        'students' => $instructor_students_count,
        'rating' => $average_rating,
        'reviews' => 0, // Placeholder
      ],
      '#courses' => $this->buildCourseCards($courses),
    ];
  }

  /**
   * Category listing.
   */
  public function categories(): array {
    $categories = $this->lmsHelper->getTermsByVocabulary('course_category');

    $active_categories = [];
    $course_storage = $this->entityTypeManager->getStorage('lms_course');
    foreach ($categories as $term) {
      $count = $course_storage->getQuery()
        ->condition('field_category', $term->id())
        ->condition('status', TRUE)
        ->accessCheck(FALSE)
        ->count()
        ->execute();

      if ($count > 0) {
        $thumbnail_url = '';
        if ($term->hasField('field_category_thumbnail') && !$term->get('field_category_thumbnail')->isEmpty()) {
          $file = $term->get('field_category_thumbnail')->entity;
          if ($file) {
             $thumbnail_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }
        
        $active_categories[] = [
          'entity' => $term,
          'course_count' => $count,
          'thumbnail_url' => $thumbnail_url,
        ];
      }
    }

    return [
      '#theme' => 'lms_categories',
      '#categories' => $active_categories,
    ];
  }

  /**
   * Category detail — list courses in category.
   */
  public function categoryDetail(TermInterface $taxonomy_term): array {
    $ids = $this->entityTypeManager->getStorage('lms_course')
      ->getQuery()
      ->condition('field_category', $taxonomy_term->id())
      ->condition('status', TRUE)
      ->sort('created', 'DESC')
      ->pager(12)
      ->accessCheck(FALSE)
      ->execute();
    $courses = $ids
      ? $this->entityTypeManager->getStorage('lms_course')->loadMultiple($ids)
      : [];

    return [
      '#theme' => 'lms_catalog',
      '#courses' => $this->buildCourseCards($courses),
      '#categories' => [],
      '#title' => $taxonomy_term->label(),
      '#pager' => ['#type' => 'pager'],
    ];
  }

  /**
   * Build course card view models.
   */
  private function buildCourseCards(array $courses): array {
    $cards = [];
    foreach ($courses as $course) {
      /** @var \Drupal\lms_course\Entity\Course $course */
      $price = (float) $course->get('field_price')->value;
      $category = $course->get('field_category')->entity;
      $level = $course->get('field_level')->entity;
      $duration = (int) $course->get('field_duration')->value;

      $instructor_refs = $course->get('field_instructor')->referencedEntities();
      $instructor_entity = !empty($instructor_refs) ? reset($instructor_refs) : $course->getOwner();
      $instructor_name = $instructor_entity ? $instructor_entity->getDisplayName() : '';
      if ($instructor_entity) {
        $first_name = $instructor_entity->hasField('field_first_name') ? $instructor_entity->get('field_first_name')->value : '';
        $last_name = $instructor_entity->hasField('field_last_name') ? $instructor_entity->get('field_last_name')->value : '';
        $full_name = trim("$first_name $last_name");
        if (!empty($full_name)) {
          $instructor_name = $full_name;
        }
      }
      $instructor_url = $instructor_entity ? \Drupal\Core\Url::fromRoute('lms_course.instructor_profile', ['user' => $instructor_entity->id()])->toString() : '';
      
      $instructor_avatar = '';
      if ($instructor_entity && $instructor_entity->hasField('user_picture') && !$instructor_entity->get('user_picture')->isEmpty()) {
        $file = $instructor_entity->get('user_picture')->entity;
        if ($file) {
           $instructor_avatar = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }

      $is_enrolled = FALSE;
      $continue_url = \Drupal\Core\Url::fromRoute('lms_course.detail', ['lms_course' => $course->id()])->toString();

      $user_account = \Drupal::currentUser();
      $uid = $user_account->id();
      
      if ($uid) {
        // Check membership first.
        $has_membership = \Drupal::moduleHandler()->moduleExists('lms_membership') && \Drupal::service('lms_membership.manager')->hasActiveMembership($user_account);
        
        if ($has_membership) {
          $is_enrolled = TRUE;
        } else {
          $enrollment_count = $this->entityTypeManager->getStorage('lms_user_course')
            ->getQuery()
            ->condition('uid', $uid)
            ->condition('field_course', $course->id())
            ->accessCheck(FALSE)
            ->count()
            ->execute();
          $is_enrolled = $enrollment_count > 0;
        }

        if ($is_enrolled && !$course->get('field_chapters')->isEmpty()) {
           $course_lesson_ids = [];
           foreach ($course->get('field_chapters')->referencedEntities() as $chapter) {
             if ($chapter->hasField('field_lessons') && !$chapter->get('field_lessons')->isEmpty()) {
               foreach ($chapter->get('field_lessons')->referencedEntities() as $lesson) {
                 if ($lesson->get('status')->value) {
                   $course_lesson_ids[] = $lesson->id();
                 }
               }
             }
           }

           if (!empty($course_lesson_ids)) {
             $target_lesson_id = NULL;

             $last_progress = $this->entityTypeManager->getStorage('lms_user_lesson')
               ->getQuery()
               ->condition('uid', $uid)
               ->condition('field_lesson', $course_lesson_ids, 'IN')
               ->sort('id', 'DESC')
               ->range(0, 1)
               ->accessCheck(FALSE)
               ->execute();

             if ($last_progress) {
               $progress_record = $this->entityTypeManager->getStorage('lms_user_lesson')->load(reset($last_progress));
               if ($progress_record) {
                 $last_lesson_id = $progress_record->get('field_lesson')->target_id;
                 if ($progress_record->get('field_status')->value !== 'completed') {
                   $target_lesson_id = $last_lesson_id;
                 } else {
                   $found = FALSE;
                   foreach ($course_lesson_ids as $lid) {
                     if ($found) {
                       $target_lesson_id = $lid;
                       break;
                     }
                     if ($lid == $last_lesson_id) {
                       $found = TRUE;
                     }
                   }
                 }
               }
             }

             if (!$target_lesson_id) {
               $completed_progress = $this->entityTypeManager->getStorage('lms_user_lesson')
                 ->getQuery()
                 ->condition('uid', $uid)
                 ->condition('field_lesson', $course_lesson_ids, 'IN')
                 ->condition('field_status', 'completed')
                 ->accessCheck(FALSE)
                 ->execute();
               
               $completed_lesson_ids = [];
               if ($completed_progress) {
                 foreach ($this->entityTypeManager->getStorage('lms_user_lesson')->loadMultiple($completed_progress) as $record) {
                    $completed_lesson_ids[] = $record->get('field_lesson')->target_id;
                 }
               }
               foreach ($course_lesson_ids as $lid) {
                 if (!in_array($lid, $completed_lesson_ids)) {
                   $target_lesson_id = $lid;
                   break;
                 }
               }
             }

             if (!$target_lesson_id) {
               $target_lesson_id = $course_lesson_ids[0];
             }

             if ($target_lesson_id) {
                $continue_url = \Drupal\Core\Url::fromRoute('lms_progress.lesson_view', [
                  'lms_course' => $course->id(), 
                  'lms_lesson' => $target_lesson_id
                ])->toString();
             }
           }
        }
      }

      $thumbnail_url = '';
      if ($course->hasField('field_thumbnail') && !$course->get('field_thumbnail')->isEmpty()) {
        $file = $course->get('field_thumbnail')->entity;
        if ($file && file_exists($file->getFileUri())) {
          $thumbnail_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }

      $cards[] = [
        'id' => $course->id(),
        'name' => $course->label(),
        'short_description' => $course->get('field_short_description')->value ?? '',
        'price' => $price > 0 ? '$' . number_format($price, 2) : 'Free',
        'price_raw' => $price,
        'rating' => $course->hasField('field_rating') && !$course->get('field_rating')->isEmpty() ? (float) $course->get('field_rating')->value : 5.0,
        'category' => $category?->label() ?? '',
        'category_id' => $category?->id() ?? '',
        'level' => $level?->label() ?? '',
        'level_id' => strtolower($level?->label() ?? ''),
        'duration' => $this->lmsHelper->formatDuration($duration),
        'bestseller' => (bool) $course->get('field_bestseller')->value,
        'featured' => (bool) $course->get('field_featured')->value,
        'has_thumbnail' => !empty($thumbnail_url),
        'thumbnail_url' => $thumbnail_url,
        'url' => \Drupal\Core\Url::fromRoute('lms_course.detail', ['lms_course' => $course->id()])->toString(),
        'continue_url' => $continue_url,
        'instructor_name' => $instructor_name,
        'instructor_avatar' => $instructor_avatar,
        'instructor_url' => $instructor_url,
        'is_enrolled' => $is_enrolled,
      ];
    }
    return $cards;
  }

}
