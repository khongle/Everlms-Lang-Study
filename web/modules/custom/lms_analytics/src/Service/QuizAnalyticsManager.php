<?php

declare(strict_types=1);

namespace Drupal\lms_analytics\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Service to manage Quiz Analytics and Reports (Phase 1 & 2).
 */
final class QuizAnalyticsManager {

  public function __construct(
    private Connection $database,
    private EntityTypeManagerInterface $entityTypeManager,
    private AccountProxyInterface $currentUser,
  ) {}

  /**
   * Get an overview of all quizzes.
   */
  public function getQuizOverview(array $filters = [], int $limit = 50, int $offset = 0): array {
    $query = $this->database->select('lms_quiz_field_data', 'q');
    $query->fields('q', ['id', 'name', 'field_threshold']);
    
    // In a real scenario, we'd join course to filter by instructor.
    if (!empty($filters['search'])) {
      $query->condition('q.name', '%' . $this->database->escapeLike($filters['search']) . '%', 'LIKE');
    }

    $query->range($offset, $limit);
    $quizzes = $query->execute()->fetchAllAssoc('id');

    if (empty($quizzes)) {
      return [];
    }
    
    $quiz_ids = array_keys($quizzes);

    // Get attempts and scores.
    $res_query = $this->database->select('lms_quiz_result', 'qr');
    $res_query->fields('qr', ['field_quiz']);
    $res_query->addExpression('COUNT(qr.id)', 'total_attempts');
    $res_query->addExpression('COUNT(DISTINCT qr.uid)', 'unique_attempts');
    $res_query->addExpression('AVG(qr.field_score)', 'avg_score');
    $res_query->join('lms_quiz_field_data', 'q2', 'q2.id = qr.field_quiz');
    $res_query->addExpression('SUM(CASE WHEN qr.field_score >= q2.field_threshold THEN 1 ELSE 0 END)', 'passed_attempts');
    $res_query->condition('qr.field_quiz', $quiz_ids, 'IN');
    $res_query->groupBy('qr.field_quiz');
    $res_stats = $res_query->execute()->fetchAllAssoc('field_quiz');

    $results = [];
    foreach ($quizzes as $qid => $quiz) {
      $attempts = isset($res_stats[$qid]) ? (int) $res_stats[$qid]->total_attempts : 0;
      $unique_attempts = isset($res_stats[$qid]) ? (int) $res_stats[$qid]->unique_attempts : 0;
      $avg_score = isset($res_stats[$qid]) ? round((float) $res_stats[$qid]->avg_score, 2) : 0;
      $passed = isset($res_stats[$qid]) ? (int) $res_stats[$qid]->passed_attempts : 0;
      
      $pass_rate = $attempts > 0 ? round(($passed / $attempts) * 100, 2) : 0;
      $fail_rate = $attempts > 0 ? (100 - $pass_rate) : 0;

      $results[$qid] = [
        'id' => $qid,
        'title' => $quiz->name,
        'pass_threshold' => $quiz->field_threshold,
        'attempts' => $attempts,
        'unique_attempts' => $unique_attempts,
        'avg_score' => $avg_score,
        'pass_rate' => $pass_rate,
        'fail_rate' => $fail_rate,
      ];
    }

    return $results;
  }

  /**
   * Get score distribution for Chart.js.
   */
  public function getScoreDistribution(int $quiz_id): array {
    $results = $this->database->select('lms_quiz_result', 'qr')
      ->fields('qr', ['field_score'])
      ->condition('field_quiz', $quiz_id)
      ->execute()
      ->fetchAll();

    $distribution = [
      '0-20' => 0,
      '21-40' => 0,
      '41-60' => 0,
      '61-80' => 0,
      '81-100' => 0,
    ];

    foreach ($results as $row) {
      $score = (float) $row->field_score;
      if ($score <= 20) $distribution['0-20']++;
      elseif ($score <= 40) $distribution['21-40']++;
      elseif ($score <= 60) $distribution['41-60']++;
      elseif ($score <= 80) $distribution['61-80']++;
      else $distribution['81-100']++;
    }

    return $distribution;
  }

  /**
   * Get hardest questions.
   */
  public function getHardestQuestions(int $quiz_id, int $limit = 5): array {
    // Phase 1 Analytics: Join questions and responses to calculate correct rate.
    // Query structure depends heavily on lms_question_response field mapping.
    // Assuming field_question exists, and field_is_correct (boolean).
    
    $query = $this->database->select('lms_question_response', 'qr');
    $query->join('lms_question_field_data', 'q', 'q.id = qr.field_question');
    $query->condition('qr.field_quiz', $quiz_id);
    $query->fields('q', ['id', 'name', 'type']);
    $query->addExpression('COUNT(qr.id)', 'total_responses');
    $query->addExpression('SUM(CASE WHEN qr.field_is_correct = 1 THEN 1 ELSE 0 END)', 'correct_responses');
    $query->groupBy('q.id');
    $query->groupBy('q.name');
    $query->groupBy('q.type');
    
    // Having condition to only get questions with at least some answers
    // Order by correct rate ascending.
    // Note: Due to limitations of SQL aggregate ordering without alias in some DBs,
    // we fetch them to PHP and sort if the correct rate is a dynamic expression.
    
    $stats = $query->execute()->fetchAll();
    
    $questions = [];
    foreach ($stats as $stat) {
      if ((int) $stat->total_responses > 0) {
        $rate = ((int) $stat->correct_responses / (int) $stat->total_responses) * 100;
        $questions[] = [
          'id' => $stat->id,
          'title' => $stat->name,
          'type' => $stat->type,
          'total' => $stat->total_responses,
          'correct' => $stat->correct_responses,
          'rate' => round($rate, 2),
        ];
      }
    }
    
    // Sort ascending by rate to find hardest.
    usort($questions, function($a, $b) {
      return $a['rate'] <=> $b['rate'];
    });
    
    return array_slice($questions, 0, $limit);
  }

}
