# Task: Quiz Performance Report

**ID:** TASK-192
**Unit:** — (extends lms_analytics + lms_quiz)
**Group:** G-ANALYTICS
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a per-quiz performance report for instructors and admins. The report shows, for each quiz: average score, pass rate, attempt count distribution (histogram), and per-question correctness rate to identify weak content. Instructors use this to improve quiz quality and spot knowledge gaps. Supports filtering by date range and cohort. Lives in `lms_analytics` with queries against `lms_quiz` tables.

## Acceptance Criteria
- [ ] Report page at `/admin/lms/reports/quiz-performance` (admin + instructor access)
- [ ] Quiz list: quiz title, parent course, total attempts, unique students attempted, avg score (%), pass rate (%), avg time-to-complete (minutes)
- [ ] Filters: date range (attempt date), cohort (user field), course (select)
- [ ] Per-quiz detail page: attempt score distribution (buckets: 0-19, 20-39, 40-59, 60-79, 80-100); bar chart via Chart.js
- [ ] Per-question breakdown table: question text (truncated 80 chars), correct answer rate (%), total responses; sorted by correct rate ascending (weakest first)
- [ ] Identify "weak questions" threshold: questions with correct rate <50% highlighted in red
- [ ] Instructor scope: only quizzes belonging to their courses
- [ ] CSV export: per-question rows for all quizzes matching filters
- [ ] Cached 1 h with cache tags `lms_quiz_attempt_list`; "Rebuild" button
- [ ] Unit test: per-question correctness rate computed correctly from fixture attempt data

## Technical Notes
- Module: `web/modules/custom/lms_analytics/`
- `src/Report/QuizPerformanceReport.php` — `buildQuizList(array $filters)`, `buildQuestionBreakdown(int $quizId)`
- `src/Controller/ReportController.php` — extend with `quizPerformance()` + `quizDetail()` methods
- Routes: `lms_analytics.routing.yml` → `lms_analytics.report.quiz_performance` + `lms_analytics.report.quiz_detail`
- Core query: JOIN `lms_quiz` + `lms_quiz_attempt` + `lms_quiz_question` + `lms_quiz_response` grouped by quiz/question
- Pass rate: `COUNT(*) WHERE qa.score >= qa.passing_score / COUNT(*)` from `lms_quiz_attempt`
- Score distribution: `SELECT FLOOR(score/20)*20 AS bucket, COUNT(*) FROM lms_quiz_attempt GROUP BY bucket`
- Chart.js: already present if used elsewhere; otherwise add `web/libraries/chartjs/` or CDN reference via `lms_analytics.libraries.yml`
- Per-question correctness: `SUM(qr.is_correct) / COUNT(qr.id)` from `lms_quiz_response qr JOIN lms_quiz_question qq USING (question_id)`

## Dependencies
- TASK-014 (analytics dashboards — lms_analytics module in place)
- TASK-189 (quiz performance depends on ReportController + CSV pattern established)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: per-question correctness formula; Behat: quiz detail page shows chart + weak questions highlighted)
- [ ] Chart.js bar chart renders correctly in supported browsers
- [ ] Instructor scope enforced (cannot access other instructors' quiz data)
