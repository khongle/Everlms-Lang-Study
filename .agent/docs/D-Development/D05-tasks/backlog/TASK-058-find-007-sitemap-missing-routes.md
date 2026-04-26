# Task: FIND-007 — Reconcile Sitemap vs Actual Routes (`/forum/{id}`, Revenue Report)

**ID:** TASK-058
**Unit:** U-043 (forum) + U-050 (revenue report)
**Group:** G-HARDENING
**Priority:** P2 (Functionality — Medium severity)
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
`C07-sitemap.md` lists `/forum/{id}` (forum topic detail) and `/admin/lms/reports/revenue` (revenue report), but neither route exists in any `*.routing.yml`. These are visible in navigation/links but produce 404. Either (a) add the routes and corresponding controllers/templates, or (b) remove the entries from sitemap and any UI that links to them.

## Acceptance Criteria
- [ ] **Forum topic route:** decide based on U-043 status. If U-043 is in scope for this milestone → register route `lms_forum.topic_detail` at `/forum/{id}` with controller + template. Else → remove forum topic detail link from `C07-sitemap.md` and from public footer.
- [ ] **Revenue report route:** decide based on U-050 status. If revenue report is in scope → register route `lms_analytics.report_revenue` at `/admin/lms/reports/revenue` with manager-only access. Else → remove from sitemap.
- [ ] No 404 from any prototype HTML (`C10-prototypes/html/`) link or live theme menu after fix
- [ ] If routes added: PHPUnit functional test verifying 200 for permitted role and 403 for student
- [ ] `C07-sitemap.md` Last-updated line bumped with date and decision summary

## Technical Notes
- Modules: `lms_forum`, `lms_analytics`
- Reference existing similar routes for patterns (e.g. `lms_blog.detail` for forum-detail style; `lms_analytics.dashboard` for revenue report style)
- Update `C-Design/C10-prototypes/html/manage/dashboard.html` "View revenue" link if route is removed

## Dependencies
- U-043, U-050 status — confirm with SA whether forum/revenue are in v1 scope or deferred
- Closely related to U-024 (My Courses) and TASK-039/044 if scope grows

## Definition of Done
- [ ] Routes registered or sitemap pruned
- [ ] FIND-007 status in G02-risk-register.md → CLOSED with date, commit ref, and decision rationale
- [ ] No broken navigation in production theme
