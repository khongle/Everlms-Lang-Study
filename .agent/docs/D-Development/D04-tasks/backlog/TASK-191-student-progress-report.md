# Task: Student Progress Report (Admin / Instructor View)

**ID:** TASK-191
**Unit:** — (extends lms_analytics)
**Group:** G-ANALYTICS
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a student progress report for admins and instructors. Shows, per student, the number of courses enrolled, percentage completed, total time spent learning, date of last activity, and quizzes passed. Admins can view all students; instructors see only students enrolled in their courses. Supports filtering by cohort and specific course. Exports to CSV.

## Acceptance Criteria
- [ ] Report page at `/admin/lms/reports/student-progress` (admin + instructor access)
- [ ] Per-student row: display name, email, courses enrolled count, avg completion %, total time spent (hh:mm), last active date, quizzes passed / quizzes attempted
- [ ] Filters: cohort (user field), course (select), date range (enrollment date), search by student name/email
- [ ] Instructor scope: query restricted to students enrolled in courses owned by current instructor
- [ ] Clicking a student row expands per-course breakdown: course title, % complete, time spent, quiz scores
- [ ] CSV export: all rows matching filters; per-course breakdown included as separate sheet or flattened rows
- [ ] Report paginated: 50 rows/page with server-side pagination
- [ ] Time spent source: sum of `lms_lesson_progress.time_spent_seconds` per user
- [ ] Cached 30 min with cache tag `lms_progress_list`; "Rebuild" button clears cache
- [ ] Unit test: per-student aggregation correct from fixture progress rows

## Technical Notes
- Module: `web/modules/custom/lms_analytics/`
- `src/Report/StudentProgressReport.php` — query builder; returns `StudentProgressRow[]`
- `src/Controller/ReportController.php` — extend with `studentProgress()` method
- Route: `lms_analytics.routing.yml` → `lms_analytics.report.student_progress`
- Core query: JOIN `users_field_data` + `lms_enrollment` + `lms_progress` + `lms_lesson_progress` + `lms_quiz_attempt` grouped by `uid`
- Time spent: `SUM(lp.time_spent_seconds)` from `lms_lesson_progress lp`; format to `hh:mm` in PHP
- Quizzes passed: `COUNT(*) WHERE qa.status='passed'` from `lms_quiz_attempt qa`
- Pagination: use Drupal `PagerSelectExtender` on query
- CSV: `CsvResponse` reusing pattern from TASK-189

## Dependencies
- TASK-014 (analytics dashboards — lms_analytics module + progress tables confirmed)
- TASK-189 (course completion rate report — ReportController + CsvResponse pattern established first)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: aggregation query; Behat: report loads + instructor scope enforced)
- [ ] CSV export includes all filter-matching rows (no pagination limit on export)
