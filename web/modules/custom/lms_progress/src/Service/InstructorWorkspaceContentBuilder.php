<?php

declare(strict_types=1);

namespace Drupal\lms_progress\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Builds scenario-based copy for the instructor workspace.
 *
 * This service is intentionally read-only and side-effect free so it can be
 * introduced without changing existing instructor data flows.
 */
final class InstructorWorkspaceContentBuilder {

  public function __construct(
    private readonly TranslationInterface $stringTranslation,
    private readonly AccountProxyInterface $currentAccount,
    private readonly ConfigFactoryInterface $configFactory,
  ) {
  }

  /**
   * Builds the dashboard content payload.
   *
   * @param array<string, int|float> $stats
   *   Summary metrics for the current instructor.
   * @param int $enrollment_count
   *   Total learner-course enrollments in scope.
   *
   * @return array<string, mixed>
   *   Theme-ready copy plus a resolved scenario id.
   */
  public function buildDashboardContent(array $stats, int $enrollment_count): array {
    $scenario = $this->resolveScenario($stats, $enrollment_count);

    return [
      'scenario' => $scenario,
      'hero' => $this->buildHero($scenario, $stats),
      'spotlight' => $this->buildSpotlight($scenario, $stats, $enrollment_count),
      'queue' => $this->buildQueue($scenario, (int) $stats['courses']),
    ];
  }

  /**
   * Resolves the active scenario for the current dashboard state.
   */
  private function resolveScenario(array $stats, int $enrollment_count): string {
    $courses = (int) ($stats['courses'] ?? 0);
    $students = (int) ($stats['students'] ?? 0);
    $completion_rate = (float) ($stats['completion_rate'] ?? 0.0);

    if ($courses === 0) {
      return 'no_courses_assigned';
    }

    if ($enrollment_count === 0 || $students === 0) {
      return 'courses_no_enrollments';
    }

    if ($completion_rate < 20.0) {
      return 'attention_needed';
    }

    if ($courses >= 5) {
      return 'multi_course_operator';
    }

    return 'healthy_teaching';
  }

  /**
   * Builds hero copy for the current scenario.
   *
   * @param array<string, int|float> $stats
   *   Summary metrics for the current instructor.
   *
   * @return array<string, string>
   *   Theme-ready hero copy.
   */
  private function buildHero(string $scenario, array $stats): array {
    $name = trim($this->currentAccount->getDisplayName());

    $default = match ($scenario) {
      'no_courses_assigned' => [
        'eyebrow' => $this->t('Instructor workspace'),
        'title' => $this->t('Your teaching space is ready, @name', ['@name' => $name]),
        'subtitle' => $this->t('Once a course is assigned to you, this workspace will surface learner momentum, quick teaching actions, and your current roster in one place.'),
      ],
      'courses_no_enrollments' => [
        'eyebrow' => $this->t('Instructor workspace'),
        'title' => $this->t('Your courses are set up, @name', ['@name' => $name]),
        'subtitle' => $this->t('You are currently guiding @courses courses. As soon as learners enroll, this workspace will shift from setup mode into active teaching operations.', [
          '@courses' => (string) ($stats['courses'] ?? 0),
        ]),
      ],
      'attention_needed' => [
        'eyebrow' => $this->t('Instructor workspace'),
        'title' => $this->t('Your learners may need attention, @name', ['@name' => $name]),
        'subtitle' => $this->t('You are currently guiding @courses courses and @students unique learners. Start with the roster and course queue to see where progress is slowing down.', [
          '@courses' => (string) ($stats['courses'] ?? 0),
          '@students' => (string) ($stats['students'] ?? 0),
        ]),
      ],
      'multi_course_operator' => [
        'eyebrow' => $this->t('Instructor workspace'),
        'title' => $this->t('Run your teaching portfolio, @name', ['@name' => $name]),
        'subtitle' => $this->t('You are currently guiding @courses courses and @students unique learners. Use this workspace as your operating layer for monitoring, prioritising, and course actions.', [
          '@courses' => (string) ($stats['courses'] ?? 0),
          '@students' => (string) ($stats['students'] ?? 0),
        ]),
      ],
      default => [
        'eyebrow' => $this->t('Instructor workspace'),
        'title' => $this->t('Run your teaching space, @name', ['@name' => $name]),
        'subtitle' => $this->t('You are currently guiding @courses courses and @students unique learners. Review momentum, open your roster, and jump into course operations without dropping back into the learner portal.', [
          '@courses' => (string) ($stats['courses'] ?? 0),
          '@students' => (string) ($stats['students'] ?? 0),
        ]),
      ],
    };

    return $this->applyOverrides($scenario, 'hero', $default, $this->buildPlaceholderArgs($stats));
  }

