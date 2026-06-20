# ISSUE-005 — Anonymous Access to Notification Unread-Count Endpoint

| Field | Value |
|---|---|
| ID | `ISSUE-005` |
| Title | `/api/lms/notifications/unread-count` route uses `_access: TRUE` — accessible without authentication |
| Source | G4-Security / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `HIGH` |
| Affected page / route | `GET /api/lms/notifications/unread-count` |
| Owning Epic | E-10 (G-HARDENING) |
| Owning Story | [`hardening-security`](../../../D-Development/D03-stories/hardening-security.md) |
| Owning module(s) | `lms_notification` |

## Summary

The route `lms_notification.api_unread_count` (path `GET /api/lms/notifications/unread-count`) is declared with `_access: 'TRUE'` in `lms_notification.routing.yml`, making it accessible to anonymous users without any authentication requirement. While the controller's implementation (`NotificationController::apiUnreadCount()`) returns `{count: 0}` for anonymous users (i.e., it does not leak data for unauthenticated callers), the route requirement itself is overly permissive.

The impact in the current implementation is limited because `apiUnreadCount()` checks `$this->currentUser()->isAnonymous()` and short-circuits. However:

1. **Defence in depth** — the security boundary is enforced only in application code, not at the routing layer. A future refactor that changes the controller logic could silently break the auth boundary without any routing-layer warning.
2. **Consistent policy** — all other `lms_notification` routes use `_user_is_logged_in: TRUE`; this endpoint inconsistently breaks the pattern.
3. **Information disclosure risk** — even a `{count: 0}` response to anonymous callers confirms the endpoint exists and is operational (useful for fingerprinting). A future bug or accidental logic change could expose real counts to unauthenticated callers.
4. **CSRF surface** — `_access: TRUE` routes do not require a CSRF token for state-changing operations; if this route were ever changed to POST, it would be CSRF-vulnerable from day one.

## Reproduction

1. Open `web/modules/custom/lms_notification/lms_notification.routing.yml`.
2. Locate `lms_notification.api_unread_count`:
   ```yaml
   lms_notification.api_unread_count:
     path: '/api/lms/notifications/unread-count'
     defaults:
       _controller: '...NotificationController::apiUnreadCount'
     requirements:
       _access: 'TRUE'
   ```
3. Make a `GET` request without any session cookie or auth header:
   ```
   curl -s https://everlms.ddev.site/api/lms/notifications/unread-count
   ```
   Returns HTTP 200 `{"count":0}` — no authentication error.
4. Compare to `lms_notification.list` (path `/notifications`): requires `_user_is_logged_in: TRUE` — returns 302 to login for anonymous.

## Root Cause

The `_access: 'TRUE'` requirement was likely added intentionally to allow the Notification Bell block to poll the endpoint from JavaScript without requiring a separate CSRF token handshake. However, the `lms_notification_bell_block` renders only for logged-in users (it queries the current user's notifications), so anonymous callers of this endpoint are not a legitimate use case.

The lms_mobile_api uses a similar `_access: 'TRUE'` pattern for its public API routes (which is correct for those unauthenticated flows), and this pattern may have been copied without considering that the notification endpoint should be gated.

## Proposed Fix

Change the route requirement from `_access: 'TRUE'` to `_user_is_logged_in: TRUE`:

```yaml
lms_notification.api_unread_count:
  path: '/api/lms/notifications/unread-count'
  defaults:
    _controller: '...NotificationController::apiUnreadCount'
  requirements:
    _user_is_logged_in: TRUE
    _format: json
```

The Notification Bell block JavaScript (`js/lms-notification-bell.js`) polls this endpoint; since the block only renders for authenticated users, the JavaScript is only loaded for logged-in sessions. No functional change is required in the client-side JS.

Additionally, remove the redundant anonymous check in `NotificationController::apiUnreadCount()` — once the route gate is correct, the anonymous case is unreachable.

## Trace links

- **Story:** `D-Development/D03-stories/hardening-security.md` §Scope "Access control audit — every route must have an explicit access requirement"
- **C05 module:** `C-Design/C05-modules/lms_notification.md`
- **Facts base:** `modules-progress-comms.md` §lms_notification — routing.yml
- **Related issues:** This pattern (missing `_user_is_logged_in`) may recur in other modules; the access control audit in Story `hardening-security` will sweep all `*.routing.yml` files.
