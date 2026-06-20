# D03 — Unit Spec: Checkout Workflow

**Unit ID:** U-030
**Group:** G-COMMERCE
**Title:** Checkout workflow
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned

---

## Scope

Implement end-to-end checkout flow: cart review → payment selection → gateway redirect → success/failure handling → order status update → course enrollment trigger. Covers both single course purchase and membership plan purchase.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Controller/CheckoutController.php` | Create | lms_payment |
| `src/Form/CheckoutForm.php` | Create | lms_payment |
| `src/Service/CheckoutService.php` | Create | lms_payment |
| `templates/checkout-form.html.twig` | Create | lms_payment |
| `templates/checkout-success.html.twig` | Create | lms_payment |
| `templates/checkout-cancel.html.twig` | Create | lms_payment |
| `lms_payment.routing.yml` | Modify | lms_payment |
| `src/Service/EnrollmentService.php` | Create | lms_course |

---

## Interfaces / Services

- `PaymentGatewayInterface` (U-027) — `initiatePayment()`, `getPaymentUrl()`
- `PaymentManager` (U-027) — gateway dispatch
- `Order` entity (U-025) — status transitions: `pending` → `processing` → `completed` / `failed`
- Depends on: U-025, U-026, U-027, U-028, U-029

---

## Acceptance Criteria

- [ ] User can checkout a single course (authenticated)
- [ ] User can purchase a membership plan
- [ ] Checkout redirects to gateway (Stripe/PayPal) payment page
- [ ] On success: Order status → `completed`, user enrolled in course
- [ ] On failure/cancel: Order status → `failed`, user redirected with message
- [ ] Checkout form validates required fields before redirect
- [ ] Guest checkout shows login prompt

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | — |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-025 | Order entity required |
| U-026 | MembershipPlan entity required |
| U-027 | PaymentGatewayInterface required |
| U-028 | StripeGateway must be non-stub |
| U-029 | PayPalGateway must be non-stub |

---

**Feature Flag:** None

*End of U-030-checkout-workflow.md*
