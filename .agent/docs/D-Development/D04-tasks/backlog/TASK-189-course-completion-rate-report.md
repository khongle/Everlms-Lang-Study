# Task: Course Completion Rate Report

**ID:** TASK-189
**Unit:** — (extends lms_analytics)
**Group:** G-ANALYTICS
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build an admin/instructor-facing report showing course completion rates, average time-to-complete, and per-lesson drop-off points. The report answers: "Of all students who enrolled in this course in date range X, what percentage completed it, how long did it take on average, and where did most students stop?" Filter by date range, instructor, and category. CSV export available. Lives in `lms_analytics`.

## Acceptance Criteria
- [ ] Report page at `/admin/lms/reports/course-completion` (admin + instructor role access)
- [ ] Default view: all courses sorted by completion rate ascending (worst first)
- [ ] Per-course row: course title, enrolled count, completed count, completion rate (%), avg days to complete, drop-off lesson (lesson with highest abandonment rate)
- [ ] Filters: date range (enrollment date), instructor (user select), category (taxonomy select); applied via GET params; filter form persists on page
- [ ] Clicking a course row expands/navigates to per-lesson breakdown: lesson title, started count, completed count, drop-off rate
- [ ] CSV export button: downloads all rows matching current filters (no pagination limit); background export via Queue for >1000 rows
- [ ] Data source: `lms_enrollment` + `lms_lesson_progress` + `lms_progress` tables
- [ ] Report cached 1 h in Drupal cache with cache tags `lms_enrollment_list`, `lms_progress_list`; manual "Rebuild" button available
- [ ] Unit test: completion rate and avg time-to-complete computed correctly from fixture data

## Technical Notes
- Module: `web/modules/custom/lms_analytics/`
- `src/Report/CourseCompletionReport.php` — query builder class; returns `ReportRow[]`
- `src/Controller/ReportController.php` — extend with `courseCompletion()` method
- Route: `lms_analytics.routing.yml` → `lms_analytics.report.course_completion`
- Drop-off lesson query: `SELECT lesson_id, COUNT(*) as started, SUM(status='completed') as completed FROM lms_lesson_progress WHERE course_id=:cid GROUP BY lesson_id ORDER BY started DESC` — lesson with lowest `completed/started` ratio is drop-off
- CSV export: use Drupal `csv_serialization` or custom `CsvResponse`; for >1000 rows enqueue `CsvExportQueueWorker`
- Template: `web/themes/custom/lms_theme/templates/report/course-completion-report.html.twig`
- SCSS: `_reports.scss` (shared with other reports)

## Dependencies
- TASK-014 (analytics dashboards — lms_analytics module + event tables confirmed in place)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: completion rate formula; Behat: report page loads + CSV downloads)
- [ ] CSV export tested with 5000-row fixture (background queue path exercised)
- [ ] Instructor role confirmed scoped to their own courses only (row-level access check)
