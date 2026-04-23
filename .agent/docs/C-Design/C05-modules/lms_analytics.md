# lms_analytics — Analytics & Reporting Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_analytics` |
| Name | LMS Analytics |
| Layer | **Integration** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base` |

## Mô tả

Module analytics và reporting nội bộ: báo cáo course (enrollment, completion rate), user activity, và quiz item analysis. Cung cấp export (CSV/Excel). Dự định tích hợp GA4 nhưng hiện tại chủ yếu là internal reporting từ database.

---

## Provided Entities

Không có entity riêng. Query trực tiếp từ entities của các module khác qua `@database`.

---

## Provided Routes

| Route ID | Path | Controller | Access |
|----------|------|-----------|--------|
| `lms_analytics.course_report` | `/admin/lms/reports/courses` | `CourseReportController::build` | `administer lms_course` |
| `lms_analytics.course_report_export` | `/admin/lms/reports/courses/export` | `CourseReportController::export` | `administer lms_course` |
| `lms_analytics.user_report` | `/admin/lms/reports/users` | `UserReportController::build` | `administer users` |
| `lms_analytics.user_report_export` | `/admin/lms/reports/users/export` | `UserReportController::export` | `administer users` |
| `lms_analytics.quiz_report_list` | `/admin/lms/reports/quizzes` | `QuizReportController::buildList` | `administer lms_quiz` |
| `lms_analytics.quiz_report_detail` | `/admin/lms/reports/quizzes/{quiz_id}` | `QuizReportController::buildDetail` | `administer lms_quiz` |
| `lms_analytics.quiz_report_export` | `/admin/lms/reports/quizzes/{quiz_id}/export` | `QuizReportController::exportItemAnalysis` | `administer lms_quiz` |

---

## Provided Services

| Service ID | Class | Purpose |
|-----------|-------|---------|
| `lms_analytics.service` | `AnalyticsService` | General analytics queries; inject database, entity_type.manager |
| `lms_analytics.course_report` | `CourseReportManager` | Course-specific aggregations: enrollment rates, completion rates, revenue |
| `lms_analytics.user_report` | `UserReportManager` | User activity reporting: logins, enrollments, progress |
| `lms_analytics.quiz_analytics` | `QuizAnalyticsManager` | Quiz item analysis: per-question pass rates, distractor analysis |

---

## Provided Plugins

Không có plugin.

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

Không có custom template. Reports render thông qua Drupal render array trả từ controllers. Export routes trả về BinaryFileResponse.

Cache: reports nên có cache với moderate max-age (5-15 phút) vì query nặng. Chưa rõ có được implement chưa.

---

## Related Units

- **C05** `05-integration-theme.md` — layer overview
- `lms_course.md` — admin_reports route point đến analytics
- `lms_quiz.md` — quiz item analysis
- `lms_progress.md` — enrollment/completion data source

---

## Known Issues / Debt

- `{quiz_id}` trong route dùng raw integer, không phải entity parameter — mất access check tự động.
- Không có caching layer cho reports — heavy SQL queries trên database lớn sẽ slow.
- GA4 integration chỉ được đề cập trong description, không có code rõ ràng.
- Không có role `administer lms_quiz` được define rõ ràng — cần audit permission.
