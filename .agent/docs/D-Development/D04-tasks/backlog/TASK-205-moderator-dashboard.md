# Task: Moderator Role and Dashboard

**ID:** TASK-205
**Unit:** — (lms_moderation admin)
**Group:** G-COMMUNICATION
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Create the `moderator` Drupal role and a dedicated moderation dashboard with three sub-sections: moderation queue, user reports, and user management. Dashboard KPI cards show pending count, items resolved today, and average resolution time. Bulk actions available throughout.

## Acceptance Criteria

- [ ] **Moderator role:**
  - [ ] Role machine name: `lms_moderator`; permissions: `administer lms moderation`, `access lms moderation queue`, `view user profiles`
  - [ ] Role does NOT inherit admin permissions; principle of least privilege
  - [ ] Assignable via standard Drupal user edit UI
- [ ] **Dashboard layout** (`/manage/moderation`):
  - [ ] KPI cards row: "Pending in queue", "Resolved today", "Avg resolution time (7 days)", "Open reports"
  - [ ] KPIs refresh every 60 seconds via AJAX polling endpoint
  - [ ] Three sub-pages: Queue (`/manage/moderation/queue`), Reports (`/manage/moderation/reports`), Users (`/manage/moderation/users`)
- [ ] **Queue page** (delegates to TASK-202):
  - [ ] Embedded queue list with bulk approve/reject/spam actions
  - [ ] Filter: source type, date range, auto-flagged only
- [ ] **Reports page** (delegates to TASK-203):
  - [ ] List of UserReport entities; columns: Content, Reasons summary, Report count, Escalated, Actions
  - [ ] Actions: Dismiss report, Escalate to queue, Ban reporter (if abuse)
- [ ] **Users page** (`/manage/moderation/users`):
  - [ ] Search user by name/email
  - [ ] Per-user actions: Warn, Temporary ban (1/7/30 days), Permanent ban, Reset trust level
  - [ ] User ban creates `UserBan` entity; bans checked on login and content submission
- [ ] **Bulk actions** across all pages: process up to 50 items per bulk operation; confirmation step required
- [ ] PHPUnit test: KPI query correctness; moderator role cannot access admin config pages

## Technical Notes

- Module: `lms_moderation` — `ModerationDashboardController`, separate `ModerationUsersController`
- KPI endpoint: `GET /api/lms/moderation/kpi` (JSON); cached with `max-age=60`
- `UserBan` entity: fields `uid`, `banned_by_uid`, `ban_type` (warn/temp/perm), `expires_at`, `reason`, `created`
- Ban check: `BanCheckSubscriber` on `kernel.request` event; 403 + custom message if banned
- Permissions defined in `lms_moderation.permissions.yml`
- Twig templates in `lms_moderation/templates/admin/`

## Dependencies

- TASK-202 (moderation queue — embedded in dashboard)
- TASK-203 (user reports — embedded in dashboard)
- TASK-204 (auto-flag — flag reasons shown in queue)
- TASK-206 (audit log — all moderator actions logged)

## Definition of Done

- [ ] Code complete — role, permissions, dashboard, KPI endpoint, user ban system
- [ ] G1-G4 gates passed
- [ ] Moderator role cannot access non-moderation admin pages
- [ ] KPI values match raw DB counts
- [ ] Tests written + passing
- [ ] No new debt introduced
