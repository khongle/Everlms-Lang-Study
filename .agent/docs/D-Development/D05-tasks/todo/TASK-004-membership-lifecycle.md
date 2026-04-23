# Task: Membership Lifecycle

**ID:** TASK-004
**Unit:** U-033
**Group:** G-COMMERCE
**Priority:** P0
**Estimate:** 12h
**Assigned:** unassigned
**Status:** TODO (55% done)

## Audit Notes — What Already Exists
- MembershipPlan + UserMembership entities fully defined ✅
- Activation via `lms_order.module` `_lms_order_process_completed_order()` ✅
- `MembershipManager::hasActiveMembership()` ✅
- `MembershipAccessSubscriber` redirects 403 on /learn/ to pricing page ✅
- `hook_cron()` expires memberships past end_date + email notification ✅
- `MembershipController::cancelMembership()` sets auto_renew=FALSE ✅
- Admin CRUD: pricingList, toggleStatus, pricingAddForm, pricingEditForm, userMembershipsList ✅

## Remaining Work
- [ ] Implement `MembershipManager::renew()` (auto_renew flag exists but no renewal logic)
- [ ] Add grace period handling
- [ ] Implement trial activation logic (field_trial_days on plan but never used)
- [ ] Flesh out MembershipManager with `activate()`, `expire()` as dedicated methods
- [ ] Add pre-expiry notification (7 days, 1 day before)

## Description
Triển khai membership lifecycle hoàn chỉnh: activate → access check → renew/expire qua scheduler.

## Acceptance Criteria
- [ ] Activate membership sau payment success
- [ ] Access checks: active membership → truy cập membership courses; expired → blocked
- [ ] Cron job check expiration daily
- [ ] Auto-expire memberships khi hết hạn
- [ ] Notification gửi trước khi expire (7 days, 1 day)
- [ ] Renew membership workflow
- [ ] Admin/Manager quản lý memberships

## Technical Notes
- Module: `lms_membership`
- Service: `MembershipManager::activate()`, `::checkAccess()`, `::expire()`, `::renew()`
- Hook: `hook_cron()` cho scheduled checks
- Access check integration vào lesson view controller

## Dependencies
- U-026 (Membership entities) ✅ DONE
- TASK-001 (Checkout workflow)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests cho lifecycle transitions
- [ ] Cron test cho expiration
