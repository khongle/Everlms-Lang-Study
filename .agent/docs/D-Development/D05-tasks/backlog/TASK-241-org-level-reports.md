# Task: Org-Level Reports

**ID:** TASK-241
**Unit:** U-060 (analytics)
**Group:** G-MULTITENANCY
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Scope all existing analytics reports (TASK-189 through TASK-194) to the active Organization context. An Org Admin sees only their Org's data. A Platform Admin sees all Orgs plus a cross-Org rollup report. The scoping is applied via query conditions injected by a centralized `OrgReportScopeService`, so individual report queries need minimal modification.

## Context
- TASK-189–194 define the analytics report suite; this task adds the multi-tenancy dimension.
- Requires TASK-238 (Org entity) and `OrgContextService` from that task.
- TASK-241 must not break existing report behavior for Platform Admins.

## Acceptance Criteria
- [ ] `OrgReportScopeService::applyScope(SelectInterface $query, string $user_table_alias)` injects `AND uid IN (SELECT uid FROM lms_org_membership WHERE org_id = :current_org)` when active user is `org_admin`
- [ ] All six report queries (TASK-189..194) use `OrgReportScopeService::applyScope()` — no hardcoded WHERE clauses
- [ ] Platform Admin (`administrator` role) sees all data; scope service returns no-op for platform admins
- [ ] Org Admin dashboard at `/org/{org_slug}/reports` aggregates: total enrolled users, courses active, completion rate, revenue (if applicable), top 5 courses by enrollment
- [ ] Cross-Org rollup report at `/admin/lms/reports/cross-org` (Platform Admin only): table of all Orgs with summary columns: user count, course count, completion rate, revenue
- [ ] All report pages show active Org name in page title when in Org Admin context
- [ ] Export (CSV download) of any report is also Org-scoped
- [ ] Report cache tags include `org:{org_id}` so cache is invalidated per Org on data changes

## Technical Notes
- Modules: `lms_analytics` (extend), `lms_org` (OrgReportScopeService)
- `OrgReportScopeService` lives in `lms_org/src/Service/OrgReportScopeService.php`; injected into all report controllers/services in `lms_analytics`
- Scoping method: subquery on `lms_org_membership` (or `field_data_field_org_id` depending on TASK-238 implementation); avoids JOINs in report queries
- Cross-Org rollup: new `CrossOrgRollupReport` class in `lms_analytics`; query groups by `org_id` using `GROUP BY` on scoped data
- Access: `org/{org_slug}/reports` route uses `_custom_access` check that verifies user is `org_admin` of that Org
- Cache: `lms_analytics` report controllers already use cache; add `org:{org_id}` as additional cache tag via `$build['#cache']['tags'][]`

## Dependencies
- TASK-189 (analytics reports — enrollment report)
- TASK-190 (analytics reports — completion report)
- TASK-191 (analytics reports — revenue report)
- TASK-192 (analytics reports — quiz performance report)
- TASK-193 (analytics reports — lesson engagement report)
- TASK-194 (analytics reports — user activity report)
- TASK-238 (Org entity — OrgContextService, OrgMembership)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: OrgReportScopeService no-op for platform admin, scoped query for org_admin; functional: Org Admin cannot see other Org's report data)
- [ ] No new debt introduced
