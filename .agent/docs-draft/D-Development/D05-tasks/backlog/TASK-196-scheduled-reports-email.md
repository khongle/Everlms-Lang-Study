# Task: Scheduled Reports Email Delivery

**ID:** TASK-196
**Unit:** — (extends TASK-195 report builder)
**Group:** G-ANALYTICS
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Allow users to schedule automatic email delivery of any saved report definition (from TASK-195). The user configures: which saved report, frequency (daily / weekly / monthly), format (CSV / XLSX / PDF — PDF via headless Chrome or wkhtmltopdf), and one or more recipient email addresses. A Drupal Queue worker runs the report on schedule, generates the file, attaches it to an email, and sends it. Delivery log tracks success/failure per run.

## Acceptance Criteria
- [ ] Schedule UI accessible from "My reports" list (TASK-195): "Schedule delivery" button per saved report
- [ ] Schedule config form: frequency (daily at 07:00 / weekly day-of-week + time / monthly day + time), format (CSV / XLSX / PDF), recipients (multi-email field, comma-separated)
- [ ] Schedules stored in `lms_report_schedule` config entity: report_id, frequency, format, recipients[], next_run_at, active toggle
- [ ] Cron hook checks `next_run_at <= now()` for active schedules and enqueues `ReportScheduleQueueItem`
- [ ] Queue worker: runs report query via `ReportQueryBuilder` (TASK-195), generates file in requested format, sends email with attachment via `lms_notification` mail service
- [ ] PDF format: rendered via `wkhtmltopdf` binary (must be installed on server) or `dompdf` library fallback; generates styled HTML table → PDF
- [ ] `next_run_at` updated after each run to next scheduled time
- [ ] Delivery log: `lms_report_delivery_log` table (`schedule_id`, `run_at`, `status`, `format`, `recipient_count`, `error_message`)
- [ ] On consecutive 3 failures: auto-deactivate schedule + email owner with warning
- [ ] Admin view: `/admin/lms/reports/schedules` — all schedules table with last run status + toggle active
- [ ] Unit test: `next_run_at` computation correct for daily/weekly/monthly frequencies across DST boundary

## Technical Notes
- Extend: `web/modules/custom/lms_report_builder/` (add schedule feature to existing module)
- `src/Config/Entity/LmsReportSchedule.php` — config entity
- `src/Plugin/QueueWorker/ReportScheduleWorker.php` — runs report, generates file, sends email
- `src/Service/ReportFileGenerator.php` — `generate(ReportDefinition, string $format): FileInfo`; delegates to CSV / XLSX / PDF generators
- PDF: `dompdf/dompdf` (Composer); template `web/modules/custom/lms_report_builder/templates/report-pdf.html.twig`
- Email: use `\Drupal::service('plugin.manager.mail')->mail(...)` with attachment; hook into `lms_notification` mail templates
- Cron: `hook_cron()` in `lms_report_builder.module`; use Drupal Queue to decouple execution
- DB schema: `lms_report_delivery_log` — add via `hook_schema()` in `lms_report_builder.install`
- Next-run computation: `src/Service/ScheduleTimeCalculator.php` — handles daily/weekly/monthly + timezone from site settings

## Dependencies
- TASK-195 (custom report builder — ReportQueryBuilder + saved ReportDefinition entities must exist)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: ScheduleTimeCalculator across DST; QueueWorker sends email with attachment; Behat: schedule created → cron → email received)
- [ ] PDF output tested: table renders correctly for 50-row + 500-row report
- [ ] Auto-deactivate on 3 consecutive failures confirmed (test with mock failing mail service)
