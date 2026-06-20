# Task: Checkout Flow UI

**ID:** TASK-026
**Unit:** U-077
**Group:** G-UI-POLISH
**Priority:** P0
**Estimate:** 10h
**Assigned:** unassigned
**Status:** TODO (70% done)

## Audit Notes — What Already Exists
- lms-checkout.html.twig (5.2KB): checkout page template ✅
- lms-bank-transfer.html.twig (3.5KB): VietQR bank transfer page ✅
- stripe_elements.js (3.6KB): Stripe Elements JS ✅
- CheckoutForm renders payment method radios, Stripe card container, PayPal button container ✅

## Remaining Work
- [ ] Add coupon input field in checkout form
- [ ] Add order summary sidebar/section (data available, not rendered)
- [ ] Add loading/processing state indicators during payment redirect
- [ ] Create success/confirmation page template
- [ ] Create cancel/retry page template
- [ ] Improve error state UX beyond Drupal messenger
- [ ] Move inline CSS styling to separate SCSS file

## Description
UI hoàn chỉnh cho checkout flow: cart → coupon → payment method → success/cancel pages.

## Acceptance Criteria
- [ ] Checkout page: order summary, coupon input, payment method selection
- [ ] Responsive design (mobile + desktop)
- [ ] Loading states khi redirect to payment
- [ ] Success page: order confirmation, access link to course
- [ ] Cancel page: retry option, back to course
- [ ] Error states: payment failed message

## Technical Notes
- Templates: `lms-checkout.html.twig`, `lms-checkout-success.html.twig`, `lms-checkout-cancel.html.twig`
- SCSS: `_checkout.scss`

## Dependencies
- TASK-001 (Checkout workflow)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Mobile responsive verified
