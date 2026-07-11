# ISSUE-0007 - Analytics KPIs partial / scoping unverified

**State:** open
**Severity:** P2 · **Module:** [lms_analytics](../../../C-Design/C05-modules/lms_analytics.md) · **Story:** [E12S01-lms_analytics](../../../D-Development/D03-stories/E12S01-lms_analytics.md) · **Task:** [E12S01T01](../../../D-Development/D04-tasks/todo/E12S01T01-complete-analytics-kpis.md)

## Summary
`lms_analytics` provides course/quiz/user reports + CSV export, but KPI depth is partial and role-scoping (manager sees org-wide data vs instructor should see only their own) needs verification.

## Steps to reproduce
1. Log in as an `instructor` role user who owns only a subset of courses.
2. Open `/workspace/reports` (P049 Analytics reports).
3. Compare the rows shown against what a `manager` role sees for the same period → if the instructor view is not filtered to their own courses, scoping is loose (data-exposure risk); separately, compare the KPI list shown against the required KPI set in [B03](../../../B-Requirements/B03-features/F-020-analytics-and-reporting.md) to confirm depth gaps.

## Root cause
Analytics reports were built against the aggregate query layer first (fastest path to a working dashboard); per-role query scoping (`WHERE instructor_uid = current_user`) and the full required KPI set were left as follow-up work that was not completed before this build pass closed.

## Fix
Audit reports vs the required KPI list; implement any missing KPIs; enforce role-scoped queries (instructor = own courses only, manager = org-wide) at the query layer, not just in the UI; verify CSV exports respect the same scoping.

## Regression test
No automated regression test exists yet (tracked under [ISSUE-0004](ISSUE-0004-zero-test-coverage.md) / [X01](../../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md)). Planned: an [E03](../../E03-test-definitions/_index.md) `report-scope` Kernel test asserting an instructor-scoped query never returns rows for courses they do not own, run against seeded multi-instructor data.
