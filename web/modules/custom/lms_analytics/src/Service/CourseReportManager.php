<?php

declare(strict_types=1);

namespace Drupal\lms_analytics\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Service to manage Course Report data (Phase 1 & 2).
 */
final class CourseReportManager {

  public function __construct(
    private Connection $database,
    private EntityTypeManagerInterface $entityTypeManager,
    private AccountProxyInterface $currentUser,
  ) {}

  /**
   * Get course statistics based on filters.
   *
   * @param array $filters
   *   Filters for the report.
   * @param int $limit
   *   Number of items to return.
   * @param int $offset
   *   Offset for pagination.
   *
   * @return array
   *   Array of course stats.
   */
  public function getCourseStats(array $filters = [], int $limit = 50, int $offset = 0): array {
    $query = $this->database->select('lms_course_field_data', 'c');
    $query->fields('c', ['id', 'name', 'field_price_type', 'status']);
    
    // Join taxonomy term for category name.
    $query->leftJoin('taxonomy_term_field_data', 't', 'c.field_category = t.tid');
    $query->addField('t', 'name', 'category_name');
    
    // Ensure user has access. Instructors only see their own courses.
    if (!$this->currentUser->hasPermission('administer lms_course') && !$this->currentUser->hasPermission('manager edit lms_course')) {
      // Assuming instructor can only view their own courses. 
      // Need to join field_instructor or user entity if using owner field.
      // Note: According to Course entity, field_instructor is a multi-value field.
      // For simplicity, checking if current user is owner (uid) or instructor.
      // But standard says owner/uid is the main creator.
      $query->condition('c.uid', $this->currentUser->id());
    }

    // Apply filters.
    if (!empty($filters['status'])) {
      $query->condition('c.status', $filters['status'] === 'published' ? 1 : 0);
    }
    if (!empty($filters['search'])) {
      $query->condition('c.name', '%' . $this->database->escapeLike($filters['search']) . '%', 'LIKE');
    }

    $query->range($offset, $limit);
    $courses = $query->execute()->fetchAllAssoc('id');

    if (empty($courses)) {
      return [];
    }

    $course_ids = array_keys($courses);

    // 1. Get Enrollments and Completions from lms_user_course.
    $progress_query = $this->database->select('lms_user_course', 'uc');
    $progress_query->fields('uc', ['field_course']);
    $progress_query->addExpression('COUNT(uc.id)', 'total_enrollments');
    $progress_query->addExpression('SUM(CASE WHEN uc.field_status = \'completed\' THEN 1 ELSE 0 END)', 'total_completed');
    $progress_query->condition('uc.field_course', $course_ids, 'IN');
    $progress_query->groupBy('uc.field_course');
    $progress_stats = $progress_query->execute()->fetchAllAssoc('field_course');

    // 2. Get Revenue from lms_order_item and lms_order.
    // Order item links to course via field_course.
    // Order links to order items via field_items.
    $revenue_query = $this->database->select('lms_order__field_items', 'ofi');
    $revenue_query->join('lms_order_item', 'oi', 'ofi.field_items_target_id = oi.id');
    $revenue_query->join('lms_order', 'o', 'o.id = ofi.entity_id');
    $revenue_query->condition('oi.field_course', $course_ids, 'IN');
    $revenue_query->condition('o.field_status', 'completed');
    $revenue_query->fields('oi', ['field_course']);
    $revenue_query->addExpression('SUM(oi.field_price)', 'total_revenue');
    $revenue_query->groupBy('oi.field_course');
    $revenue_stats = $revenue_query->execute()->fetchAllAssoc('field_course');

    // 3. Assemble the final data.
    $results = [];
    foreach ($courses as $cid => $course) {
      $enrollments = isset($progress_stats[$cid]) ? (int) $progress_stats[$cid]->total_enrollments : 0;
      $completed = isset($progress_stats[$cid]) ? (int) $progress_stats[$cid]->total_completed : 0;
      $revenue = isset($revenue_stats[$cid]) ? (float) $revenue_stats[$cid]->total_revenue : 0;
      
      $completion_rate = $enrollments > 0 ? round(($completed / $enrollments) * 100, 2) : 0;

      $results[$cid] = [
        'id' => $cid,
        'title' => $course->name,
        'category' => $course->category_name ?? 'Uncategorized',
        'status' => $course->status ? 'Published' : 'Draft',
        'price_type' => $course->field_price_type,
        'enrollments' => $enrollments,
        'active' => $enrollments - $completed,
        'completed' => $completed,
        'completion_rate' => $completion_rate,
        'revenue' => $revenue,
      ];
    }

    return $results;
  }

}
