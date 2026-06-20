---
workflow: WF-005
title: Membership Subscription — Use-Case Catalog
status: as-built
---

# C13 — WF-005: Membership Subscription — Use-Case Catalog

Use cases covering membership plan purchase, access gating, cron-based expiry, and cancellation. Membership activation is created by the same order hook that handles course enrollment — `_lms_order_process_completed_order()`. Access checks run through `MembershipManager::hasActiveMembership()`.

## Files in this folder

| File | ID | Type | Summary |
|------|----|------|---------|
| [UC-WF005-001-membership-purchase-activate.md](UC-WF005-001-membership-purchase-activate.md) | UC-WF005-001 | happy | Student purchases a paid membership plan; `lms_user_membership` is created and access is granted |
| [UC-WF005-002-membership-expiry-cron.md](UC-WF005-002-membership-expiry-cron.md) | UC-WF005-002 | edge | Cron runs; expired memberships are marked expired; auto-renewal is logged but not executed |
| [UC-WF005-003-access-denied-redirect.md](UC-WF005-003-access-denied-redirect.md) | UC-WF005-003 | permission | Student with no active membership attempts to access a membership-gated lesson and is redirected |

## Cross-links

- Workflow: [../../../B-Requirements/B04-business-workflows/WF-005-membership-subscription.md](../../../B-Requirements/B04-business-workflows/WF-005-membership-subscription.md)
- Acceptance criteria: [../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md)
- Primary modules: `lms_membership`, `lms_order`, `lms_payment`
- Entities mutated: `lms_order`, `lms_order_item`, `lms_user_membership`, `lms_notification`

## Known gaps

| Gap | Detail |
|-----|--------|
| Auto-renewal not implemented | `hook_cron` logs "defer to payment gateway webhook" but no webhook routes exist; `field_auto_renew=TRUE` memberships are NOT auto-charged at renewal |
| Free membership double-set bug | `createAndFulfillFreeMembershipOrder()` calls `$order->set('field_items', ...)` twice (minor bug, functional impact: second call overwrites first; net result is the same item, so single items work correctly) |
| `MembershipManager` not wired as Access plugin | `hasActiveMembership()` is called externally by `lms_course.module::hook_entity_access()`; there is no Drupal access handler or route requirement backed by this service |
