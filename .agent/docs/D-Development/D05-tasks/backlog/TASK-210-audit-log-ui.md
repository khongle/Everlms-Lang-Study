# Task: Audit Log UI for Admin

**ID:** TASK-210
**Unit:** — (lms_base audit service + admin page)
**Group:** G-OPERATIONS
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Implement a centralised audit log service in `lms_base` and an admin search/filter UI. The service captures security and compliance-relevant events: user logins, role changes, admin config changes, payment refunds, and content publish/delete. The UI lets admins search and filter the log; results are exportable.

## Acceptance Criteria

- [ ] **AuditEvent entity** (content entity, write-once):
  - [ ] Fields: `event_type` (string key), `actor_uid`, `target_type`, `target_id`, `summary` (text), `details` (serialised array), `ip_address`, `created`
  - [ ] No UI edit form; no delete permission exposed
- [ ] **AuditLogService** (`lms_base.audit_log` service):
  - [ ] `record(string $event_type, int $actor_uid, ?string $target_type, ?int $target_id, string $summary, array $details = []): void`
  - [ ] Writes to DB asynchronously via Drupal queue to avoid blocking requests
  - [ ] Retry on failure (Drupal Queue API)
- [ ] **Events captured (minimum):**
  - [ ] `user.login`, `user.logout`, `user.login_failed` (with IP)
  - [ ] `user.role_granted`, `user.role_revoked` (with role name)
  - [ ] `admin.config_changed` (config key, old value, new value — redact secrets)
  - [ ] `payment.refund_issued` (charge ID, amount)
  - [ ] `content.published`, `content.deleted` (entity type, entity ID)
- [ ] **Admin UI** (`/admin/reports/lms-audit`):
  - [ ] Search: full-text on `summary` field
  - [ ] Filters: event type (multi-select), date range (from/to), actor user (autocomplete)
  - [ ] Table columns: Timestamp, Event type badge, Actor (linked), Target, Summary, IP
  - [ ] Pagination: 50 rows per page
  - [ ] "Export CSV" button (up to 10 000 rows, streamed)
- [ ] **Retention:** 24 months; cron purge older records
- [ ] PHPUnit test: service record() creates DB row; queue worker processes correctly; login event captured via subscriber

## Technical Notes

- Module: `lms_base` — new `AuditLogService`, `AuditEventSubscriber`, `AuditLogWorker` (queue worker), `AuditLogController`
- Queue: `\Drupal::queue('lms_base_audit_log')` — processed by `QueueWorkerBase` plugin
- Event subscribers: hook into `\Drupal\Core\Routing\RouteMatchInterface` for login events; `hook_user_role_grant`; `hook_config_save` for config changes; custom events dispatched by `lms_payment` for refunds
- Details field: stored as JSON blob; sensitive values (passwords, tokens) never written
- IP capture: `\Drupal::request()->getClientIp()` — respects `trusted_host_patterns`

## Dependencies

- TASK-206 (mod audit log also uses AuditLogService — coordinate schema)
- TASK-207 (feature flag toggles call AuditLogService)

## Definition of Done

- [ ] Code complete — entity, service, queue worker, event subscribers, admin UI, CSV export
- [ ] G1-G4 gates passed
- [ ] Login event appears in log within 5s of login (queue processed)
- [ ] No passwords or tokens in `details` field (verified by test)
- [ ] Tests written + passing
- [ ] No new debt introduced
