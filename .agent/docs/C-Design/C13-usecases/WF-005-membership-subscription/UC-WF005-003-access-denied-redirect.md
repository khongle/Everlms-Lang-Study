---
usecase_id: UC-WF005-003
workflow: WF-005
title: Student without active membership is redirected from gated content to pricing
type: permission
status: as-built
---

# UC-WF005-003 — Membership Access Denied Redirect (Permission Path)

Covers the runtime access gate for membership-gated course content. A student with no active or trial `lms_user_membership` record attempts to access a `/learn/*` or `/course/*` path. The `MembershipAccessSubscriber` catches the resulting `AccessDeniedHttpException` and redirects to the pricing page.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Authenticated user without a valid membership |
| System | Access check via `hook_entity_access`; exception subscriber redirect |

---

## Preconditions

1. Student is authenticated but either: (a) has no `lms_user_membership` record, (b) has one with `field_status = expired` or `cancelled`, or (c) has one with `field_end_date <= now()`.
2. Target `lms_course` has `field_price_type = membership`.
3. `lms_membership.manager` service and `lms_membership.access_subscriber` event subscriber are active.
4. `MembershipManager::hasActiveMembership()` returns FALSE for the student.

---

## Trigger

Student clicks on a lesson URL (e.g., `/learn/{lesson_id}`) or a course URL (`/courses/{course_id}`) for a membership-gated course.

---

## Main Flow

| # | Given / When / Then | Entity / Code |
|---|---------------------|---------------|
| 1 | **Given** the student requests a `/learn/*` or `/course/*` URL | Drupal routing resolves the request and runs access checks |
| 2 | **When** `lms_course.module::hook_entity_access()` runs for the relevant `lms_course` or `lms_lesson` entity | The hook checks `$entity->field_price_type->value === 'membership'` (for courses) or traverses lesson → chapter → course to check course type |
| 3 | **When** the hook calls `MembershipManager::hasActiveMembership($account)` | Service queries `lms_user_membership` storage: `uid = $account->id()` AND `field_status IN [active, trial]` AND `field_end_date > REQUEST_TIME`; returns FALSE for this student |
| 4 | **Then** `hook_entity_access()` returns `AccessResult::forbidden()` | Drupal raises an `AccessDeniedHttpException` (HTTP 403) |
| 5 | **When** `MembershipAccessSubscriber::onException()` receives the exception | Event is `KernelEvents::EXCEPTION` at priority 50; subscriber checks: exception is `AccessDeniedHttpException` AND current path starts with `/learn/` or `/course/` |
| 6 | **Then** the subscriber redirects to the pricing route | `RedirectResponse` to `lms_course.pricing` (`/pricing`); sets a Drupal warning messenger message (e.g., "You need a membership to access this content") |
| 7 | **When** the student reaches `/pricing` | `PageController::pricing()` loads all active `lms_membership_plan` entities sorted by price; renders `lms_pricing` theme with plan cards and CTA links to `/checkout/membership/{plan_id}` |

---

## Entities Mutated

None — this is a read-only access check followed by a redirect. No entity is created or modified.

---

## Observability Hooks

| Signal | Where | Detail |
|--------|-------|--------|
| HTTP 302 redirect | `MembershipAccessSubscriber` | Redirects to `/pricing` |
| Drupal warning message | `MembershipAccessSubscriber` via `@messenger` | Warning shown to student on the pricing page |
| No log entry | — | Access denial is not logged by the subscriber |

---

## Acceptance Link

| Acceptance Criterion | Test Definition |
|---------------------|----------------|
| [AC-046](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md) — Membership access check gates `/learn/*` content | E03-TEST-WF005-003-A |

---

## Notes

- **Admin bypass**: `MembershipManager::hasActiveMembership()` returns `TRUE` for any user with the `administer lms_course` permission, regardless of actual membership status. Admins and managers are never blocked.
- **`MembershipAccessSubscriber` path pattern**: The subscriber checks `strpos($request->getPathInfo(), '/learn/')` and `/course/` — it is a string prefix match, not a route-aware check. Any path containing these prefixes will be caught, including hypothetical sub-paths.
- **`hook_entity_access` traversal**: For lesson entities, `lms_course.module::hook_entity_access()` traverses the lesson entity to find its parent course and checks the course's `field_price_type`. The traversal relies on `field_chapter` on the lesson (populated by relationship fields) — this field is not declared in `lms_lesson`'s `baseFieldDefinitions()` but is referenced in the access hook; it may be a configured field not yet confirmed in the facts base (`«unverified»`).
- **Free-preview lessons**: `lms_lesson.field_free_preview = TRUE` bypasses membership gating — such lessons are accessible even without a membership (handled separately in the access logic).
