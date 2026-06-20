# Task: FIND-013 — Require Auth on `GET /api/lms/notifications/unread-count`

**ID:** TASK-056
**Unit:** U-024 (scope extension; entity-access audit overlap with U-065)
**Group:** G-HARDENING
**Priority:** P1 (security — High severity)
**Estimate:** 2h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
The notification unread-count endpoint currently responds for anonymous users, leaking information about whether a system has notifications and potentially exposing per-user counts to incorrect callers. Add `_user_is_logged_in: TRUE` to the routing layer and verify the controller enforces per-UID scoping.

## Acceptance Criteria
- [ ] Route definition for `lms_notification.api.unread_count` in `lms_notification.routing.yml` includes `_user_is_logged_in: TRUE` requirement
- [ ] Anonymous request to `GET /api/lms/notifications/unread-count` returns 403 (NOT 200 with empty body)
- [ ] Authenticated request scoped to `\Drupal::currentUser()->id()` — never allows querying another user's count via tampered params
- [ ] PHPUnit kernel test covers both anonymous (403) and authenticated (200 with correct count) cases
- [ ] No regression in dashboard widget that consumes this endpoint (check `account/my-learning.html` UI binding)

## Technical Notes
- Module: `lms_notification`
- File: `lms_notification.routing.yml` + the relevant controller method
- Pattern reference: most other authenticated routes in the codebase use the same `_user_is_logged_in: TRUE` pattern; copy that
- Watch for client-side caching: existing CSS/HTML may cache the 200 response — flush Drupal page cache after fix

## Dependencies
- None (independent fix)

## Definition of Done
- [ ] Route hardened, tests pass
- [ ] FIND-013 status in G02-risk-register.md → CLOSED with date and commit ref
- [ ] Same audit pattern applied opportunistically to any sibling info endpoints if found during scope (escalate via separate task if more endpoints discovered)
