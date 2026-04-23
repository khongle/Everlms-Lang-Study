<?php

declare(strict_types=1);

namespace Drupal\lms_course\Controller;

use Drupal\Core\Cache\Cache;
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
    $homepage_settings = $this->getHomepageSettings();

    // 0. Slideshow Banner from Custom Node Type.
    $slideshows = [];
    $slide_nids = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->condition('type', 'slideshow')
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->execute();

    if ($slide_nids) {
      $slide_nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($slide_nids);
      foreach ($slide_nodes as $block) {
        $image_url = '';
        if ($block->hasField('field_slide_image') && !$block->get('field_slide_image')->isEmpty()) {
          $file = $block->get('field_slide_image')->entity;
          if ($file) {
            $image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }
        
        $link_url = '#';
        $link_title = $homepage_settings['default_slide_cta_text'];
        if ($block->hasField('field_slide_link') && !$block->get('field_slide_link')->isEmpty()) {
          $link_item = $block->get('field_slide_link')->first();
          try {
            $link_url = \Drupal\Core\Url::fromUri($link_item->uri)->toString();
          } catch (\Exception $e) {
            $link_url = $link_item->uri;
          }
          $link_title = $link_item->title ?: $link_title;
        }

        $slideshows[] = [
          'heading' => $block->hasField('field_slide_heading') ? $block->get('field_slide_heading')->value : '',
          'subheading' => $block->hasField('field_slide_subheading') ? $block->get('field_slide_subheading')->value : '',
          'link_url' => $link_url,
          'link_title' => $link_title,
          'position' => $block->hasField('field_slide_position') ? $block->get('field_slide_position')->value : 'left',
          'image_url' => $image_url,
        ];
      }
    }

    // Featured courses.
    $featured = $this->entityTypeManager->getStorage('lms_course')
      ->getQuery()
      ->condition('field_featured', TRUE)
      ->condition('status', TRUE)
      ->sort('changed', 'DESC')
      ->sort('id', 'DESC')
      ->range(0, 24)
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

      $icon_url = '';
      if ($term->hasField('field_icon') && !$term->get('field_icon')->isEmpty()) {
        $icon_file = $term->get('field_icon')->entity;
        if ($icon_file) {
          $icon_url = \Drupal::service('file_url_generator')->generateAbsoluteString($icon_file->getFileUri());
        }
      }

      $top_categories[] = [
        'entity' => $term,
        'name' => $term->label(),
        'course_count' => $count,
        'thumbnail_url' => $thumbnail_url,
        'icon_url' => $icon_url,
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

    // Fetch Featured Testimonials.
    $testimonials = [];
    if (\Drupal::entityTypeManager()->hasDefinition('lms_rating')) {
      $rating_storage = $this->entityTypeManager->getStorage('lms_rating');
      $rating_ids = $rating_storage->getQuery()
        ->condition('field_approved', TRUE)
        ->condition('field_is_featured', TRUE)
        ->sort('created', 'DESC')
        ->range(0, 10)
        ->accessCheck(FALSE)
        ->execute();

      if ($rating_ids) {
        $ratings = $rating_storage->loadMultiple($rating_ids);
        foreach ($ratings as $rating) {
          $owner = $rating->getOwner();
          $name = $owner ? $owner->getDisplayName() : 'Anonymous';
          $role = 'Student';
          $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random';
          
          if ($owner) {
            $first_name = $owner->hasField('field_first_name') ? $owner->get('field_first_name')->value : '';
            $last_name = $owner->hasField('field_last_name') ? $owner->get('field_last_name')->value : '';
            $full_name = trim("$first_name $last_name");
            if (!empty($full_name)) {
              $name = $full_name;
              $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random';
            }
            if ($owner->hasField('field_job_title') && !$owner->get('field_job_title')->isEmpty()) {
              $role = $owner->get('field_job_title')->value;
            }
            if ($owner->hasField('user_picture') && !$owner->get('user_picture')->isEmpty()) {
              $file = $owner->get('user_picture')->entity;
              if ($file) {
                $avatar = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
              }
            }
          }

          $testimonials[] = [
            'name' => $name,
            'role' => $role,
            'content' => $rating->get('field_review')->value,
            'rating' => (int) $rating->get('field_score')->value,
            'avatar' => $avatar,
            'course_title' => $rating->hasField('field_course') && !$rating->get('field_course')->isEmpty() && $rating->get('field_course')->entity
              ? $rating->get('field_course')->entity->label()
              : (string) $this->t('Top Learner'),
          ];
        }
      }
    }

    // Introductions.
    $introductions = [];
    $intro_nids = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->condition('type', 'introduction')
      ->condition('status', 1)
      ->sort('created', 'ASC')
      ->accessCheck(FALSE)
      ->execute();

    if ($intro_nids) {
      $intro_nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($intro_nids);
      foreach ($intro_nodes as $node) {
        $image_url = '';
        if ($node->hasField('field_intro_image') && !$node->get('field_intro_image')->isEmpty()) {
          $file = $node->get('field_intro_image')->entity;
          if ($file) {
            $image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }
        
        $link_url = '#';
        $link_title = $homepage_settings['default_intro_cta_text'];
        if ($node->hasField('field_call_to_action') && !$node->get('field_call_to_action')->isEmpty()) {
          $link_item = $node->get('field_call_to_action')->first();
          try {
            $link_url = \Drupal\Core\Url::fromUri($link_item->uri)->toString();
          } catch (\Exception $e) {
            $link_url = $link_item->uri;
          }
          $link_title = $link_item->title ?: $link_title;
        }

        $bg_color_mapped = 'white';
        $bg_raw = $node->hasField('field_background_color') ? $node->get('field_background_color')->value : '#f8f9fa';
        if (str_contains(strtolower((string)$bg_raw), '#1e293b') || str_contains(strtolower((string)$bg_raw), 'dark')) {
            $bg_color_mapped = 'dark';
        } elseif (str_contains(strtolower((string)$bg_raw), '#f1f5f9') || str_contains(strtolower((string)$bg_raw), 'gray') || str_contains(strtolower((string)$bg_raw), '#f8f9fa')) {
            $bg_color_mapped = 'gray';
        }

        $introductions[] = [
          'title' => $node->label(),
          'subheading' => $node->hasField('field_subheading') ? $node->get('field_subheading')->value : '',
          'body' => $node->hasField('body') ? $node->get('body')->value : '',
          'image_url' => $image_url,
          'image_position' => $node->hasField('field_image_position') ? $node->get('field_image_position')->value : 'right',
          'background_color' => $bg_color_mapped,
          'link_url' => $link_url,
          'link_title' => $link_title,
        ];
      }
    }

    // FAQs (Global)
    $faq_ids = $this->entityTypeManager->getStorage('lms_faq')
      ->getQuery()
      ->condition('field_scope', 'global')
      ->sort('weight', 'ASC')
      ->range(0, 8)
      ->accessCheck(FALSE)
      ->execute();

    $global_faqs = [];
    if ($faq_ids) {
      $faq_entities = $this->entityTypeManager->getStorage('lms_faq')->loadMultiple($faq_ids);
      foreach ($faq_entities as $faq) {
        $global_faqs[] = [
          'id' => $faq->id(),
          'question' => $faq->get('question')->value,
          'answer' => $faq->hasField('field_answer') && !$faq->get('field_answer')->isEmpty() ? $faq->get('field_answer')->value : '',
        ];
      }
    }
    // Memberships.
    $memberships = $this->getHomepageMemberships();

    // Latest News & Insights.
    $latest_news = [];
    $news_nids = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->condition('type', 'blog')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, 6)
      ->accessCheck(FALSE)
      ->execute();

    if ($news_nids) {
      $news_nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($news_nids);
      foreach ($news_nodes as $node) {
        $image_url = '';
        if ($node->hasField('field_thumb') && !$node->get('field_thumb')->isEmpty()) {
          $file = $node->get('field_thumb')->entity;
          if ($file) {
            $image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }
        
        $body = $node->hasField('body') ? $node->get('body')->value : '';
        $summary = '';
        if ($node->hasField('body') && $node->get('body')->summary) {
           $summary = $node->get('body')->summary;
        } else {
           $summary = strip_tags((string) $body);
           if (mb_strlen($summary) > 130) {
              $summary = mb_substr($summary, 0, 127) . '...';
           }
        }

        $latest_news[] = [
          'title' => $node->label(),
          'url' => \Drupal\Core\Url::fromRoute('lms_blog.frontend.canonical', ['node' => $node->id()])->toString(),
          'thumbnail_url' => $image_url,
          'summary' => $summary,
          'date' => \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'custom', 'M d, Y'),
        ];
      }
    }

    // Latest News & Insights.
    $latest_news = [];
    $news_nids = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->condition('type', 'blog')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, 6)
      ->accessCheck(FALSE)
      ->execute();

    if ($news_nids) {
      $news_nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($news_nids);
      foreach ($news_nodes as $node) {
        $image_url = '';
        if ($node->hasField('field_thumb') && !$node->get('field_thumb')->isEmpty()) {
          $file = $node->get('field_thumb')->entity;
          if ($file) {
            $image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }
        
        $body = $node->hasField('body') ? $node->get('body')->value : '';
        $summary = '';
        if ($node->hasField('body') && $node->get('body')->summary) {
           $summary = $node->get('body')->summary;
        } else {
           $summary = strip_tags((string) $body);
           if (mb_strlen($summary) > 130) {
              $summary = mb_substr($summary, 0, 127) . '...';
           }
        }

        $latest_news[] = [
          'title' => $node->label(),
          'url' => \Drupal\Core\Url::fromRoute('lms_blog.frontend.canonical', ['node' => $node->id()])->toString(),
          'thumbnail_url' => $image_url,
          'summary' => $summary,
          'date' => \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'custom', 'M d, Y'),
        ];
      }
    }

    return [
      '#theme' => 'lms_homepage',
      '#homepage_style' => theme_get_setting('homepage_style', 'lms_theme') ?? 'glassmorphism',
      '#homepage_settings' => $homepage_settings,
      '#introductions' => $introductions,
      '#slideshows' => $slideshows,
      '#latest_news' => $latest_news,
      '#featured_courses' => $this->buildCourseCards($featured_courses),
      '#categories' => $top_categories,
      '#my_courses' => $my_courses,
      '#bundles' => $bundles,
      '#testimonials' => $testimonials,
      '#memberships' => $memberships,
      '#faqs' => $global_faqs,
      '#logged_in' => $user->isAuthenticated(),
      '#stats' => $this->getHomepageStats($homepage_settings, $course_count),
      '#cache' => [
        'tags' => Cache::mergeTags([
          'lms_rating_list',
          'lms_course_list',
        ], array_map(static fn($course) => 'lms_course:' . $course->id(), $featured_courses)),
      ],
    ];
  }

  /**
   * Returns editable homepage settings with safe defaults.
   */
  private function getHomepageSettings(): array {
    $site_name = trim((string) (\Drupal::config('system.site')->get('name') ?? '')) ?: 'EverLMS';
    $catalog_url = \Drupal\Core\Url::fromRoute('lms_course.catalog')->toString();
    $style = theme_get_setting('homepage_style', 'lms_theme') ?? 'glassmorphism';
    $default_sections = [
      'hero',
      'stats',
      'continue_learning',
      'featured',
      'categories',
      'introductions',
      'bundles',
      'testimonials',
      'faqs',
      'news',
      'membership',
      'training_process',
      'cta_guest',
    ];

    $defaults = [
      'stats_students_label' => (string) $this->t('Students'),
      'stats_students_value' => '5,000+',
      'stats_satisfaction_label' => (string) $this->t('Satisfaction'),
      'stats_satisfaction_value' => '98%',
      'stats_courses_label' => (string) $this->t('Courses'),
      'stats_certs_label' => (string) $this->t('Certificates'),
      'hero_fallback_heading' => (string) $this->t('Welcome to @site_name', ['@site_name' => $site_name]),
      'hero_fallback_subtext' => (string) $this->t('Start your learning journey with our premium courses today.'),
      'hero_fallback_cta_text' => (string) $this->t('Explore Courses'),
      'hero_fallback_cta_url' => $catalog_url,
      'default_slide_cta_text' => (string) $this->t('Explore Now'),
      'default_intro_cta_text' => (string) $this->t('Learn More'),
    ];

    $defaults += $this->getHomepageCopyDefaults($style, $site_name);

    $settings = [];
    foreach ($defaults as $key => $default) {
      $value = theme_get_setting($key, 'lms_theme');
      $settings[$key] = $value !== NULL && $value !== '' ? $value : $default;
    }

    $raw_sections = theme_get_setting('homepage_sections_enabled', 'lms_theme');
    if ($raw_sections === NULL) {
      $raw_sections = $default_sections;
    }

    $settings['sections_enabled'] = [];
    foreach ($default_sections as $section) {
      $settings['sections_enabled'][$section] = in_array($section, $raw_sections, TRUE) || !empty($raw_sections[$section]);
    }

    return $settings;
  }

  /**
   * Returns style-aware defaults for editable homepage copy.
   */
  private function getHomepageCopyDefaults(string $style, string $site_name): array {
    $defaults = [
      'glassmorphism' => [
        'hero_secondary_cta_text' => (string) $this->t('Browse Catalog'),
        'categories_section_title' => (string) $this->t('Explore Popular Categories'),
        'categories_section_subtitle' => (string) $this->t('Find the perfect topic to boost your skills and career.'),
        'featured_section_title' => (string) $this->t('Featured Courses'),
        'featured_section_subtitle' => '',
        'featured_section_link_text' => (string) $this->t('View All Courses'),
        'bundles_section_title' => (string) $this->t('Popular Learning Bundles'),
        'bundles_section_subtitle' => (string) $this->t('Save up to 40% with our curated course bundles.'),
        'introductions_section_title' => '',
        'introductions_section_subtitle' => '',
        'testimonials_section_title' => (string) $this->t('What Our Students Say'),
        'testimonials_section_subtitle' => '',
        'membership_section_title' => '',
        'membership_section_subtitle' => '',
        'membership_recommended_badge_text' => (string) $this->t('Most Popular'),
        'membership_free_price_text' => (string) $this->t('Free'),
        'membership_free_supporting_text' => '',
        'membership_paid_supporting_text' => '',
        'membership_free_cta_text' => (string) $this->t('Get Started'),
        'membership_recommended_cta_text' => (string) $this->t('Get Started'),
        'membership_paid_cta_text' => (string) $this->t('Get Started'),
        'faq_section_title' => '',
        'faq_section_subtitle' => '',
        'news_section_title' => '',
        'news_section_subtitle' => '',
        'news_section_link_text' => (string) $this->t('View all news'),
        'training_process_section_title' => '',
        'training_process_section_subtitle' => '',
        'training_process_step_1_title' => '',
        'training_process_step_1_description' => '',
        'training_process_step_2_title' => '',
        'training_process_step_2_description' => '',
        'training_process_step_3_title' => '',
        'training_process_step_3_description' => '',
        'training_process_step_4_title' => '',
        'training_process_step_4_description' => '',
        'training_process_panel_title' => '',
        'training_process_panel_subtitle' => '',
        'training_process_feature_1' => '',
        'training_process_feature_2' => '',
        'training_process_feature_3' => '',
        'training_process_feature_4' => '',
        'training_process_feature_5' => '',
        'training_process_feature_6' => '',
        'training_process_panel_cta_text' => '',
        'faq_support_title' => '',
        'faq_support_subtitle' => '',
        'faq_support_body' => '',
        'faq_support_cta_text' => '',
        'faq_quicklinks_title' => '',
        'faq_quicklink_catalog_text' => '',
        'faq_quicklink_categories_text' => '',
        'faq_quicklink_dashboard_text' => '',
        'faq_quicklink_certificates_text' => '',
        'cta_banner_title' => (string) $this->t('Ready to Transform Your Career?'),
        'cta_banner_description' => (string) $this->t('Join thousands of students and start learning today.'),
        'cta_primary_text' => (string) $this->t('Start Free Trial'),
        'cta_secondary_text' => (string) $this->t('Browse Courses'),
        'cta_logged_in_secondary_text' => (string) $this->t('Go to Dashboard'),
      ],
      'minimalist' => [
        'hero_secondary_cta_text' => (string) $this->t('Browse Catalog'),
        'categories_section_title' => (string) $this->t('Explore Categories'),
        'categories_section_subtitle' => (string) $this->t('Find the perfect topic to build practical skills and advance your career.'),
        'featured_section_title' => (string) $this->t('Featured Programs'),
        'featured_section_subtitle' => (string) $this->t('Hand-picked premium content for your growth.'),
        'featured_section_link_text' => (string) $this->t('Explore All'),
        'bundles_section_title' => (string) $this->t('Popular Learning Bundles'),
        'bundles_section_subtitle' => (string) $this->t('Save multiple courses by enrolling into verified bundles.'),
        'introductions_section_title' => '',
        'introductions_section_subtitle' => '',
        'testimonials_section_title' => (string) $this->t('What Our Students Say'),
        'testimonials_section_subtitle' => (string) $this->t('Real feedback from our professional community.'),
        'membership_section_title' => '',
        'membership_section_subtitle' => '',
        'membership_recommended_badge_text' => (string) $this->t('Most Popular'),
        'membership_free_price_text' => (string) $this->t('Free'),
        'membership_free_supporting_text' => '',
        'membership_paid_supporting_text' => '',
        'membership_free_cta_text' => (string) $this->t('Get Started'),
        'membership_recommended_cta_text' => (string) $this->t('Get Started'),
        'membership_paid_cta_text' => (string) $this->t('Get Started'),
        'faq_section_title' => (string) $this->t('Frequently Asked Questions'),
        'faq_section_subtitle' => (string) $this->t('Got questions? We have answers. Check out our most common queries below.'),
        'news_section_title' => '',
        'news_section_subtitle' => '',
        'news_section_link_text' => (string) $this->t('View all news'),
        'training_process_section_title' => '',
        'training_process_section_subtitle' => '',
        'training_process_step_1_title' => '',
        'training_process_step_1_description' => '',
        'training_process_step_2_title' => '',
        'training_process_step_2_description' => '',
        'training_process_step_3_title' => '',
        'training_process_step_3_description' => '',
        'training_process_step_4_title' => '',
        'training_process_step_4_description' => '',
        'training_process_panel_title' => '',
        'training_process_panel_subtitle' => '',
        'training_process_feature_1' => '',
        'training_process_feature_2' => '',
        'training_process_feature_3' => '',
        'training_process_feature_4' => '',
        'training_process_feature_5' => '',
        'training_process_feature_6' => '',
        'training_process_panel_cta_text' => '',
        'faq_support_title' => '',
        'faq_support_subtitle' => '',
        'faq_support_body' => '',
        'faq_support_cta_text' => '',
        'faq_quicklinks_title' => '',
        'faq_quicklink_catalog_text' => '',
        'faq_quicklink_categories_text' => '',
        'faq_quicklink_dashboard_text' => '',
        'faq_quicklink_certificates_text' => '',
        'cta_banner_title' => (string) $this->t('Ready to Transform Your Career?'),
        'cta_banner_description' => (string) $this->t('Join thousands of professionals mastering new skills every day. Registration takes less than a minute.'),
        'cta_primary_text' => (string) $this->t('Create Free Account'),
        'cta_secondary_text' => (string) $this->t('Browse Catalog'),
        'cta_logged_in_secondary_text' => (string) $this->t('Go to Dashboard'),
      ],
      'playful' => [
        'hero_secondary_cta_text' => (string) $this->t('Explore Catalog'),
        'categories_section_title' => (string) $this->t('Choose Your Adventure!'),
        'categories_section_subtitle' => (string) $this->t('Pick a path and start exploring. Every adventure is packed with fun challenges and awesome rewards!'),
        'featured_section_title' => (string) $this->t('Popular Quests'),
        'featured_section_subtitle' => (string) $this->t('Top-rated courses our learners love. Earn XP and unlock badges as you complete each one!'),
        'featured_section_link_text' => (string) $this->t('Explore All'),
        'bundles_section_title' => (string) $this->t('Advance Your Career'),
        'bundles_section_subtitle' => (string) $this->t('Follow a tactical learning path to move from new hire to true expert.'),
        'introductions_section_title' => (string) $this->t("Skills You'll Master"),
        'introductions_section_subtitle' => (string) $this->t('Every course gives you new tactical abilities. Upgrade your stats!'),
        'testimonials_section_title' => (string) $this->t('Star Associates Say…'),
        'testimonials_section_subtitle' => (string) $this->t('Hear from learners who are leveling up every week.'),
        'membership_section_title' => (string) $this->t('Pick Your Plan'),
        'membership_section_subtitle' => (string) $this->t('Choose the adventure tier that fits your quest. Upgrade anytime!'),
        'membership_recommended_badge_text' => (string) $this->t('🔥 Most Popular!'),
        'membership_free_price_text' => (string) $this->t('Free'),
        'membership_free_supporting_text' => '',
        'membership_paid_supporting_text' => '',
        'membership_free_cta_text' => (string) $this->t('Start Free 🎈'),
        'membership_recommended_cta_text' => (string) $this->t('Go Champion 🚀'),
        'membership_paid_cta_text' => (string) $this->t('Go Legend 👑'),
        'faq_section_title' => (string) $this->t('Got Questions?'),
        'faq_section_subtitle' => (string) $this->t("We've got answers. Click to reveal the magic."),
        'news_section_title' => '',
        'news_section_subtitle' => '',
        'news_section_link_text' => (string) $this->t('View all news'),
        'training_process_section_title' => '',
        'training_process_section_subtitle' => '',
        'training_process_step_1_title' => '',
        'training_process_step_1_description' => '',
        'training_process_step_2_title' => '',
        'training_process_step_2_description' => '',
        'training_process_step_3_title' => '',
        'training_process_step_3_description' => '',
        'training_process_step_4_title' => '',
        'training_process_step_4_description' => '',
        'training_process_panel_title' => '',
        'training_process_panel_subtitle' => '',
        'training_process_feature_1' => '',
        'training_process_feature_2' => '',
        'training_process_feature_3' => '',
        'training_process_feature_4' => '',
        'training_process_feature_5' => '',
        'training_process_feature_6' => '',
        'training_process_panel_cta_text' => '',
        'faq_support_title' => '',
        'faq_support_subtitle' => '',
        'faq_support_body' => '',
        'faq_support_cta_text' => '',
        'faq_quicklinks_title' => '',
        'faq_quicklink_catalog_text' => '',
        'faq_quicklink_categories_text' => '',
        'faq_quicklink_dashboard_text' => '',
        'faq_quicklink_certificates_text' => '',
        'cta_banner_title' => (string) $this->t('Ready for the Floor? 🚀'),
        'cta_banner_description' => (string) $this->t('Join thousands of associates who are leveling up their career every day. The journey starts now.'),
        'cta_primary_text' => (string) $this->t('Start Training Now 🎉'),
        'cta_secondary_text' => (string) $this->t('Browse Catalog'),
        'cta_logged_in_secondary_text' => (string) $this->t('Go to Dashboard'),
      ],
      'community' => [
        'hero_secondary_cta_text' => (string) $this->t('Join Community'),
        'categories_section_title' => (string) $this->t('Find Your Learning Circle'),
        'categories_section_subtitle' => (string) $this->t('Explore topic hubs where members learn, share, and grow together.'),
        'featured_section_title' => (string) $this->t('Popular with Members'),
        'featured_section_subtitle' => (string) $this->t('Courses learners are actively discussing and completing this week.'),
        'featured_section_link_text' => (string) $this->t('See Full Catalog'),
        'bundles_section_title' => (string) $this->t('Community Learning Paths'),
        'bundles_section_subtitle' => (string) $this->t('Follow curated paths to build momentum with your peers.'),
        'introductions_section_title' => (string) $this->t('Learn Better Together'),
        'introductions_section_subtitle' => (string) $this->t('Join conversations, learn from mentors, and stay accountable with your cohort.'),
        'testimonials_section_title' => (string) $this->t('Member Stories'),
        'testimonials_section_subtitle' => (string) $this->t('Real voices from learners advancing their goals inside the community.'),
        'membership_section_title' => (string) $this->t('Membership Tiers'),
        'membership_section_subtitle' => (string) $this->t('Pick the access level that matches your learning rhythm.'),
        'membership_recommended_badge_text' => (string) $this->t('Most Active'),
        'membership_free_price_text' => (string) $this->t('Free'),
        'membership_free_supporting_text' => (string) $this->t('Great for getting started'),
        'membership_paid_supporting_text' => (string) $this->t('Unlock member-only perks and live sessions'),
        'membership_free_cta_text' => (string) $this->t('Start for Free'),
        'membership_recommended_cta_text' => (string) $this->t('Join Popular Plan'),
        'membership_paid_cta_text' => (string) $this->t('Upgrade Access'),
        'faq_section_title' => (string) $this->t('Community FAQ'),
        'faq_section_subtitle' => (string) $this->t('Everything you need to know about joining, participating, and growing here.'),
        'news_section_title' => (string) $this->t('Community Updates'),
        'news_section_subtitle' => (string) $this->t('Highlights, events, and announcements from across the learning community.'),
        'news_section_link_text' => (string) $this->t('View all updates'),
        'training_process_section_title' => (string) $this->t('How Community Learning Works'),
        'training_process_section_subtitle' => (string) $this->t('A simple flow to help you stay active and make visible progress.'),
        'training_process_step_1_title' => (string) $this->t('Join and set your learning goals'),
        'training_process_step_1_description' => (string) $this->t('Create your account, choose interests, and define your first target milestone.'),
        'training_process_step_2_title' => (string) $this->t('Enroll and learn with your peers'),
        'training_process_step_2_description' => (string) $this->t('Start courses, follow your path, and keep momentum with weekly progress.'),
        'training_process_step_3_title' => (string) $this->t('Discuss, ask, and get mentor support'),
        'training_process_step_3_description' => (string) $this->t('Use forum threads and Q&A spaces to unblock quickly and learn together.'),
        'training_process_step_4_title' => (string) $this->t('Celebrate wins and level up'),
        'training_process_step_4_description' => (string) $this->t('Track outcomes, collect certificates, and unlock the next growth stage.'),
        'training_process_panel_title' => (string) $this->t('Member Experience'),
        'training_process_panel_subtitle' => (string) $this->t('Built for participation, consistency, and meaningful progress.'),
        'training_process_feature_1' => (string) $this->t('Peer discussion and support loops'),
        'training_process_feature_2' => (string) $this->t('Structured learning paths'),
        'training_process_feature_3' => (string) $this->t('Mentor-guided Q&A touchpoints'),
        'training_process_feature_4' => (string) $this->t('Progress and completion tracking'),
        'training_process_feature_5' => (string) $this->t('Certificates and achievement signals'),
        'training_process_feature_6' => (string) $this->t('Events, updates, and member spotlights'),
        'training_process_panel_cta_text' => (string) $this->t('Explore Learning Paths'),
        'faq_support_title' => '',
        'faq_support_subtitle' => '',
        'faq_support_body' => '',
        'faq_support_cta_text' => '',
        'faq_quicklinks_title' => '',
        'faq_quicklink_catalog_text' => '',
        'faq_quicklink_categories_text' => '',
        'faq_quicklink_dashboard_text' => '',
        'faq_quicklink_certificates_text' => '',
        'cta_banner_title' => (string) $this->t('Ready to Learn with the Community?'),
        'cta_banner_description' => (string) $this->t('Join a friendly peer network, stay accountable, and grow your skills together.'),
        'cta_primary_text' => (string) $this->t('Create Account'),
        'cta_secondary_text' => (string) $this->t('Browse Courses'),
        'cta_logged_in_secondary_text' => (string) $this->t('Go to Dashboard'),
      ],
      'nature' => [
        'hero_secondary_cta_text' => (string) $this->t('Explore Courses'),
        'categories_section_title' => (string) $this->t('Topics Rooted in Practice'),
        'categories_section_subtitle' => (string) $this->t('Discover subject areas cultivated for depth, clarity, and long-term mastery.'),
        'featured_section_title' => (string) $this->t('Courses Worth Growing With'),
        'featured_section_subtitle' => (string) $this->t('Hand-picked programs that reward patience, curiosity, and consistent effort.'),
        'featured_section_link_text' => (string) $this->t('Browse The Full Catalog'),
        'bundles_section_title' => (string) $this->t('Learning Paths'),
        'bundles_section_subtitle' => (string) $this->t('Curated sequences of courses designed to build understanding layer by layer.'),
        'introductions_section_title' => (string) $this->t('Our Approach to Learning'),
        'introductions_section_subtitle' => (string) $this->t('We believe knowledge grows best when it is nurtured with intention and care.'),
        'testimonials_section_title' => (string) $this->t('Voices from Our Community'),
        'testimonials_section_subtitle' => (string) $this->t('Real reflections from learners who took their time and made it count.'),
        'membership_section_title' => (string) $this->t('Choose Your Growth Plan'),
        'membership_section_subtitle' => (string) $this->t('Flexible options that let you learn at your own natural pace.'),
        'membership_recommended_badge_text' => (string) $this->t('Most Nourishing'),
        'membership_free_price_text' => (string) $this->t('Free'),
        'membership_free_supporting_text' => (string) $this->t('Start with essential access'),
        'membership_paid_supporting_text' => (string) $this->t('Unlock guided pathways and premium support'),
        'membership_free_cta_text' => (string) $this->t('Start for Free'),
        'membership_recommended_cta_text' => (string) $this->t('Grow With Us'),
        'membership_paid_cta_text' => (string) $this->t('Join Now'),
        'faq_section_title' => (string) $this->t('Common Questions'),
        'faq_section_subtitle' => (string) $this->t('Answers to the things most learners wonder about as they settle in.'),
        'news_section_title' => (string) $this->t('From the Garden'),
        'news_section_subtitle' => (string) $this->t('Updates, stories, and ideas freshly picked from our learning community.'),
        'news_section_link_text' => (string) $this->t('Read All Updates'),
        'training_process_section_title' => (string) $this->t('How Your Journey Unfolds'),
        'training_process_section_subtitle' => (string) $this->t('A gentle, purposeful path from first step to lasting achievement.'),
        'training_process_step_1_title' => (string) $this->t('Explore the landscape'),
        'training_process_step_1_description' => (string) $this->t('Browse topics and subjects that genuinely interest you.'),
        'training_process_step_2_title' => (string) $this->t('Plant your intention'),
        'training_process_step_2_description' => (string) $this->t('Choose a course or path that aligns with where you want to grow.'),
        'training_process_step_3_title' => (string) $this->t('Tend to your progress'),
        'training_process_step_3_description' => (string) $this->t('Move through lessons steadily, revisiting concepts as needed.'),
        'training_process_step_4_title' => (string) $this->t('Harvest your results'),
        'training_process_step_4_description' => (string) $this->t('Complete your course, earn your certificate, and carry the knowledge forward.'),
        'training_process_panel_title' => (string) $this->t('Growth Takes Root Here'),
        'training_process_panel_subtitle' => (string) $this->t('Learning designed for the long term'),
        'training_process_feature_1' => (string) $this->t('Self-paced and unhurried'),
        'training_process_feature_2' => (string) $this->t('Expert-crafted content'),
        'training_process_feature_3' => (string) $this->t('Warm and welcoming community'),
        'training_process_feature_4' => (string) $this->t('Progress tracked naturally'),
        'training_process_feature_5' => (string) $this->t('Certificates that endure'),
        'training_process_feature_6' => (string) $this->t('Consistent, thoughtful design'),
        'training_process_panel_cta_text' => (string) $this->t('Begin growing'),
        'faq_support_title' => '',
        'faq_support_subtitle' => '',
        'faq_support_body' => '',
        'faq_support_cta_text' => '',
        'faq_quicklinks_title' => '',
        'faq_quicklink_catalog_text' => '',
        'faq_quicklink_categories_text' => '',
        'faq_quicklink_dashboard_text' => '',
        'faq_quicklink_certificates_text' => '',
        'cta_banner_title' => (string) $this->t('Ready to Cultivate Something Meaningful?'),
        'cta_banner_description' => (string) $this->t('Join a learning community rooted in genuine curiosity, thoughtful guidance, and organic growth.'),
        'cta_primary_text' => (string) $this->t('Start Learning'),
        'cta_secondary_text' => (string) $this->t("See What's Growing"),
        'cta_logged_in_secondary_text' => (string) $this->t('Go to Dashboard'),
      ],
      'corporate' => [
        'hero_secondary_cta_text' => (string) $this->t('Browse Catalog'),
        'categories_section_title' => (string) $this->t('Training Tracks'),
        'categories_section_subtitle' => (string) $this->t('Structured learning programs designed to build deep expertise and drive organizational transformation.'),
        'featured_section_title' => (string) $this->t('Recommended Modules'),
        'featured_section_subtitle' => (string) $this->t('Curated training modules designed for measurable business impact and professional development.'),
        'featured_section_link_text' => (string) $this->t('View All Courses'),
        'bundles_section_title' => '',
        'bundles_section_subtitle' => '',
        'introductions_section_title' => (string) $this->t('@site_name Advantage', ['@site_name' => $site_name]),
        'introductions_section_subtitle' => (string) $this->t('Enterprise-grade capabilities built for scalable operations and rigorous compliance.'),
        'testimonials_section_title' => (string) $this->t('Performance Results'),
        'testimonials_section_subtitle' => (string) $this->t('See how leading organizations are scaling with structured learning.'),
        'membership_section_title' => (string) $this->t('Scalable Pricing'),
        'membership_section_subtitle' => (string) $this->t('Plans designed for teams of every size.'),
        'membership_recommended_badge_text' => (string) $this->t('Most Popular'),
        'membership_free_price_text' => (string) $this->t('Free'),
        'membership_free_supporting_text' => (string) $this->t('For individual associates'),
        'membership_paid_supporting_text' => (string) $this->t('Per user, billed annually'),
        'membership_free_cta_text' => (string) $this->t('Access Portal'),
        'membership_recommended_cta_text' => (string) $this->t('Deploy Fleet Plan'),
        'membership_paid_cta_text' => (string) $this->t('Deploy Fleet Plan'),
        'faq_section_title' => (string) $this->t("We're here to help you."),
        'faq_section_subtitle' => (string) $this->t('Everything you need to know about enterprise learning operations and how @site_name supports rollout, onboarding, and team growth.', ['@site_name' => $site_name]),
        'news_section_title' => (string) $this->t('Latest News & Insights'),
        'news_section_subtitle' => (string) $this->t('Stay up to date with platform tips, updates, and educational resources.'),
        'news_section_link_text' => (string) $this->t('View all news'),
        'training_process_section_title' => '',
        'training_process_section_subtitle' => '',
        'training_process_step_1_title' => '',
        'training_process_step_1_description' => '',
        'training_process_step_2_title' => '',
        'training_process_step_2_description' => '',
        'training_process_step_3_title' => '',
        'training_process_step_3_description' => '',
        'training_process_step_4_title' => '',
        'training_process_step_4_description' => '',
        'training_process_panel_title' => '',
        'training_process_panel_subtitle' => '',
        'training_process_feature_1' => '',
        'training_process_feature_2' => '',
        'training_process_feature_3' => '',
        'training_process_feature_4' => '',
        'training_process_feature_5' => '',
        'training_process_feature_6' => '',
        'training_process_panel_cta_text' => '',
        'faq_support_title' => '',
        'faq_support_subtitle' => '',
        'faq_support_body' => '',
        'faq_support_cta_text' => '',
        'faq_quicklinks_title' => '',
        'faq_quicklink_catalog_text' => '',
        'faq_quicklink_categories_text' => '',
        'faq_quicklink_dashboard_text' => '',
        'faq_quicklink_certificates_text' => '',
        'cta_banner_title' => (string) $this->t('Ready to Standardize Operations?'),
        'cta_banner_description' => (string) $this->t('Use @site_name to standardize training delivery, onboarding, and operational readiness across teams.', ['@site_name' => $site_name]),
        'cta_primary_text' => (string) $this->t('Browse Catalog'),
        'cta_secondary_text' => (string) $this->t('Contact Support'),
        'cta_logged_in_secondary_text' => (string) $this->t('Go to Dashboard'),
      ],
      'government' => [
        'hero_secondary_cta_text' => (string) $this->t('View Categories'),
        'categories_section_title' => (string) $this->t('Training Categories'),
        'categories_section_subtitle' => (string) $this->t('Browse training by subject area. Select a category to view available courses.'),
        'featured_section_title' => (string) $this->t('Featured Training'),
        'featured_section_subtitle' => (string) $this->t('Priority training modules recommended for professional development and compliance.'),
        'featured_section_link_text' => (string) $this->t('View all courses'),
        'bundles_section_title' => '',
        'bundles_section_subtitle' => '',
        'introductions_section_title' => (string) $this->t('About & Resources'),
        'introductions_section_subtitle' => (string) $this->t('Learn more about our training programs and access support resources.'),
        'testimonials_section_title' => '',
        'testimonials_section_subtitle' => '',
        'membership_section_title' => '',
        'membership_section_subtitle' => '',
        'membership_recommended_badge_text' => (string) $this->t('Most Popular'),
        'membership_free_price_text' => (string) $this->t('Free'),
        'membership_free_supporting_text' => '',
        'membership_paid_supporting_text' => '',
        'membership_free_cta_text' => (string) $this->t('Get Started'),
        'membership_recommended_cta_text' => (string) $this->t('Get Started'),
        'membership_paid_cta_text' => (string) $this->t('Get Started'),
        'faq_section_title' => (string) $this->t('Frequently Asked Questions'),
        'faq_section_subtitle' => (string) $this->t('Common questions about training requirements, deadlines, and portal usage.'),
        'news_section_title' => (string) $this->t('Announcements & Updates'),
        'news_section_subtitle' => (string) $this->t('Policy updates, training announcements, and important notices.'),
        'news_section_link_text' => (string) $this->t('View all announcements'),
        'training_process_section_title' => (string) $this->t('Training Process'),
        'training_process_section_subtitle' => (string) $this->t('Follow these steps to access and complete your required and elective training.'),
        'training_process_step_1_title' => (string) $this->t('Register or log in to the portal'),
        'training_process_step_1_description' => (string) $this->t('Create your account or sign in to access the training catalog and your personal dashboard.'),
        'training_process_step_2_title' => (string) $this->t('Browse and enroll in courses'),
        'training_process_step_2_description' => (string) $this->t('Explore training categories, view course details, and enroll in programs that match your development goals.'),
        'training_process_step_3_title' => (string) $this->t('Complete courses and assessments'),
        'training_process_step_3_description' => (string) $this->t('Work through modules at your own pace. Progress is saved automatically. Pass assessments to earn credit.'),
        'training_process_step_4_title' => (string) $this->t('Download certificates and track progress'),
        'training_process_step_4_description' => (string) $this->t('Completed training is recorded in your transcript. Download certificates for your records.'),
        'training_process_panel_title' => (string) $this->t('Certified Training Program'),
        'training_process_panel_subtitle' => (string) $this->t('Recognized professional development'),
        'training_process_feature_1' => (string) $this->t('Self-paced online learning'),
        'training_process_feature_2' => (string) $this->t('Progress auto-saved across sessions'),
        'training_process_feature_3' => (string) $this->t('Downloadable completion certificates'),
        'training_process_feature_4' => (string) $this->t('Instructor-reviewed assessments'),
        'training_process_feature_5' => (string) $this->t('Accessible on any device'),
        'training_process_feature_6' => (string) $this->t('Transcript and compliance reporting'),
        'training_process_panel_cta_text' => (string) $this->t('Get Started Today'),
        'faq_support_title' => (string) $this->t('Still have questions?'),
        'faq_support_subtitle' => (string) $this->t('Our support team can help'),
        'faq_support_body' => (string) $this->t("Can't find the answer you need? Our training coordinators are available to assist you with enrollment, technical issues, and certification questions."),
        'faq_support_cta_text' => (string) $this->t('Contact Support'),
        'faq_quicklinks_title' => (string) $this->t('Quick Access'),
        'faq_quicklink_catalog_text' => (string) $this->t('Course Catalog'),
        'faq_quicklink_categories_text' => (string) $this->t('All Categories'),
        'faq_quicklink_dashboard_text' => (string) $this->t('My Dashboard'),
        'faq_quicklink_certificates_text' => (string) $this->t('My Certificates'),
        'cta_banner_title' => (string) $this->t('Ready to Start Your Training?'),
        'cta_banner_description' => (string) $this->t('Browse our course catalog, enroll today, and take the first step toward professional growth.'),
        'cta_primary_text' => (string) $this->t('Browse Course Catalog'),
        'cta_secondary_text' => (string) $this->t('Create Account'),
        'cta_logged_in_secondary_text' => (string) $this->t('Go to Dashboard'),
      ],
    ];

    return $defaults[$style] ?? $defaults['glassmorphism'];
  }

  /**
   * Builds homepage stats with editable labels and values.
   */
  private function getHomepageStats(array $homepage_settings, int $course_count): array {
    $certificate_count = 0;
    if ($this->entityTypeManager->hasDefinition('lms_user_certificate')) {
      $certificate_count = (int) $this->entityTypeManager->getStorage('lms_user_certificate')
        ->getQuery()
        ->accessCheck(FALSE)
        ->count()
        ->execute();
    }

    return [
      'courses' => $course_count > 0 ? (string) $course_count : '10+',
      'students' => (string) $homepage_settings['stats_students_value'],
      'satisfaction' => (string) $homepage_settings['stats_satisfaction_value'],
      'certs' => $certificate_count > 0 ? (string) $certificate_count : '0',
      'courses_label' => (string) $homepage_settings['stats_courses_label'],
      'students_label' => (string) $homepage_settings['stats_students_label'],
      'satisfaction_label' => (string) $homepage_settings['stats_satisfaction_label'],
      'certs_label' => (string) $homepage_settings['stats_certs_label'],
    ];
  }

  /**
   * Loads homepage memberships from entity storage.
   */
  private function getHomepageMemberships(): array {
    $memberships = [];

    if ($this->entityTypeManager->hasDefinition('lms_membership_plan')) {
      $plan_ids = $this->entityTypeManager->getStorage('lms_membership_plan')
        ->getQuery()
        ->condition('status', TRUE)
        ->sort('field_price', 'ASC')
        ->range(0, 3)
        ->accessCheck(FALSE)
        ->execute();

      if ($plan_ids) {
        $plans = $this->entityTypeManager->getStorage('lms_membership_plan')->loadMultiple($plan_ids);
        foreach ($plans as $plan) {
          $features = [];
          foreach ($plan->get('field_features') as $feature_item) {
            $feature_value = trim((string) $feature_item->value);
            if ($feature_value !== '') {
              $features[] = $feature_value;
            }
          }

          $memberships[] = [
            'id' => (int) $plan->id(),
            'name' => $plan->label(),
            'price' => (float) ($plan->get('field_price')->value ?? 0),
            'currency' => 'USD',
            'description' => $plan->hasField('field_description') && !$plan->get('field_description')->isEmpty() ? $plan->get('field_description')->value : '',
            'features' => $features,
            'recommended' => $plan->hasField('field_recommended') && (bool) $plan->get('field_recommended')->value,
            'billing_cycle' => $plan->hasField('field_billing_cycle') ? (string) ($plan->get('field_billing_cycle')->value ?? 'monthly') : 'monthly',
          ];
        }
      }
    }

    return $memberships;
  }

  /**
   * Course catalog.
   */
  public function catalog(\Symfony\Component\HttpFoundation\Request $request): array {
    $search = trim($request->query->get('search', ''));
    $query_all = $request->query->all();

    $categories_input = $query_all['category'] ?? [];
    $categories = is_array($categories_input) ? $categories_input : [$categories_input];
    $categories = array_map('strval', array_filter($categories));

    $levels_input = $query_all['level'] ?? [];
    $levels = is_array($levels_input) ? $levels_input : [$levels_input];
    $levels = array_filter($levels);

    $prices_input = $query_all['price'] ?? [];
    $prices = is_array($prices_input) ? $prices_input : [$prices_input];
    $prices = array_filter($prices);

    $ratings_input = $query_all['rating'] ?? [];
    $ratings = is_array($ratings_input) ? $ratings_input : [$ratings_input];
    $ratings = array_map('intval', array_filter($ratings));

    $sort = $request->query->get('sort', 'Most Popular');

    $query = $this->entityTypeManager->getStorage('lms_course')
      ->getQuery()
      ->condition('status', TRUE)
      ->accessCheck(FALSE);

    if ($search !== '') {
      $query->condition('name', $search, 'CONTAINS');
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
   * AJAX search endpoint for course catalog.
   */
  public function searchApi(\Symfony\Component\HttpFoundation\Request $request): \Symfony\Component\HttpFoundation\JsonResponse {
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
      $query->condition('name', $search, 'CONTAINS');
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

    // Pagination logic
    $limit = 12;
    $page = (int) $request->query->get('page', 0);
    $query_count = clone $query;
    $total = (int) $query_count->count()->execute();

    $query->range($page * $limit, $limit);
    $ids = $query->execute();

    $courses = $ids
      ? $this->entityTypeManager->getStorage('lms_course')->loadMultiple($ids)
      : [];

    $cards = $this->buildCourseCards($courses);

    // Prepare translation strings for the frontend
    foreach ($cards as &$card) {
      $card['t_continue_learning'] = (string) $this->t('Continue Learning');
      $card['t_enroll_now'] = (string) $this->t('Enroll Now');
    }

    return new \Symfony\Component\HttpFoundation\JsonResponse([
      'status' => 'success',
      'total' => $total,
      'courses' => $cards,
      'pager' => [
        'current_page' => $page,
        'total_pages' => ceil($total / $limit),
      ],
    ]);
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
      ->condition('field_scope', 'course')
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
         $mapped_url = '/agent_images/image_banner_course/' . $demo_map[$course_title] . '*.png';
         $matches = glob(\Drupal::root() . $mapped_url);
         if (!empty($matches)) {
            $thumbnail_url = '/agent_images/image_banner_course/' . basename($matches[0]);
         }
      }

      // Universal fallback if still missing.
      if (empty($thumbnail_url)) {
        $encoded_title = urlencode(substr($course_title, 0, 40));
        $thumbnail_url = 'https://picsum.photos/seed/' . $encoded_title . '/1200/630';
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
        $continue_url = \Drupal\Core\Url::fromRoute('lms_course.detail', ['lms_course' => $lms_course->id()])->toString();
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

    // Load approved reviews for this course.
    $course_reviews = [];
    if (\Drupal::entityTypeManager()->hasDefinition('lms_rating')) {
      $rating_storage = $this->entityTypeManager->getStorage('lms_rating');
      $review_ids = $rating_storage->getQuery()
        ->condition('field_course', $lms_course->id())
        ->condition('field_approved', TRUE)
        ->sort('id', 'DESC')
        ->range(0, 20)
        ->accessCheck(FALSE)
        ->execute();
      if ($review_ids) {
        foreach ($rating_storage->loadMultiple($review_ids) as $rating) {
          $reviewer = $rating->getOwner();
          $reviewer_name = $reviewer ? $reviewer->getDisplayName() : 'Anonymous';
          if ($reviewer && $reviewer->hasField('field_first_name')) {
            $fn = $reviewer->get('field_first_name')->value;
            $ln = $reviewer->hasField('field_last_name') ? $reviewer->get('field_last_name')->value : '';
            $full = trim("$fn $ln");
            if ($full) $reviewer_name = $full;
          }
          $reviewer_avatar = '';
          if ($reviewer && $reviewer->hasField('user_picture') && !$reviewer->get('user_picture')->isEmpty()) {
            $avatar_file = $reviewer->get('user_picture')->entity;
            if ($avatar_file) {
              $reviewer_avatar = \Drupal::service('file_url_generator')->generateAbsoluteString($avatar_file->getFileUri());
            }
          }
          $course_reviews[] = [
            'id'             => $rating->id(),
            'score'          => (int) $rating->get('field_score')->value,
            'review'         => $rating->hasField('field_review') ? $rating->get('field_review')->value : '',
            'reviewer_name'  => $reviewer_name,
            'reviewer_avatar'=> $reviewer_avatar,
            'date'           => \Drupal::service('date.formatter')->format($rating->get('created')->value, 'medium'),
            'is_featured'    => $rating->hasField('field_is_featured') ? (bool) $rating->get('field_is_featured')->value : FALSE,
          ];
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
      '#course_reviews' => $course_reviews,
      '#rating_form' => $is_enrolled && \Drupal::moduleHandler()->moduleExists('lms_rating') ? \Drupal::formBuilder()->getForm('\Drupal\lms_rating\Form\RatingForm', $lms_course->id()) : NULL,
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

      // Count lessons across all chapters.
      $lesson_count = 0;
      if ($course->hasField('field_chapters') && !$course->get('field_chapters')->isEmpty()) {
        foreach ($course->get('field_chapters')->referencedEntities() as $chapter) {
          if ($chapter->hasField('field_lessons') && !$chapter->get('field_lessons')->isEmpty()) {
            $lesson_count += $chapter->get('field_lessons')->count();
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

      if (empty($thumbnail_url)) {
        $encoded_title = urlencode(substr($course->label(), 0, 40));
        $thumbnail_url = 'https://picsum.photos/seed/' . $encoded_title . '/600/338';
      }

      $short_description = trim((string) ($course->get('field_short_description')->value ?? ''));
      if ($short_description === '' && $course->hasField('field_description') && !$course->get('field_description')->isEmpty()) {
        $short_description = trim(strip_tags((string) $course->get('field_description')->value));
        $short_description = \Drupal\Component\Utility\Unicode::truncate($short_description, 160, TRUE, TRUE);
      }

      $cards[] = [
        'id' => $course->id(),
        'name' => $course->label(),
        'short_description' => $short_description,
        'price' => $price > 0 ? '$' . number_format($price, 2) : (string) $this->t('Free'),
        'price_raw' => $price,
        'is_free' => $price <= 0,
        'rating' => $course->hasField('field_rating') && !$course->get('field_rating')->isEmpty() ? (float) $course->get('field_rating')->value : 5.0,
        'category' => $category?->label() ?? (string) $this->t('General'),
        'category_name' => $category?->label() ?? (string) $this->t('General'),
        'category_id' => $category?->id() ?? '',
        'level' => $level?->label() ?? (string) $this->t('All Levels'),
        'level_id' => strtolower($level?->label() ?? ''),
        'duration' => $duration > 0 ? $this->lmsHelper->formatDuration($duration) : '',
        'bestseller' => (bool) $course->get('field_bestseller')->value,
        'featured' => (bool) $course->get('field_featured')->value,
        'has_thumbnail' => !empty($thumbnail_url),
        'thumbnail_url' => $thumbnail_url,
        'url' => \Drupal\Core\Url::fromRoute('lms_course.detail', ['lms_course' => $course->id()])->toString(),
        'continue_url' => $continue_url,
        'instructor_name' => $instructor_name !== '' ? $instructor_name : (string) $this->t('Instructor'),
        'instructor_avatar' => $instructor_avatar,
        'instructor_url' => $instructor_url,
        'is_enrolled' => $is_enrolled,
        'lesson_count' => $lesson_count,
      ];
    }
    return $cards;
  }

  /**
   * Glossary page.
   */
  public function glossary(): array {
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'glossary', 'status' => 1]);
    
    $terms = [];
    $alphabet = range('A', 'Z');
    
    // Initialize standard alphabet categories
    foreach ($alphabet as $letter) {
      $terms[$letter] = [];
    }
    $terms['#'] = [];
    
    foreach ($nodes as $node) {
      if (!$node->access('view')) {
        continue;
      }
      $title = $node->getTitle();
      $first_letter = strtoupper(substr($title, 0, 1));
      
      if (!in_array($first_letter, $alphabet)) {
        $first_letter = '#';
      }
      
      $category = '';
      if ($node->hasField('field_glossary_category') && !$node->get('field_glossary_category')->isEmpty()) {
          $category = $node->get('field_glossary_category')->entity->getName();
      }
      
      $related = [];
      if ($node->hasField('field_related_terms') && !$node->get('field_related_terms')->isEmpty()) {
        foreach ($node->get('field_related_terms') as $item) {
            if ($item->entity) {
                $related[] = [
                  'title' => $item->entity->getTitle(),
                  'anchor' => strtolower(str_replace(' ', '-', $item->entity->getTitle())),
                ];
            }
        }
      }
      
      $body_value = '';
      $body_format = 'full_html';
      if ($node->hasField('body') && !$node->get('body')->isEmpty()) {
        $body_value = $node->get('body')->value;
        $body_format = $node->get('body')->format ?? 'full_html';
      } elseif ($node->hasField('field_definition') && !$node->get('field_definition')->isEmpty()) {
        $body_value = $node->get('field_definition')->value;
        $body_format = $node->get('field_definition')->format ?? 'full_html';
      }

      $terms[$first_letter][] = [
        'id' => $node->id(),
        'title' => $title,
        'anchor' => strtolower(str_replace(' ', '-', $title)),
        'body' => [
          '#type' => 'processed_text',
          '#text' => $body_value,
          '#format' => $body_format,
        ],
        'category' => $category,
        'related' => $related,
      ];
    }
    
    // Sort items within each letter
    foreach ($terms as $letter => &$items) {
      usort($items, function($a, $b) {
        return strcmp($a['title'], $b['title']);
      });
    }

    return [
      '#theme' => 'lms_glossary',
      '#terms' => $terms,
      '#alphabet' => $alphabet,
      '#attached' => [
        'library' => [
          'lms_theme/global',
        ],
      ],
    ];
  }

}
