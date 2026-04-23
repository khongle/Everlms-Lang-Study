# Task: Checkout Workflow Implementation

**ID:** TASK-001
**Unit:** U-030
**Group:** G-COMMERCE
**Priority:** P0
**Estimate:** 16h
**Assigned:** unassigned
**Status:** TODO (80% done)

## Audit Notes — What Already Exists
- CheckoutController: `checkout()`, `checkoutMembership()`, `bankTransferInstruction()`, free order auto-enroll ✅
- CheckoutForm (8.2KB): Stripe PaymentIntent, PayPal, ZaloPay, Bank Transfer radios ✅
- MembershipCheckoutForm (7.7KB): near-clone for membership plans ✅
- Order + OrderItem entity creation on submit ✅
- `hook_entity_update` triggers enrollment on status→completed ✅
- Email + in-app notification on completion ✅

## Remaining Work
- [ ] Add coupon/discount application in checkout form (field exists on Order but never populated)
- [ ] Create success/thank-you page (currently redirects to course detail)
- [ ] Implement ZaloPay properly (currently hardcoded sandbox URL)
- [ ] Add order confirmation page with access links

## Description
Triển khai luồng checkout hoàn chỉnh: tạo order → apply coupon → chọn payment gateway → redirect đến Stripe/PayPal → callback xử lý → cấp quyền học.

## Acceptance Criteria
- [ ] Student có thể checkout khóa học từ course detail page
- [ ] Order entity được tạo với status `pending`
- [ ] Apply coupon giảm giá đúng (validate expiry + usage limits)
- [ ] Redirect đến payment gateway (Stripe Checkout Session / PayPal)
- [ ] Success callback → order `completed` → cấp quyền học trong ≤30s
- [ ] Cancel callback → order stays `pending` → không cấp quyền
- [ ] Error handling cho payment failures

## Technical Notes
- Module: `lms_order`, `lms_payment`
- Controller: `CheckoutController` (mới)
- Route: `/checkout`, `/checkout/success`, `/checkout/cancel`
- Service: `OrderManager::createOrder()`, `PaymentManager::initiatePayment()`
- Template: `lms-checkout.html.twig`

## Dependencies
- U-025 (Order entity) ✅ DONE
- U-027 (PaymentManager) ✅ DONE
- TASK-002 (Webhook handler)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Unit tests cho OrderManager
- [ ] Integration test cho checkout flow
- [ ] No new debt introduced
