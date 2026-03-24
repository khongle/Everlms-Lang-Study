<?php

declare(strict_types=1);

namespace Drupal\lms_analytics\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\lms_analytics\Service\QuizAnalyticsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for Quiz Analytics Report (Phase 3 & 4).
 */
class QuizReportController extends ControllerBase {

  public function __construct(
    protected QuizAnalyticsManager $quizAnalyticsManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lms_analytics.quiz_analytics')
    );
  }

  /**
   * Builds the quiz report overview page.
   */
  public function buildList(Request $request): array {
    $filters = [
      'search' => $request->query->get('search', ''),
    ];

    $stats = $this->quizAnalyticsManager->getQuizOverview($filters, 50, 0);

    return [
      '#theme' => 'lms_analytics_quiz_report_list',
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
   * Builds the drill-down view for a specific quiz.
   */
  public function buildDetail(int $quiz_id): array {
    // Basic stats for the header.
    $overview = $this->quizAnalyticsManager->getQuizOverview([]);
    $quiz_stat = $overview[$quiz_id] ?? null;

    if (!$quiz_stat) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $distribution = $this->quizAnalyticsManager->getScoreDistribution($quiz_id);
    $hardest_questions = $this->quizAnalyticsManager->getHardestQuestions($quiz_id, 10);

    return [
      '#theme' => 'lms_analytics_quiz_report_detail',
      '#quiz' => $quiz_stat,
      '#distribution' => $distribution,
      '#hardest_questions' => $hardest_questions,
      '#attached' => [
        'library' => [
          'lms_analytics/admin_report',
        ],
      ],
    ];
  }

  /**
   * Exports item analysis for a specific quiz.
   */
  public function exportItemAnalysis(int $quiz_id): StreamedResponse {
    $hardest_questions = $this->quizAnalyticsManager->getHardestQuestions($quiz_id, 1000); // get all

    $response = new StreamedResponse(function () use ($hardest_questions, $quiz_id) {
      $handle = fopen('php://output', 'w+');
      fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

      fputcsv($handle, [
        'Question ID',
        'Title',
        'Type',
        'Total Responses',
        'Correct Responses',
        'Correct Rate (%)',
      ]);

      foreach ($hardest_questions as $row) {
        fputcsv($handle, [
          $row['id'],
          $row['title'],
          $row['type'],
          $row['total'],
          $row['correct'],
          $row['rate'],
        ]);
      }
      fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="quiz_' . $quiz_id . '_item_analysis.csv"');

    return $response;
  }

}
