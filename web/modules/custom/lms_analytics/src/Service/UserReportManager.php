<?php

declare(strict_types=1);

namespace Drupal\lms_analytics\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Service to manage User Report data (Phase 1 & 2).
 */
final class UserReportManager {

  public function __construct(
    private Connection $database,
    private EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Get overall user statistics.
   *
   * @param array $filters
   *   Filters for the report.
   * @param int $limit
   *   Limit limit.
   * @param int $offset
   *   Offset.
   *
   * @return array
   *   Array of user stats.
   */
  public function getOverviewList(array $filters = [], int $limit = 50, int $offset = 0): array {
    // Phase 1: Use database queries directly to aggregate user records.
    $query = $this->database->select('users_field_data', 'u');
    $query->fields('u', ['uid', 'name', 'mail', 'created', 'access']);
    
    // Only fetch authenticated users, not admin (uid 1) or anonymous (uid 0).
    $query->condition('u.uid', 1, '>');
    
    // Apply filters.
    if (!empty($filters['search'])) {
      $or = $query->orConditionGroup()
        ->condition('u.name', '%' . $this->database->escapeLike($filters['search']) . '%', 'LIKE')
        ->condition('u.mail', '%' . $this->database->escapeLike($filters['search']) . '%', 'LIKE');
      $query->condition($or);
    }
    
    // Inactive filter (Phase 4 requirements).
    if (!empty($filters['inactive_days'])) {
      $threshold = \Drupal::time()->getRequestTime() - ((int) $filters['inactive_days'] * 86400);
      $query->condition('u.access', $threshold, '<');
    }

    $query->range($offset, $limit);
    $users = $query->execute()->fetchAllAssoc('uid');

    if (empty($users)) {
      return [];
    }

    $uids = array_keys($users);

    // 1. Progress Stats (Enrolled & Completed Courses) from lms_user_course.
    $course_query = $this->database->select('lms_user_course', 'uc');
    $course_query->fields('uc', ['uid']);
    $course_query->addExpression('COUNT(uc.id)', 'total_enrolled');
    $course_query->addExpression('SUM(CASE WHEN uc.field_status = \'completed\' THEN 1 ELSE 0 END)', 'total_completed');
    $course_query->condition('uc.uid', $uids, 'IN');
    $course_query->groupBy('uc.uid');
    $course_stats = $course_query->execute()->fetchAllAssoc('uid');

    // 2. Quiz Stats (Average score & attempts) from lms_quiz_result.
    $quiz_query = $this->database->select('lms_quiz_result', 'qr');
    $quiz_query->fields('qr', ['uid']);
    $quiz_query->addExpression('COUNT(qr.id)', 'quizzes_taken');
    $quiz_query->addExpression('AVG(qr.field_score)', 'avg_score');
    $quiz_query->condition('qr.uid', $uids, 'IN');
    $quiz_query->groupBy('qr.uid');
    $quiz_stats = $quiz_query->execute()->fetchAllAssoc('uid');

    // 3. Certificates Stats from lms_user_certificate.
    $cert_query = $this->database->select('lms_user_certificate', 'ucert');
    $cert_query->fields('ucert', ['uid']);
    $cert_query->addExpression('COUNT(ucert.id)', 'total_certs');
    $cert_query->condition('ucert.uid', $uids, 'IN');
    $cert_query->groupBy('ucert.uid');
    $cert_stats = $cert_query->execute()->fetchAllAssoc('uid');

    // 4. Detailed Courses Tracking
    $tracking_courses_query = $this->database->select('lms_user_course', 'uc');
    $tracking_courses_query->join('lms_course_field_data', 'c', 'c.id = uc.field_course');
    $tracking_courses_query->fields('uc', ['uid', 'field_status', 'field_progress']);
    $tracking_courses_query->addField('c', 'name', 'course_title');
    $tracking_courses_query->condition('uc.uid', $uids, 'IN');
    $courses_detail = $tracking_courses_query->execute()->fetchAll();

    $tracking_courses = [];
    foreach ($courses_detail as $row) {
      $tracking_courses[$row->uid][] = [
        'title' => $row->course_title,
        'status' => $row->field_status,
        'progress' => (float) $row->field_progress,
      ];
    }

    // 5. Detailed Quizzes Tracking
    $tracking_quizzes_query = $this->database->select('lms_quiz_result', 'qr');
    $tracking_quizzes_query->join('lms_quiz_field_data', 'q', 'q.id = qr.field_quiz');
    $tracking_quizzes_query->fields('qr', ['uid', 'field_score']);
    $tracking_quizzes_query->addField('q', 'name', 'quiz_title');
    $tracking_quizzes_query->condition('qr.uid', $uids, 'IN');
    $quizzes_detail = $tracking_quizzes_query->execute()->fetchAll();

    $tracking_quizzes = [];
    foreach ($quizzes_detail as $row) {
      $tracking_quizzes[$row->uid][] = [
        'title' => $row->quiz_title,
        'score' => (float) $row->field_score,
      ];
    }

    // Assemble final array.
    $results = [];
    foreach ($users as $uid => $user) {
      $enrolled = isset($course_stats[$uid]) ? (int) $course_stats[$uid]->total_enrolled : 0;
      $completed = isset($course_stats[$uid]) ? (int) $course_stats[$uid]->total_completed : 0;
      $quizzes = isset($quiz_stats[$uid]) ? (int) $quiz_stats[$uid]->quizzes_taken : 0;
      $avg_score = isset($quiz_stats[$uid]) ? round((float) $quiz_stats[$uid]->avg_score, 2) : 0;
      $certs = isset($cert_stats[$uid]) ? (int) $cert_stats[$uid]->total_certs : 0;

      $results[$uid] = [
        'uid' => $uid,
        'name' => $user->name,
        'email' => $user->mail,
        'created' => $user->created,
        'last_login' => $user->access,
        'enrolled' => $enrolled,
        'completed' => $completed,
        'quizzes' => $quizzes,
        'avg_score' => $avg_score,
        'certs' => $certs,
        'tracking' => [
          'courses' => $tracking_courses[$uid] ?? [],
          'quizzes' => $tracking_quizzes[$uid] ?? [],
        ],
      ];
    }

    return $results;
  }

}
