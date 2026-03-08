<?php

declare(strict_types=1);

namespace Drupal\lms_analytics\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Internal analytics and reporting service.
 */
final class AnalyticsService {

  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    private Connection $database,
  ) {}

  /**
   * Get total enrolled students count.
   */
  public function getTotalEnrollments(): int {
    return (int) $this->database->select('lms_user_course', 'uc')
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  /**
   * Get total revenue.
   */
  public function getTotalRevenue(): float {
    $result = $this->database->select('lms_order', 'o')
      ->condition('o.field_status', 'completed')
      ->addExpression('SUM(o.field_total)')
      ->execute()
      ->fetchField();
    return (float) ($result ?? 0);
  }

  /**
   * Get course completion rate.
   */
  public function getCompletionRate(): float {
    $total = (int) $this->database->select('lms_user_course', 'uc')
      ->countQuery()->execute()->fetchField();
    if ($total === 0) {
      return 0.0;
    }
    $completed = (int) $this->database->select('lms_user_course', 'uc')
      ->condition('uc.field_status', 'completed')
      ->countQuery()->execute()->fetchField();
    return round(($completed / $total) * 100, 2);
  }

}
