---
usecase_id: UC-WF004-003
workflow: WF-004
title: Anonymous user attempts checkout — redirect to login
type: permission
status: as-built
---

# UC-WF004-003 — Anonymous Checkout Redirect (Permission Path)

Covers the access-control path where an unauthenticated (anonymous) user attempts to access the checkout page. The controller detects the anonymous session and redirects to the login form with a destination parameter so that the user is returned to checkout after authentication.

---

## Actors

| Actor | Role |
|-------|------|
| Anonymous User | Unauthenticated visitor attempting to purchase a course |
| System | Session check and redirect |

---

## Preconditions

1. `lms_course` entity exists and is published.
2. Route `lms_order.checkout` has `_access: TRUE` — access is NOT gated by the routing system; the controller itself performs the check.

---

## Trigger

An anonymous user navigates to `/checkout/{course_id}` (e.g., from clicking "Buy Now" on a course detail page).

---

## Main Flow

| # | Given / When / Then | Entity / Code |
|---|---------------------|---------------|
| 1 | **Given** the user is not authenticated (anonymous session) and visits `/checkout/{course_id}` | Route `lms_order.checkout`; `_access: TRUE` so the route is reachable |
| 2 | **When** `CheckoutController::checkout()` runs | Calls `$this->currentUser()->isAnonymous()`; evaluates to TRUE |
| 3 | **Then** the controller returns a redirect response | Redirects to the Drupal login route (`user.login`) with `destination=/checkout/{course_id}` as a query parameter |
| 4 | **When** the user authenticates successfully | Drupal's login form reads the `destination` parameter and redirects the user back to `/checkout/{course_id}` |
| 5 | **Then** `CheckoutController::checkout()` runs again with an authenticated session | Normal checkout flow resumes (see [UC-WF004-001](UC-WF004-001-stripe-checkout.md) for paid, or free course short-circuit) |

---

## Entities Mutated

None — no entity is created or modified. This use case produces only an HTTP redirect response.

---

## Observability Hooks

| Signal | Where | Detail |
|--------|-------|--------|
| HTTP 302 redirect | `CheckoutController::checkout()` | Redirect to `/user/login?destination=/checkout/{course_id}` |
| No log entry | — | Anonymous redirect is silent; no watchdog entry is written by the controller |

---

## Acceptance Link

| Acceptance Criterion | Test Definition |
|---------------------|----------------|
| [AC-036](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md#ac-036-free-course-checkout-immediately-enrolls-the-student) — Checkout requires authentication (implicit in AC-036: "requesting user is authenticated") | E03-TEST-WF004-003-A |

---

## Notes

- The route requirement `_access: TRUE` means the routing layer allows any visitor to reach the URL; the controller is responsible for its own auth check. This is intentional — it allows the destination-aware redirect pattern. A routing-level `_user_is_logged_in: TRUE` requirement would produce a generic 403 without the destination redirect.
- The same anonymous check applies to `CheckoutController::checkoutMembership()` at `/checkout/membership/{plan_id}`.
- After successful login and redirect, if the course has become unavailable (deleted, unpublished) between the two requests, `CheckoutController::checkout()` will raise a 404 or redirect — that edge is handled by Drupal's entity parameter upcasting.
