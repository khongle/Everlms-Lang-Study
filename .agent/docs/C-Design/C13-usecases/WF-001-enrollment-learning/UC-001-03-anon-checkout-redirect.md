---
usecase_id: UC-001-03
workflow: WF-001-enrollment-learning
type: permission
---

# UC-001-03 — Anonymous Visitor Hits Checkout → Login Redirect

**One-line purpose:** An unauthenticated visitor attempts to access a course checkout page; the system redirects them to login rather than showing an error.

---

## Actors

| Actor | Role |
|-------|------|
| Anonymous Visitor | Unauthenticated user |
| `CheckoutController` | Detects anonymous session and redirects |

---

## Preconditions

- User is not authenticated (`current_user->isAnonymous() === TRUE`).
- `lms_course` entity with the given `course_id` exists.
- The route `lms_order.checkout` has `_access: TRUE` (intentionally public) — access is enforced by the controller, not the routing layer.

---

## Trigger

Anonymous visitor navigates to `/checkout/{course_id}`.

---

## Main Flow

| # | Given / When / Then |
|---|---------------------|
| 1 | **Given** an unauthenticated user navigates to `/checkout/{course_id}` |
| 2 | **When** `CheckoutController::checkout()` runs |
| 3 | **Then** the controller calls `$current_user->isAnonymous()` and detects anonymous |
| 4 | **Then** the controller returns a `RedirectResponse` to `/user/login` (with destination query parameter pointing back to the checkout URL) |
| 5 | **Then** the browser receives a 302 redirect to the login page |
| 6 | **When** the user authenticates |
| 7 | **Then** Drupal's login redirect system returns the user to `/checkout/{course_id}` and the normal checkout flow (UC-001-01 or UC-001-02) resumes |

---

## Entities Mutated

None — no entities are created or modified during this redirect flow.

---

## Observability Hooks

| Signal | Mechanism | Details |
|--------|-----------|---------|
| Redirect response | HTTP 302 | No Drupal log entry; standard browser redirect |

---

## Acceptance Link

| B05 AC | E03 Test (planned) |
|--------|--------------------|
| [AC-036](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md#ac-036-free-course-checkout-immediately-enrolls-the-student) — checkout requires auth (implied) | TEST-036-anon |

---

## Notes

- The route is `_access: TRUE`, so Drupal's routing layer does not block anonymous access. The authentication check is entirely inside `CheckoutController::checkout()`. This means the route is technically accessible but the controller always redirects before displaying anything.
- A similar pattern applies to `lms_order.checkout_membership` for membership plan checkout — `CheckoutController::checkoutMembership()` applies the same anonymous check.
- The same pattern applies to the lesson view: `lms_progress.lesson_view` uses `_user_is_logged_in: TRUE` at the routing layer, which produces a standard Drupal access denied / login redirect.
