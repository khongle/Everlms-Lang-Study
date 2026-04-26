# Task: Moderator Action Audit Log

**ID:** TASK-206
**Unit:** — (lms_moderation)
**Group:** G-COMMUNICATION
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Record every moderator action (approve, reject, mark as spam, ban user, unban, warn, dismiss report) in a read-only audit table. Each record captures the moderator UID, timestamp, action type, target content reference, and a human-readable reason. The audit log supports compliance and accountability requirements.

## Acceptance Criteria

- [ ] **ModerationAuditLog entity** (content entity, no edit/delete operations exposed):
  - [ ] Fields: `mod_uid`, `action` (approve/reject/spam/ban/unban/warn/dismiss), `target_type`, `target_id`, `reason`, `created`
  - [ ] No UI edit form; entity is write-once
- [ ] **Audit log written automatically** when:
  - [ ] Queue item status changes via TASK-202 UI or TASK-205 bulk actions
  - [ ] User ban/unban/warn via TASK-205 Users page
  - [ ] Report dismissed via TASK-205 Reports page
- [ ] **Audit log viewer** (`/manage/moderation/audit`):
  - [ ] Accessible to `administrator` role only (not regular moderators)
  - [ ] Columns: Timestamp, Moderator, Action, Target (linked), Reason
  - [ ] Filter: by moderator, by action type, by date range
  - [ ] Exportable to CSV (up to 10 000 rows)
  - [ ] No inline edit or delete buttons
- [ ] **Retention:** records retained for 24 months; older records purged by cron (configurable)
- [ ] PHPUnit test: approve action writes exactly one audit row with correct fields; no duplicate rows on retry

## Technical Notes

- Module: `lms_moderation`
- Entity class: `ModerationAuditLog` — override `::access()` to deny edit/delete for all roles
- `AuditLogService::record(string $action, int $mod_uid, string $target_type, int $target_id, string $reason)` — single public method; called from queue service and ban service
- DB table: `lms_moderation_audit` created via entity schema hook
- CSV export: `AuditExportController` streams CSV via `StreamedResponse`; chunked 500 rows at a time to avoid memory limit
- Cron: `lms_moderation_cron()` deletes `WHERE created < NOW() - INTERVAL 24 MONTH`

## Dependencies

- TASK-202 (moderation queue — action hooks call audit service)
- TASK-205 (moderator dashboard — ban/unban/warn hooks call audit service)

## Definition of Done

- [ ] Code complete — entity, service, viewer page, CSV export, cron purge
- [ ] G1-G4 gates passed
- [ ] Audit rows cannot be edited or deleted via UI or API
- [ ] CSV export tested with 1 000+ rows
- [ ] Tests written + passing
- [ ] No new debt introduced
