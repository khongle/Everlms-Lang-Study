# F-020 - Analytics & reporting

**Status:** partial
**Primary module:** [lms_analytics](../../C-Design/C05-modules/lms_analytics.md)
**Epic:** [E12 Analytics](../../D-Development/D02-epics/E12-analytics.md)
**Related workflows:** —
**Acceptance:** [B05 F-020](../B05-acceptance-criteria/F-020-analytics-and-reporting.md)
**Source:** `web/modules/custom/lms_analytics/`

## Requirement
Managers/instructors view dashboards and reports: enrollments over time, completion rates, quiz pass rates, revenue, and per-course/per-learner progress (`lms_analytics`, ~7 routes). Data is derived from progress, orders, and quiz results.

## Business value
Analytics turns activity into decisions — which courses sell, where learners drop off, instructor performance — and is essential for B2B reporting.

## Scope notes
- Read-only reporting layer over existing entities (no new domain entity).
- ⚠️ As-built coverage is partial — some reports/KPIs are basic or placeholder; depth to be confirmed in E06.
- Should respect role scoping (a manager sees org data; an instructor sees their courses).

## Cross references
- Design: [C05 lms_analytics](../../C-Design/C05-modules/lms_analytics.md)
- Status: [E06 implementation status](../../E-Quality/E06-implementation-status/_index.md)
