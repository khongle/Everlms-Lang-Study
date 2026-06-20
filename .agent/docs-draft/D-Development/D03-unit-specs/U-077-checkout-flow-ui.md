# D03 — Unit Spec: Checkout Flow UI

**Unit ID:** U-077
**Group:** G-UI-POLISH
**Title:** Checkout flow UI
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Polish the checkout flow UI: multi-step progress indicator, order summary sidebar, coupon code input with live discount preview, payment method selection, and success/cancel pages with order confirmation.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/checkout-form.html.twig` | Modify | lms_payment |
| `templates/checkout-success.html.twig` | Modify | lms_payment |
| `templates/checkout-cancel.html.twig` | Modify | lms_payment |
| `scss/pages/_checkout.scss` | Create | lms_theme |
| `js/checkout.js` | Create | lms_theme |

---

## Acceptance Criteria

- [ ] Step indicator: 1. Review → 2. Payment → 3. Confirm
- [ ] Order summary sidebar: item(s), subtotal, discount (if coupon applied), total
- [ ] Coupon field: AJAX validation shows discount amount in real-time without full submit
- [ ] Payment method selector: radio buttons with gateway logos (Stripe card / PayPal)
- [ ] Success page: order ID, items, enrollment confirmation, CTA to start learning
- [ ] Cancel page: items still in "pending" order, option to retry payment
- [ ] All amounts formatted with VND or configured currency

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | Price manipulation via DOM inspection |

---

## Dependencies

- U-030 (checkout workflow — must be DONE first)
- U-032 (coupon system — for live discount)
- G-HARDENING complete

**Feature Flag:** None

*End of U-077-checkout-flow-ui.md*
