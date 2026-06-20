# D03 — Unit Spec: MembershipPlan + UserMembership Entities

**Unit ID:** U-026
**Group:** G-COMMERCE
**Title:** MembershipPlan + UserMembership entities
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the `lms_membership_plan` and `lms_user_membership` custom content entities for subscription-based access control. Includes `MembershipManager` service, admin and member controllers, event subscriber for access enforcement, and admin templates.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_membership/lms_membership.info.yml` | Module declaration |
| `web/modules/custom/lms_membership/lms_membership.module` | Hook implementations |
| `web/modules/custom/lms_membership/lms_membership.routing.yml` | Membership routes |
| `web/modules/custom/lms_membership/lms_membership.services.yml` | Service registration |
| `web/modules/custom/lms_membership/lms_membership.links.menu.yml` | Admin menu links |
| `web/modules/custom/lms_membership/lms_membership.links.action.yml` | Action links |
| `web/modules/custom/lms_membership/src/Entity/MembershipPlan.php` | MembershipPlan entity class |
| `web/modules/custom/lms_membership/src/Entity/UserMembership.php` | UserMembership entity class |
| `web/modules/custom/lms_membership/src/MembershipManager.php` | Membership service |
| `web/modules/custom/lms_membership/src/MembershipPlanListBuilder.php` | Plan list builder |
| `web/modules/custom/lms_membership/src/UserMembershipListBuilder.php` | User membership list builder |
| `web/modules/custom/lms_membership/src/Controller/MembershipController.php` | Member-facing controller |
| `web/modules/custom/lms_membership/src/Controller/MembershipAdminController.php` | Admin controller |
| `web/modules/custom/lms_membership/src/EventSubscriber/MembershipAccessSubscriber.php` | Access enforcement subscriber |
| `web/modules/custom/lms_membership/templates/lms-admin-pricing.html.twig` | Admin pricing template |
| `web/modules/custom/lms_membership/templates/lms-admin-user-memberships.html.twig` | Admin user memberships |
| `web/modules/custom/lms_membership/templates/lms-my-membership.html.twig` | Member view template |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_membership_plan` (`MembershipPlan extends ContentEntityBase`)
- Key fields: `title`, `field_price`, `field_duration_days`, `field_features`, `field_course_access` (`all`/`specific`), `status`

**Entity:** `lms_user_membership` (`UserMembership extends ContentEntityBase implements EntityOwnerInterface`)
- Key fields: `uid`, `field_plan` (ref: `lms_membership_plan`), `field_status` (`active`/`expired`/`cancelled`), `field_starts_at`, `field_expires_at`

**Service: `lms_membership.membership_manager` → `MembershipManager`**
- `hasActiveMembership(AccountInterface $account): bool`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schemas install; access subscriber fires |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Membership create/check functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_membership_plan` entity type registered
- [x] `lms_user_membership` entity type registered
- [x] `MembershipManager::hasActiveMembership()` returns correct status
- [x] `MembershipAccessSubscriber` enforces membership-gated content
- [x] Admin can create/edit plans and view user memberships

---

## Known Debt / Issues

None identified.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-025 | Order items reference MembershipPlan |
| U-027 | PaymentManager processes membership orders |

---

*End of U-026-membership-entities.md*
