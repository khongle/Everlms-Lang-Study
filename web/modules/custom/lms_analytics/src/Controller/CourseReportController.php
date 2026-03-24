<?php

declare(strict_types=1);

namespace Drupal\lms_analytics\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\lms_analytics\Service\CourseReportManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for Course Report (Phase 3).
 */
class CourseReportController extends ControllerBase {

  public function __construct(
    protected CourseReportManager $courseReportManager
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lms_analytics.course_report')
    );
  }

  /**
   * Builds the course report page.
   */
  public function build(Request $request): array {
    // 1. Build Filters Form. (Can be a dedicated Form class, but using empty array for now to just show logic).
    $filters = [
      'search' => $request->query->get('search', ''),
      'status' => $request->query->get('status', ''),
    ];

    // 2. Fetch Aggregated Data via Service.
    // Using limit 50 for simplicity. Should add a pager later.
    $stats = $this->courseReportManager->getCourseStats($filters, 50, 0);

    // 3. Prepare Render Array for Twig.
    return [
      '#theme' => 'lms_analytics_course_report',
      '#stats' => $stats,
      '#filters' => $filters,
      '#attached' => [
        'library' => [
          'lms_analytics/admin_report', // Assume we have a library for styling table.
        ],
      ],
    ];
  }

  /**
   * Exports the course report to CSV.
   */
  public function export(Request $request): StreamedResponse {
    $filters = [
      'search' => $request->query->get('search', ''),
      'status' => $request->query->get('status', ''),
    ];

    // Fetch all without limit for export.
    $stats = $this->courseReportManager->getCourseStats($filters, 10000, 0);

    $response = new StreamedResponse(function () use ($stats) {
      $handle = fopen('php://output', 'w+');
      
      // Add UTF-8 BOM for Excel.
      fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

      // Header row.
      fputcsv($handle, [
        'Course ID',
        'Title',
        'Category',
        'Status',
        'Price Type',
        'Enrollments',
        'Active',
        'Completed',
        'Completion Rate (%)',
        'Total Revenue',
      ]);

      foreach ($stats as $row) {
        fputcsv($handle, [
          $row['id'],
          $row['title'],
          $row['category'],
          $row['status'],
          $row['price_type'],
          $row['enrollments'],
          $row['active'],
          $row['completed'],
          $row['completion_rate'],
          $row['revenue'],
        ]);
      }

      fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="course_report.csv"');

    return $response;
  }

}
