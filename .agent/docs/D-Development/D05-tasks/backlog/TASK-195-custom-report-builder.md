# Task: Custom Report Builder UI

**ID:** TASK-195
**Unit:** — (new module: lms_report_builder)
**Group:** G-ANALYTICS
**Priority:** P2
**Estimate:** 24h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a self-service custom report builder UI that lets admins and instructors create ad-hoc reports by selecting dimensions and metrics from a curated list, apply filters, preview results, save the report definition, and schedule email delivery. The backend constructs safe parameterized SQL from the user-defined specification — never raw user input in queries. A new module `lms_report_builder` owns all logic. Scheduled delivery integrates with TASK-196.

## Acceptance Criteria
- [ ] Report builder UI at `/admin/lms/reports/builder` (admin + instructor, scoped to their data)
- [ ] Dimension picker: drag-drop or checkbox selection from available dimensions (student name, course title, category, cohort, enrollment date, completion date, instructor)
- [ ] Metric picker: checkbox selection from available metrics (enrollment count, completion rate %, avg score, revenue gross, revenue net, time spent hours, certificate count)
- [ ] Filter builder: add/remove filter rows; each row = dimension + operator (=, ≠, >, <, BETWEEN, IN) + value input
- [ ] Preview: "Run preview" button executes query (LIMIT 100) and shows results table in page
- [ ] Save: user names report, saves `lms_report_definition` config entity; appears in "My reports" list
- [ ] "My reports" list at `/admin/lms/reports/my-reports`: load saved definition + run + download
- [ ] Export: CSV and XLSX (reuse TASK-190 `XlsxExportService`) for full result sets
- [ ] SQL safety: query built via `QueryBuilder` class using typed parameters only; no string interpolation from user input; SQL injection impossible
- [ ] Instructor scope: all metric queries automatically add `WHERE instructor_uid = :uid` condition when run by instructor
- [ ] Unit test: `QueryBuilder` produces correct parameterized query for 3 representative dimension+metric combinations
- [ ] Unit test: SQL injection attempt in filter value produces no error and returns 0 rows safely

## Technical Notes
- New module: `web/modules/custom/lms_report_builder/`
- `src/QueryBuilder/ReportQueryBuilder.php` — `build(ReportDefinition $def): SelectQuery` using Drupal's `\Drupal::database()->select()` API (never raw SQL)
- `src/Config/Entity/LmsReportDefinition.php` — config entity; stores dimensions[], metrics[], filters[], name, owner_uid
- `src/Controller/ReportBuilderController.php` — `builderPage()`, `previewAction()`, `saveAction()`, `myReports()`
- `src/Form/ReportBuilderForm.php` — Drupal Form API; AJAX-powered preview + dimension/metric pickers
- Dimension registry: `src/Registry/DimensionRegistry.php` — maps dimension slug → DB table+field + label + allowed roles
- Metric registry: `src/Registry/MetricRegistry.php` — maps metric slug → aggregation expression + label
- JS: `web/modules/custom/lms_report_builder/js/report-builder.js` — drag-drop via Sortable.js (add to libraries)
- SCSS: `_report-builder.scss`
- Routes: `lms_report_builder.routing.yml`
- XLSX export: call `XlsxExportService` from TASK-190 (inject as service)

## Dependencies
- TASK-014 (analytics dashboards — source tables confirmed)
- TASK-190 (revenue report — XlsxExportService established first)
- TASK-196 (scheduled report emails — builder definitions consumed by scheduler; implement together or 195 first)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: QueryBuilder safety + correctness; Behat: builder UI → preview → save → download flow)
- [ ] Security review: SQL injection test suite passes (OWASP top 10 input set)
- [ ] Instructor scope enforced on all metric queries (automated test)
