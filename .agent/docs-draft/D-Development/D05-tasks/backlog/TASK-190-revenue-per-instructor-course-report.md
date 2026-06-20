# Task: Revenue Report per Instructor + per Course

**ID:** TASK-190
**Unit:** — (extends lms_analytics + lms_order)
**Group:** G-ANALYTICS
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a revenue report for platform admins and instructors showing gross revenue, net revenue (after platform fee), and refunds — broken down per instructor and per course. Supports date range filtering. Exports to both CSV and Excel (XLSX). This report is the basis for monthly instructor payout calculation, so correctness and auditability are critical. Lives in `lms_analytics` with queries against `lms_order` tables.

## Acceptance Criteria
- [ ] Report page at `/admin/lms/reports/revenue` (admin only); `/instructor/reports/revenue` (instructor view, scoped to own courses)
- [ ] Summary row: total gross, total platform fee, total net, total refunds for current filter
- [ ] Per-instructor table: instructor name, course count, gross, platform fee (configurable % from `lms_order.settings`), net, refunds, refund count
- [ ] Per-course breakdown: accessible by expanding/clicking instructor row; shows each course's gross, net, refund amounts
- [ ] Filters: date range (order date), instructor (admin only), payment status (`paid`, `refunded`, `partial_refund`), payment method
- [ ] CSV export: per-course rows with all financial columns; UTF-8 BOM for Excel compatibility
- [ ] XLSX export: formatted spreadsheet with summary tab + per-instructor tab + per-course tab; use `phpoffice/phpspreadsheet`
- [ ] Refund rows subtracted correctly: negative `order_total` rows from `lms_order` where `status='refunded'`
- [ ] Platform fee % configurable at `/admin/config/lms/order/settings` (`platform_fee_pct`); default 30%
- [ ] Unit test: net revenue calculation correct for mixed paid + refunded orders with known fee %

## Technical Notes
- Module: `web/modules/custom/lms_analytics/` (report query) + `web/modules/custom/lms_order/` (fee config)
- `src/Report/RevenueReport.php` — `buildByInstructor(array $filters): RevenueReportResult`
- `src/Controller/ReportController.php` — extend with `revenue()` method
- Route: `lms_analytics.routing.yml` → `lms_analytics.report.revenue`
- Core query: `SELECT o.instructor_uid, o.course_id, SUM(o.total) as gross, SUM(o.refund_amount) as refunds FROM lms_order o WHERE o.created BETWEEN :start AND :end GROUP BY o.instructor_uid, o.course_id`
- Net = gross − refunds − (gross * platform_fee_pct / 100)
- XLSX: `phpoffice/phpspreadsheet` — add to Composer if not present; generate via `XlsxExportService`
- Excel export for >10k rows: background Queue export; download link emailed when ready (use `lms_notification` email)
- Instructor scope: access control on query via `WHERE o.instructor_uid = :current_uid` for instructor role

## Dependencies
- TASK-014 (analytics dashboards — lms_analytics module in place)
- TASK-046 (zoom analytics — lms_order table confirmed populated)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: net revenue formula; Behat: admin revenue report loads + XLSX downloads)
- [ ] Instructor scope confirmed (instructor cannot see other instructors' revenue)
- [ ] XLSX file opens correctly in LibreOffice Calc and Excel Online