  /**
   * Builds spotlight copy for the current scenario.
   *
   * @param array<string, int|float> $stats
   *   Summary metrics for the current instructor.
   *
   * @return array<string, string>
   *   Theme-ready spotlight copy.
   */
  private function buildSpotlight(string $scenario, array $stats, int $enrollment_count): array {
    $default = match ($scenario) {
      'no_courses_assigned' => [
        'category' => $this->t('Today at a glance'),
        'status' => $this->t('Waiting for assignments'),
        'title' => $this->t('Ready for your first course'),
        'copy' => $this->t('No assigned courses are visible yet. As soon as a manager or administrator assigns one to you, this snapshot will start tracking learner progress and completion.'),
      ],
      'courses_no_enrollments' => [
        'category' => $this->t('Today at a glance'),
        'status' => $this->t('@count courses in scope', ['@count' => (string) ($stats['courses'] ?? 0)]),
        'title' => $this->t('Setup is complete'),
        'copy' => $this->t('Your assigned courses are connected, but learner enrollments have not started flowing into this workspace yet. Once students enroll, progress and completion metrics will appear here automatically.'),
      ],
      'attention_needed' => [
        'category' => $this->t('Today at a glance'),
        'status' => $this->t('@count active learners', ['@count' => (string) ($stats['active_students'] ?? 0)]),
        'title' => $this->t('Teaching health needs attention'),
        'copy' => $this->t('This view is calculated only from courses currently assigned to you. Average learner progress is @progress% and the current completion rate is @completion%, so it is a good moment to review your roster and course queue.', [
          '@progress' => number_format((float) ($stats['average_progress'] ?? 0), 1),
          '@completion' => number_format((float) ($stats['completion_rate'] ?? 0), 1),
        ]),
      ],
      'multi_course_operator' => [
        'category' => $this->t('Today at a glance'),
        'status' => $this->t('@count enrollments in view', ['@count' => (string) $enrollment_count]),
        'title' => $this->t('Teaching portfolio snapshot'),
        'copy' => $this->t('You are operating across multiple assigned courses. Average learner progress is @progress% and the completion rate is @completion%, giving you a quick view of where to focus next.', [
          '@progress' => number_format((float) ($stats['average_progress'] ?? 0), 1),
          '@completion' => number_format((float) ($stats['completion_rate'] ?? 0), 1),
        ]),
      ],
      default => [
        'category' => $this->t('Today at a glance'),
        'status' => $this->t('@count active learners', ['@count' => (string) ($stats['active_students'] ?? 0)]),
        'title' => $this->t('Teaching health snapshot'),
        'copy' => $this->t('This view is calculated only from courses currently assigned to you. Average learner progress is @progress% and the current completion rate is @completion%.', [
          '@progress' => number_format((float) ($stats['average_progress'] ?? 0), 1),
          '@completion' => number_format((float) ($stats['completion_rate'] ?? 0), 1),
        ]),
      ],
    };

    return $this->applyOverrides($scenario, 'spotlight', $default, $this->buildPlaceholderArgs($stats) + [
      '@enrollments' => (string) $enrollment_count,
    ]);
  }

  /**
   * Builds course queue copy for the current scenario.
   *
   * @return array<string, string>
   *   Theme-ready queue copy.
   */
  private function buildQueue(string $scenario, int $course_count): array {
    $default = [];

    if ($scenario === 'no_courses_assigned') {
      $default = [
        'title' => $this->t('Courses in your teaching queue'),
        'subtitle' => $this->t('Assigned courses will appear here once your teaching roster is connected.'),
      ];
    }
    elseif ($scenario === 'attention_needed') {
      $default = [
        'title' => $this->t('Courses in your teaching queue'),
        'subtitle' => $this->t('Start with the courses where learners may need extra support, then jump into the roster or course editing flow as needed.'),
      ];
    }
    else {
      $default = [
        'title' => $this->t('Courses in your teaching queue'),
        'subtitle' => $this->t('You currently have @count courses in scope. Open the public page, inspect learner momentum, or jump straight into editing when your role allows it.', [
          '@count' => (string) $course_count,
        ]),
      ];
    }

    return $this->applyOverrides($scenario, 'queue', $default, [
      '@count' => (string) $course_count,
      '@courses' => (string) $course_count,
      '@name' => trim($this->currentAccount->getDisplayName()),
    ]);
  }

  /**
   * Applies config overrides for a section when they are provided.
   *
   * @param array<string, string> $default
   *   Default copy for the section.
   * @param array<string, string> $args
   *   Placeholder replacements.
   *
   * @return array<string, string>
   *   Theme-ready copy.
   */
  private function applyOverrides(string $scenario, string $section, array $default, array $args): array {
    $overrides = $this->configFactory
      ->get('lms_progress.settings')
      ->get("scenario_overrides.$scenario.$section");

    if (!is_array($overrides)) {
      return $default;
    }

    foreach ($default as $key => $value) {
      $override = trim((string) ($overrides[$key] ?? ''));
      if ($override !== '') {
        $default[$key] = strtr($override, $args);
      }
    }

    return $default;
  }

  /**
   * Common placeholder values for scenario copy.
   *
   * @param array<string, int|float> $stats
   *   Summary metrics for the current instructor.
   *
   * @return array<string, string>
   *   Placeholder replacements.
   */
  private function buildPlaceholderArgs(array $stats): array {
    return [
      '@name' => trim($this->currentAccount->getDisplayName()),
      '@courses' => (string) ($stats['courses'] ?? 0),
      '@students' => (string) ($stats['students'] ?? 0),
      '@active_students' => (string) ($stats['active_students'] ?? 0),
      '@average_progress' => number_format((float) ($stats['average_progress'] ?? 0), 1),
      '@completion_rate' => number_format((float) ($stats['completion_rate'] ?? 0), 1),
    ];
  }

  /**
   * Lightweight translation helper.
   */
  private function t(string $string, array $args = []): string {
    return (string) $this->stringTranslation->translate($string, $args);
  }

}
