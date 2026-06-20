---
usecase_id: UC-WF005-002
workflow: WF-005
title: Cron expires memberships; auto-renewal is logged but not executed
type: edge
status: as-built (auto-renewal path = planned/stub)
---

# UC-WF005-002 — Membership Expiry via Cron (Edge Path: Auto-Renewal Not Implemented)

Covers the `hook_cron` path in `lms_membership.module` that evaluates active/trial memberships past their `field_end_date`. Members without auto-renew are expired. Members with `field_auto_renew=TRUE` have renewal deferred in a log message — no charge is made, no webhook is called.

---

## Actors

| Actor | Role |
|-------|------|
| Cron scheduler | Drupal cron trigger (e.g., `drush cron` or system cron URL) |
| System | Queries and updates `lms_user_membership` entities; sends expiry email |

---

## Preconditions

1. At least one `lms_user_membership` entity has `field_status IN [active, trial]` AND `field_end_date <= now()`.
2. Drupal cron is scheduled and fires (standard cron or `drush cron`).
3. The `lms_membership` module is enabled.

---

## Trigger

Drupal cron fires and invokes `hook_cron()` in `lms_membership.module`.

---

## Main Flow

| # | Given / When / Then | Entity / Code |
|---|---------------------|---------------|
| 1 | **Given** cron fires | Drupal invokes all `hook_cron()` implementations; `lms_membership.module::hook_cron()` runs |
| 2 | **When** the hook queries for expired memberships | Entity query: `lms_user_membership` WHERE `field_status` IN `['active', 'trial']` AND `field_end_date <= REQUEST_TIME` |
| 3 | **For each result — `field_auto_renew = FALSE`**: | Membership should expire |
| 3a | **Then** status is set to `expired` | Sets `field_status = expired`; saves entity |
| 3b | **Then** expiry email is sent | `plugin.manager.mail` → `hook_mail` key `membership_expired`; email includes plan name and expiry date |
| 4 | **For each result — `field_auto_renew = TRUE`**: | Renewal should occur (planned path) |
| 4a | **Then** the system logs a deferral message | `\Drupal::logger('lms_membership')->info('Defer renewal to payment gateway webhook')` (or equivalent) |
| 4b | **Then** no charge is attempted | No Stripe/PayPal API call is made; `field_status` is NOT changed; membership status remains `active` (or `trial`) until the next cron run |
| 5 | **When** `MembershipManager::hasActiveMembership()` is next called for the expired user | Returns FALSE (status = `expired`, or `field_end_date` check fails if status wasn't updated for auto-renew case) |
| 6 | **Then** `MembershipAccessSubscriber` intercepts access-denied on `/learn/*` or `/course/*` | Redirects user to `lms_course.pricing` route with a warning message |

---

## Entities Mutated

| Entity | Fields Changed | When |
|--------|---------------|------|
| `lms_user_membership` | `field_status: active/trial → expired` | Step 3a (auto_renew=FALSE only) |

Note: For `field_auto_renew=TRUE` memberships, NO entity fields are changed during this cron run. They remain in the expired-date-but-active-status state indefinitely until manual intervention or a future implementation of webhook auto-renewal.

---

## Observability Hooks

| Signal | Where | Detail |
|--------|-------|--------|
| Drupal mail | `lms_membership.module::hook_mail` key `membership_expired` | Sent for `field_auto_renew=FALSE` memberships; includes plan name |
| Drupal log (info) | `lms_membership.module::hook_cron()` | Log entry for deferred auto-renewal; visible at `/admin/reports/dblog` |
| No webhook received | — | No webhook routes exist; Stripe/PayPal auto-renewal events cannot be received |

---

## Acceptance Link

| Acceptance Criterion | Test Definition |
|---------------------|----------------|
| [AC-047](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md) — Cron expires memberships past end date | E03-TEST-WF005-002-A |
| [AC-048](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md) — Auto-renewal deferred (target-state/planned) | E03-TEST-WF005-002-B |

---

## Notes

- **Auto-renewal is not implemented**: The `hook_cron` comment explicitly states "defer to payment gateway webhook." No webhook routes are defined in any module. This means memberships with `field_auto_renew=TRUE` will never auto-renew until a webhook endpoint is built and the logic is added to the webhook handler.
- **Cancellation model**: Student cancellation via `MembershipController::cancelMembership()` sets `field_auto_renew=FALSE` only — the membership remains `active` until `field_end_date`. At the next cron run it will be caught by step 3 and expired.
- **Email only for expiry**: No email is sent for auto-renewal deferral — the system silently logs only.
- `MembershipAccessSubscriber` listens on `KernelEvents::EXCEPTION` at priority 50; catches `AccessDeniedHttpException` for paths starting with `/learn/` or `/course/` and redirects to pricing.
