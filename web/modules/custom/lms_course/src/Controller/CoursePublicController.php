<?php

declare(strict_types=1);

namespace Drupal\lms_course\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lms_base\Service\LmsHelper;
use Drupal\lms_course\Entity\Course;
use Drupal\taxonomy\TermInterface;
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

    // Categories.
    $categories = $this->lmsHelper->getTermsByVocabulary('course_category');
    $top_categories = array_filter($categories, fn($term) => empty($term->parents) || $term->parents === [0] || (is_array($term->parents) && $term->parents[0] == 0));
    $top_categories = array_slice($top_categories, 0, 8);

    // Stats.
    $course_count = (int) $this->entityTypeManager->getStorage('lms_course')
      ->getQuery()
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    return [
      '#theme' => 'lms_homepage',
      '#featured_courses' => $this->buildCourseCards($featured_courses),
      '#categories' => $top_categories,
      '#stats' => [
        'courses' => $course_count ?: '100+',
        'students' => '50,000+',
        'satisfaction' => '98%',
      ],
    ];
  }

  /**
   * Course catalog.
   */
  public function catalog(): array {
    $ids = $this->entityTypeManager->getStorage('lms_course')
      ->getQuery()
      ->condition('status', TRUE)
      ->sort('created', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
    $courses = $ids
      ? $this->entityTypeManager->getStorage('lms_course')->loadMultiple($ids)
      : [];

    $categories = $this->lmsHelper->getTermsByVocabulary('course_category');

    return [
      '#theme' => 'lms_catalog',
      '#courses' => $this->buildCourseCards($courses),
      '#categories' => $categories,
    ];
  }

  /**
   * Course detail page.
   */
  public function detail(Course $lms_course): array {
    // Load chapters.
    $chapter_ids = $this->entityTypeManager->getStorage('lms_chapter')
      ->getQuery()
      ->condition('field_course', $lms_course->id())
      ->condition('status', TRUE)
      ->sort('weight')
      ->accessCheck(FALSE)
      ->execute();
    $chapters = $chapter_ids
      ? $this->entityTypeManager->getStorage('lms_chapter')->loadMultiple($chapter_ids)
      : [];

    // Load lessons per chapter.
    $curriculum = [];
    foreach ($chapters as $chapter) {
      $lesson_ids = $this->entityTypeManager->getStorage('lms_lesson')
        ->getQuery()
        ->condition('field_chapter', $chapter->id())
        ->condition('status', TRUE)
        ->sort('weight')
        ->accessCheck(FALSE)
        ->execute();
      $lessons = $lesson_ids
        ? $this->entityTypeManager->getStorage('lms_lesson')->loadMultiple($lesson_ids)
        : [];
      $curriculum[] = [
        'chapter' => $chapter,
        'lessons' => $lessons,
      ];
    }

    // Category & level names.
    $category = $lms_course->get('field_category')->entity;
    $level = $lms_course->get('field_level')->entity;

    return [
      '#theme' => 'lms_course_detail',
      '#course' => $lms_course,
      '#curriculum' => $curriculum,
      '#category' => $category,
      '#level' => $level,
    ];
  }

  /**
   * Category listing.
   */
  public function categories(): array {
    $categories = $this->lmsHelper->getTermsByVocabulary('course_category');

    return [
      '#theme' => 'lms_categories',
      '#categories' => $categories,
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
      $instructor_avatar = '';
      if ($instructor_entity && $instructor_entity->hasField('user_picture') && !$instructor_entity->get('user_picture')->isEmpty()) {
        $file = $instructor_entity->get('user_picture')->entity;
        if ($file) {
           $instructor_avatar = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }

      $cards[] = [
        'id' => $course->id(),
        'name' => $course->label(),
        'short_description' => $course->get('field_short_description')->value ?? '',
        'price' => $price > 0 ? '$' . number_format($price, 2) : 'Free',
        'price_raw' => $price,
        'category' => $category?->label() ?? '',
        'level' => $level?->label() ?? '',
        'duration' => $this->lmsHelper->formatDuration($duration),
        'bestseller' => (bool) $course->get('field_bestseller')->value,
        'featured' => (bool) $course->get('field_featured')->value,
        'thumbnail' => $course->get('field_thumbnail')->entity,
        'url' => $course->toUrl()->toString(),
        'instructor_name' => $instructor_name,
        'instructor_avatar' => $instructor_avatar,
      ];
    }
    return $cards;
  }

}
