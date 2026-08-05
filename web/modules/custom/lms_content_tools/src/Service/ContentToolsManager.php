<?php

declare(strict_types=1);

namespace Drupal\lms_content_tools\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * Service for managing content tools operations.
 */
class ContentToolsManager {

  /**
   * Entity type groups with their entity type IDs, in deletion order.
   */
  protected const ENTITY_GROUPS = [
    'introductions' => [
      'label' => 'Introduction Sections',
      'types' => ['node:introduction'],
    ],
    'slideshows' => [
      'label' => 'Slideshow Banners',
      'types' => ['node:slideshow'],
    ],
    'glossaries' => [
      'label' => 'Glossary Terms',
      'types' => ['node:glossary'],
    ],
    'notes' => [
      'label' => 'Student Notes',
      'types' => ['lms_note'],
    ],
    'faqs' => [
      'label' => 'FAQs',
      'types' => ['lms_faq'],
    ],
    'forums' => [
      'label' => 'Forum Topics & Replies',
      'types' => ['lms_forum_reply', 'lms_forum_topic'],
    ],
    'notifications' => [
      'label' => 'Notifications',
      'types' => ['lms_notification'],
    ],
    'ratings' => [
      'label' => 'Ratings & Reviews',
      'types' => ['lms_rating'],
    ],
    'badges' => [
      'label' => 'Badges & User Badges',
      'types' => ['lms_user_badge', 'lms_badge'],
    ],
    'certificates' => [
      'label' => 'Issued Certificates',
      'types' => ['lms_user_certificate'],
    ],
    'certificate_templates' => [
      'label' => 'Certificate Templates',
      'types' => ['lms_certificate_template'],
    ],
    'results' => [
      'label' => 'Quiz Results & Responses',
      'types' => ['lms_question_response', 'lms_quiz_result'],
    ],
    'progress' => [
      'label' => 'User Progress',
      'types' => ['lms_user_lesson', 'lms_user_course'],
    ],
    'orders' => [
      'label' => 'Orders & Order Items',
      'types' => ['lms_order_item', 'lms_order'],
    ],
    'memberships' => [
      'label' => 'Memberships',
      'types' => ['lms_user_membership', 'lms_membership_plan'],
    ],
    'wishlists' => [
      'label' => 'Wishlists',
      'types' => ['lms_wishlist'],
    ],
    'bundles' => [
      'label' => 'Course Bundles',
      'types' => ['lms_course_bundle'],
    ],
    'quizzes' => [
      'label' => 'Quizzes, Questions & Answers',
      'types' => ['lms_matching_pair', 'lms_answer', 'lms_question', 'lms_quiz'],
    ],
    'courses' => [
      'label' => 'Courses, Chapters & Lessons',
      'types' => ['lms_lesson', 'lms_chapter', 'lms_course'],
    ],
    'articles' => [
      'label' => 'Blog Articles',
      'types' => ['node:blog'],
    ],
    'course_taxonomies' => [
      'label' => 'Course Categories, Levels & Languages',
      'types' => [
        'taxonomy_term:course_category',
        'taxonomy_term:course_level',
        'taxonomy_term:course_language'
      ],
    ],
    'community' => [
      'label' => 'Community (Forums & Badges)',
      'types' => ['lms_forum_topic', 'lms_forum_reply', 'lms_badge'],
    ],
  ];

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected FileSystemInterface $fileSystem,
  ) {}

  /**
   * Returns the entity groups configuration.
   */
  public function getEntityGroups(): array {
    return self::ENTITY_GROUPS;
  }

  /**
   * Counts entities for a given entity type.
   */
  public function countEntities(string $entityTypeId): int {
    try {
      $parts = explode(':', $entityTypeId);
      $type = $parts[0];
      $bundle = $parts[1] ?? NULL;

      $query = $this->entityTypeManager
        ->getStorage($type)
        ->getQuery()
        ->accessCheck(FALSE);

      if ($bundle) {
        $entityDef = $this->entityTypeManager->getDefinition($type);
        $bundleKey = $entityDef->getKey('bundle');
        if ($bundleKey) {
          $query->condition($bundleKey, $bundle);
        }
      }

      return (int) $query->count()->execute();
    }
    catch (\Exception) {
      return 0;
    }
  }

  /**
   * Counts entities for a group.
   */
  public function countGroup(string $groupKey): int {
    $group = self::ENTITY_GROUPS[$groupKey] ?? NULL;
    if (!$group) {
      return 0;
    }
    $total = 0;
    foreach ($group['types'] as $entityTypeId) {
      $total += $this->countEntities($entityTypeId);
    }
    return $total;
  }

  /**
   * Deletes all entities of a given type. Returns count deleted.
   */
  public function deleteEntities(string $entityTypeId): int {
    try {
      $parts = explode(':', $entityTypeId);
      $type = $parts[0];
      $bundle = $parts[1] ?? NULL;

      $storage = $this->entityTypeManager->getStorage($type);
      $query = $storage->getQuery()->accessCheck(FALSE);

      if ($bundle) {
        $entityDef = $this->entityTypeManager->getDefinition($type);
        $bundleKey = $entityDef->getKey('bundle');
        if ($bundleKey) {
          $query->condition($bundleKey, $bundle);
        }
      }

      $ids = $query->execute();
      if (empty($ids)) {
        return 0;
      }
      $count = count($ids);
      // Delete in chunks of 50 to avoid memory issues.
      foreach (array_chunk($ids, 50) as $chunk) {
        $entities = $storage->loadMultiple($chunk);
        $storage->delete($entities);
      }
      return $count;
    }
    catch (\Exception) {
      return 0;
    }
  }

  /**
   * Deletes all entities in a group. Returns array of type => count.
   */
  public function deleteGroup(string $groupKey): array {
    $group = self::ENTITY_GROUPS[$groupKey] ?? NULL;
    if (!$group) {
      return [];
    }
    $results = [];
    foreach ($group['types'] as $entityTypeId) {
      $results[$entityTypeId] = $this->deleteEntities($entityTypeId);
    }
    return $results;
  }

  /**
   * Deletes all users except uid=1 and the current user. Returns count.
   */
  public function deleteUsers(int $currentUid): int {
    $storage = $this->entityTypeManager->getStorage('user');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('uid', [0, 1, $currentUid], 'NOT IN')
      ->execute();
    if (empty($ids)) {
      return 0;
    }
    $count = count($ids);
    foreach (array_chunk($ids, 50) as $chunk) {
      $entities = $storage->loadMultiple($chunk);
      $storage->delete($entities);
    }
    return $count;
  }

  /**
   * Returns the seed input file path.
   */
  public function getSeedInputPath(): string {
    return DRUPAL_ROOT . '/../.agent/seed/seed-input.json';
  }

  /**
   * Reads the current seed input data.
   */
  public function loadSeedInput(): ?array {
    $path = $this->getSeedInputPath();
    if (!file_exists($path)) {
      return NULL;
    }
    $content = file_get_contents($path);
    if ($content === FALSE) {
      return NULL;
    }
    return json_decode($content, TRUE);
  }

  /**
   * Saves seed input data to JSON file.
   */
  public function saveSeedInput(array $data): bool {
    $path = $this->getSeedInputPath();
    $dir = dirname($path);

    // Ensure directories exist.
    if (!is_dir($dir)) {
      $this->fileSystem->mkdir($dir, 0775, TRUE);
    }

    // Create backup if file exists.
    if (file_exists($path)) {
      $backupPath = $dir . '/seed-input.backup.json';
      copy($path, $backupPath);

      // Also save timestamped history.
      $historyDir = $dir . '/seed-history';
      if (!is_dir($historyDir)) {
        $this->fileSystem->mkdir($historyDir, 0775, TRUE);
      }
      $historyPath = $historyDir . '/seed-input-' . date('Y-m-d-His') . '.json';
      copy($path, $historyPath);
    }

    $data['version'] = '1.0';
    $data['generated_at'] = date('c');

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents($path, $json) !== FALSE;
  }

  /**
   * Returns the generated content directory path.
   */
  public function getGeneratedPath(): string {
    return DRUPAL_ROOT . '/../.agent/generated';
  }

  /**
   * Reads the manifest file from generated directory.
   */
  public function loadManifest(): ?array {
    $path = $this->getGeneratedPath() . '/manifest.json';
    if (!file_exists($path)) {
      return NULL;
    }
    $content = file_get_contents($path);
    return $content ? json_decode($content, TRUE) : NULL;
  }

}
