---
usecase_id: UC-WF007-003
workflow: WF-007
type: error
title: Mobile OTP Session Expired Before Verification
status: as-built
---

# UC-WF007-003 — Mobile OTP Session Expired Before Verification

**Purpose:** OTP session in `keyvalue.expirable` expires (TTL = 900 s / 15 min) before the user submits the code; client receives `400 invalid_otp` and must restart registration or resend.

---

## Actors

| Actor | Type | Notes |
|-------|------|-------|
| Mobile App (Flutter) | Primary | Sends OTP verify request after session has expired |
| `MobileAuthController` | Secondary | Processes `verify-otp` and `resend-otp` endpoints |
| `keyvalue.expirable` store | Secondary | Automatically evicts sessions after TTL |

---

## Preconditions

| # | Condition |
|---|-----------|
| P1 | Registration was initiated (steps 1–8 of UC-WF007-002 completed) — `otp_token` was issued |
| P2 | More than 900 seconds have elapsed since the OTP session was stored |
| P3 | The `keyvalue.expirable` store has evicted the OTP session entry |

---

## Trigger

Flutter app POSTs to `POST /api/v1/user/register/verify-otp` with `{otp_token, otp_code}` after the OTP session has expired.

---

## Main Flow

| Step | Given | When | Then |
|------|-------|------|------|
| 1 | OTP session was stored with TTL 900 s | More than 15 min has elapsed | `keyvalue.expirable` backend evicts the entry for key `otp_token` |
| 2 | Client submits `POST /api/v1/user/register/verify-otp` with the original `{otp_token, otp_code}` | `MobileAuthController::verifyRegisterOtp()` executes | `OtpService::getOtpSession($otp_token)` returns `NULL` (key not found in expirable store) |
| 3 | Session lookup returns `NULL` | Controller branches on NULL session | Returns `400 {error:"invalid_otp", message:"Invalid or expired OTP token."}` |

**Recovery path A — Resend OTP:**

| Step | Given | When | Then |
|------|-------|------|------|
| 4a | Client has the original `otp_token` | Before it was evicted (within 15 min) | Client may call `POST /api/v1/user/register/resend-otp` with `{otp_token}` to get a new code and new token |
| 5a | `resendRegisterOtp()` finds existing session | Generates new OTP code + new 64-char session token | Old session deleted; new session stored with fresh 900 s TTL; new OTP email sent |
| 6a | New token issued | Controller returns | `200 {status:"otp_resent", otp_token:<new_token>}` — old `otp_token` is now invalid |

**Recovery path B — Restart registration:**

| Step | Given | When | Then |
|------|-------|------|------|
| 4b | Session already evicted; resend also fails | Client calls `POST /api/v1/user/register` again with same email | `register()` finds the stale blocked user created previously; deletes it (`status=0` blocked registrations are cleaned up); creates a fresh user entity; generates new OTP; returns new `otp_token` |

---

## Entities Mutated

| Entity / Store | Field / Key | Change |
|----------------|-------------|--------|
| `keyvalue.expirable` collection `lms_mobile_api.otp` | `{old_otp_token}` | Evicted by TTL (no explicit delete) |
| `user` (Drupal core) | `status` | Remains `0` (blocked) — user account never activated; will be cleaned on next registration attempt for same email |
| `keyvalue.expirable` collection `lms_mobile_api.otp` | `{new_otp_token}` | Created if resend succeeds (recovery path A) |

---

## Observability Hooks

| Hook / Log | Module | What to check |
|------------|--------|----------------|
| `MobileAuthController::verifyRegisterOtp()` | `lms_mobile_api` | Response `400 {error:"invalid_otp"}` — logged at warning level in `lms_mobile_api` logger channel |
| `MobileAuthController::resendRegisterOtp()` | `lms_mobile_api` | Response `400 {error:"invalid_request"}` if original session already evicted (resend also fails) |
| `keyvalue.expirable` | Drupal core | Collection `lms_mobile_api.otp`: key absent after TTL elapses |
| Blocked user cleanup | `lms_mobile_api` | On re-registration attempt: `user` entities with `status=0` and matching email deleted before new user created |

---

## Acceptance Link

| AC ID | Summary | Planned Test |
|-------|---------|--------------|
| [AC-069](../../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md#ac-069-email-based-registration-assigns-student-role) | Registration assigns student role (negative case: blocked user not activated) | T-069 (planned, E03 — not yet materialized) |

---

## Notes

- `keyvalue.expirable` eviction is not guaranteed to happen exactly at TTL; it depends on the Drupal backend (database keyvalue for DDEV). Keys may linger briefly past TTL but are treated as absent on read.
- Resend OTP (`resend-otp`) invalidates the old `otp_token` by deleting the old session entry before storing the new one. The old token becomes unusable even if not yet evicted by TTL.
- A `status=0` user created during a failed registration attempt consumes the email address. If the same email re-registers, the stale blocked record is cleaned up by `register()` before creating a fresh user entity. This prevents duplicate "zombie" accounts.
- No rate limiting on OTP resend or registration attempts is implemented. Multiple rapid registrations for the same email could leave multiple blocked user records until the cleanup logic on the next `register()` call removes them.
