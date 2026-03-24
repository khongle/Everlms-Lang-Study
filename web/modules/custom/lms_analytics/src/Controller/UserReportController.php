<?php

declare(strict_types=1);

namespace Drupal\lms_analytics\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\lms_analytics\Service\UserReportManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for User Report (Phase 3 & 4).
 */
class UserReportController extends ControllerBase {

  public function __construct(
    protected UserReportManager $userReportManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lms_analytics.user_report')
    );
  }

  /**
   * Builds the user report overview page.
   */
  public function build(Request $request): array {
    $filters = [
      'search' => $request->query->get('search', ''),
      'inactive_days' => $request->query->get('inactive_days', ''),
    ];

    $stats = $this->userReportManager->getOverviewList($filters, 50, 0);

    return [
      '#theme' => 'lms_analytics_user_report',
      '#stats' => $stats,
      '#filters' => $filters,
      '#attached' => [
        'library' => [
          'lms_analytics/admin_report',
        ],
      ],
    ];
  }

  /**
   * Exports the user report to CSV.
   */
  public function export(Request $request): StreamedResponse {
    $filters = [
      'search' => $request->query->get('search', ''),
      'inactive_days' => $request->query->get('inactive_days', ''),
    ];

    $stats = $this->userReportManager->getOverviewList($filters, 10000, 0);

    $response = new StreamedResponse(function () use ($stats) {
      $handle = fopen('php://output', 'w+');
      fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

      fputcsv($handle, [
        'User ID',
        'Name',
        'Email',
        'Registration Date',
        'Last Active',
        'Enrolled Courses',
        'Completed Courses',
        'Quizzes Taken',
        'Average Quiz Score',
        'Certificates',
      ]);

      foreach ($stats as $row) {
        fputcsv($handle, [
          $row['uid'],
          $row['name'],
          $row['email'],
          \Drupal::service('date.formatter')->format($row['created'], 'short'),
          $row['last_login'] ? \Drupal::service('date.formatter')->format($row['last_login'], 'short') : 'Never',
          $row['enrolled'],
          $row['completed'],
          $row['quizzes'],
          $row['avg_score'],
          $row['certs'],
        ]);
      }
      fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="user_report.csv"');

    return $response;
  }

}
