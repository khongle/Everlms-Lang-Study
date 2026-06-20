---
id: TEST-006
type: Kernel + Functional
priority: P0 — Must
ac: AC-044, AC-045
feature: F-004 (Order & Membership)
module: lms_membership, lms_course
service: lms_membership.manager (MembershipManager), lms_membership.access_subscriber (MembershipAccessSubscriber)
status: NOT_YET_AUTOMATED
---

# TEST-006 — Membership Access Gate

> Verifies that `MembershipManager::hasActiveMembership()` correctly grants or denies access to membership-gated courses, that `lms_course.module` `hook_entity_access()` blocks non-members, that `MembershipAccessSubscriber` redirects to pricing on access denial, and that cron correctly expires lapsed memberships.

**Story:** [D03 lms_membership](../../D-Development/D03-stories/lms_membership.md)
**AC:** [AC-044 — Active membership grants course access (B05 AC-04)](../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md) · [AC-045 — Expired membership revokes access (B05 AC-04)](../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md)
**C12 Workflow:** [WF-005 Membership Subscription](../../C-Design/C12-workflows/WF-005-membership-subscription.md)
**C05 Module:** [lms_membership](../../C-Design/C05-modules/lms_membership.md)

---

## TC-006-A: `MembershipManager::hasActiveMembership()` — active membership returns TRUE

**Layer:** Kernel
**Given:**
- A `lms_user_membership` entity exists for the student with:
  - `field_status = active`
  - `field_end_date = NOW() + 7 days` (in the future)

**When:** `MembershipManager::hasActiveMembership($account)` is called

**Then:** Returns `TRUE`

### Test Data

| Input | Expected Output |
|-------|----------------|
| `field_status=active`, `field_end_date` future | `TRUE` |
| `field_status=trial`, `field_end_date` future | `TRUE` |
| `field_status=expired` | `FALSE` |
| `field_status=cancelled` | `FALSE` |
| `field_status=active`, `field_end_date` past | `FALSE` (end_date check: `field_end_date > now()`) |
| User has `administer lms_course` permission | `TRUE` (admins bypass membership check) |
| No `lms_user_membership` entity at all | `FALSE` |

---

## TC-006-B: `hook_entity_access()` blocks non-member from membership-gated course

**Layer:** Kernel
**Given:**
- A `lms_course` entity with `field_price_type = membership`
- A student user with NO active `lms_user_membership`

**When:** `CourseAccessControlHandler` (via `lms_course.module::hook_entity_access()`) checks `view` access on the course

**Then:** Access is DENIED (returns `AccessResult::forbidden()`)

**And when** the student has an active membership:

**Then:** Access is ALLOWED (returns `AccessResult::neutral()` — falls through to default view permission check)

---

## TC-006-C: `MembershipAccessSubscriber` redirects to pricing on `/learn/` access denial

**Layer:** Functional
**Given:**
- A student with no membership tries to access `/learn/{lms_course}/{lms_lesson}` where the course is membership-gated
- Access is denied → `AccessDeniedHttpException` is thrown

**When:** `MembershipAccessSubscriber::onException()` handles `KernelEvents::EXCEPTION` for this request

**Then:**
- Response is HTTP 302 redirect to `/pricing` (`lms_course.pricing` route)
- A Drupal warning message is displayed (via `$messenger->addWarning()`) on the redirect target

### Test Data

| Input | Expected Output |
|-------|----------------|
| No membership + `/learn/` path | Redirect to `/pricing` |
| No membership + `/courses/` path | Redirect to `/pricing` (subscriber matches `/course/` prefix too) |
| No membership + `/admin/` path | No redirect (subscriber only matches `/learn/` or `/course/` prefix) |
| Valid membership + `/learn/` path | 200 OK (no exception thrown) |

---

## TC-006-D: Cron expires lapsed memberships with `field_auto_renew = FALSE`

**Layer:** Kernel
**Given:**
- A `lms_user_membership` with `field_status = active`, `field_end_date = NOW() - 1 day`, `field_auto_renew = FALSE`

**When:** `lms_membership_cron()` executes (triggered by `hook_cron`)

**Then:**
- The `lms_user_membership.field_status` is set to `expired`
- `plugin.manager.mail` is called with key `membership_expired` (sends expiry email)
- Subsequent call to `MembershipManager::hasActiveMembership()` returns `FALSE`

**And when** `field_auto_renew = TRUE`:

**Then:** The membership is NOT set to expired; only a log entry is written; status stays `active`

### Test Data

| Input | Expected Output |
|-------|----------------|
| Past `field_end_date`, `field_auto_renew=FALSE` | `field_status=expired`; email queued |
| Past `field_end_date`, `field_auto_renew=TRUE` | `field_status` unchanged; log entry written |
| Future `field_end_date` | Not touched by cron |

---

## TC-006-E: User cancels membership (disables auto-renew)

**Layer:** Kernel
**Given:**
- A student has an active `lms_user_membership` with `field_auto_renew = TRUE`
- Student visits `/my-membership/{lms_user_membership}/cancel`

**When:** `MembershipController::cancelMembership()` processes the request

**Then:**
- `field_auto_renew` is set to `FALSE` on the membership entity
- `field_status` is NOT immediately set to `expired` (expiry happens on next cron run past end_date)
- Response redirects to `/my-membership`

---

## Setup / Teardown

- Setup: Install `lms_base`, `lms_course`, `lms_membership`; install entity schemas; create `lms_membership_plan` entity; create student user; create test course with `field_price_type = membership`
- Teardown: Delete test entities

## Notes

- `MembershipManager` source: `web/modules/custom/lms_membership/src/MembershipManager.php`
- `MembershipAccessSubscriber` source: `web/modules/custom/lms_membership/src/EventSubscriber/MembershipAccessSubscriber.php`
- `hook_entity_access` for membership check: `web/modules/custom/lms_course/lms_course.module` — the hook calls `MembershipManager::hasActiveMembership()` but this is not injected via DI; it uses `\Drupal::service()` (module-level hook)
- Auto-renewal via webhook is explicitly NOT implemented as of 2026-06-20 (deferred; see `hook_cron` comment in `lms_membership.module`) — do not write webhook renewal tests
- Expected file: `web/modules/custom/lms_membership/tests/src/Kernel/MembershipAccessTest.php` (TC-006-A/B/D/E) + `web/modules/custom/lms_membership/tests/src/Functional/MembershipRedirectTest.php` (TC-006-C)
