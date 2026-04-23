# Task: Coupon System

**ID:** TASK-003
**Unit:** U-032
**Group:** G-COMMERCE
**Priority:** P0
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG (5% — schema only)

## Audit Notes
- No Coupon entity exists anywhere
- No CouponValidator or CouponManager service
- Order entity has `field_coupon_code` (string) + `field_discount` (decimal) — schema only, never populated
- No coupon input in CheckoutForm or MembershipCheckoutForm
- No coupon CRUD admin pages

## Description
Triển khai hệ thống coupon: tạo coupon codes, validate, apply vào order, track usage.

## Acceptance Criteria
- [ ] CRUD coupon (code, discount type: percent/fixed, amount, expiry, max usage)
- [ ] Apply coupon tại checkout → giảm giá đúng
- [ ] Validate: expiry date, max usage count, one-per-user
- [ ] Admin/Manager có thể tạo/quản lý coupons
- [ ] Usage tracking per coupon

## Technical Notes
- Module: `lms_order`
- Entity: `lms_coupon` (ensure fields: code, type, amount, expiry, max_uses, current_uses)
- Service: `CouponValidator`, `CouponManager`
- Admin route: `/manage/coupons`

## Dependencies
- TASK-001 (Checkout workflow)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Unit tests cho CouponValidator
- [ ] Edge case tests (expired, max usage, invalid code)
