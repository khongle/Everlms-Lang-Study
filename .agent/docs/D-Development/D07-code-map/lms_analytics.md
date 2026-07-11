# D07 Code Map - lms_analytics

`web/modules/custom/lms_analytics/`

| Area | Location |
| ---- | -------- |
| Module | `lms_analytics.info.yml`, `lms_analytics.services.yml`, `lms_analytics.module` (hook_theme), `lms_analytics.routing.yml`, `lms_analytics.libraries.yml` |
| Controller | `src/Controller/CourseReportController.php`, `src/Controller/QuizReportController.php`, `src/Controller/UserReportController.php` |
| Service | `src/Service/AnalyticsService.php`, `src/Service/CourseReportManager.php`, `src/Service/QuizAnalyticsManager.php`, `src/Service/UserReportManager.php` |
| Templates | `templates/lms-analytics-course-report.html.twig`, `templates/lms-analytics-quiz-report-detail.html.twig`, `templates/lms-analytics-quiz-report-list.html.twig`, `templates/lms-analytics-user-report.html.twig` |
| JS/CSS | `lms_analytics.libraries.yml` (Chart.js CDN), `css/lms-analytics-admin.css` |

Story: [E12S01](../D03-stories/E12S01-lms_analytics.md) · Module: [C05](../../C-Design/C05-modules/lms_analytics.md)
