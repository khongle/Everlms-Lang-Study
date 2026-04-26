# Task: Kernel Test Suite — G-COMMERCE (Order, Membership, Payment)

**ID:** TASK-078
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Xây dựng PHPUnit kernel test suite cho G-COMMERCE group: `lms_order`, `lms_membership`, `lms_payment`. Dùng mock payment gateways để test toàn bộ order lifecycle mà không cần kết nối thật tới Stripe/PayPal. Membership state machine phải được test ở từng transition: `pending → active → expired → cancelled`.

Suite này quan trọng vì G-COMMERCE liên quan trực tiếp tới doanh thu — bất kỳ bug regression nào về order/payment đều ảnh hưởng nghiêm trọng. Coverage target ≥80% bắt buộc trước khi v1.0 release.

## Acceptance Criteria
- [ ] **lms_order:**
  - [ ] CRUD test; field validation (`total`, `currency`, `status`)
  - [ ] Order lifecycle: `cart → pending → paid → fulfilled`
  - [ ] Order `cancelled` transition từ `pending` và `paid`
  - [ ] Idempotency: duplicate payment webhook không tạo 2 order
- [ ] **lms_membership:**
  - [ ] State machine transitions: `pending → active`, `active → expired`, `active → cancelled`
  - [ ] Expiry date calculation (monthly/yearly plans)
  - [ ] Access grant: member có `membership_access` role capability
  - [ ] Renewal: expired membership → repurchase → `active` again
- [ ] **lms_payment:**
  - [ ] Mock gateway class implements `PaymentGatewayInterface`
  - [ ] Success payment → Order status `paid`, Membership `active`
  - [ ] Failed payment → Order stays `pending`, error logged
  - [ ] Refund flow: `paid → refunded`, Membership `cancelled`
- [ ] Coverage ≥80% on service + state machine layer
- [ ] Pass trong DDEV (`ddev phpunit --group kernel-commerce`)

## Technical Notes
- Modules: `lms_order`, `lms_membership`, `lms_payment`
- Test paths:
  - `web/modules/custom/lms_order/tests/src/Kernel/`
  - `web/modules/custom/lms_membership/tests/src/Kernel/`
  - `web/modules/custom/lms_payment/tests/src/Kernel/`
- Mock gateway: `web/modules/custom/lms_payment/tests/src/Mock/MockPaymentGateway.php`
- State machine likely uses Drupal workflows module or custom field — check `lms_membership.workflows.yml`
- PayPal dedup fix: cross-reference TASK-055 (FIND-009) để tránh regression
- Annotated `@group TEST-004`

## Dependencies
- TASK-055 (FIND-009 PayPal dedup — avoid regression)
- TASK-067 (test catalog — TEST-004)
- TASK-069 (CI gate G2)

## Definition of Done
- [ ] Code complete — Order + Membership + Payment kernel tests
- [ ] G1-G4 gates passed
- [ ] Mock gateway hoạt động, 0 real HTTP calls trong test
- [ ] Coverage ≥80% confirmed
- [ ] CI integrated
