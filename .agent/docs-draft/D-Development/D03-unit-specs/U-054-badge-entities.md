# D03 — Unit Spec: Badge + UserBadge Entities

**Unit ID:** U-054
**Group:** G-ADDITIONAL
**Title:** Badge + UserBadge entities
**Status:** DONE (PARTIAL)
**Priority:** P2

---

## Scope

Implements the `lms_badge` and `lms_user_badge` custom content entities for gamification badge awards. Badge types are seeded via the `badge_type` taxonomy in U-004. The module contains entity classes only — no controller, routing, or award logic is implemented yet.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_badge/lms_badge.info.yml` | Module declaration |
| `web/modules/custom/lms_badge/src/Entity/Badge.php` | Badge entity class |
| `web/modules/custom/lms_badge/src/Entity/UserBadge.php` | UserBadge entity class |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_badge` (`Badge extends ContentEntityBase`)
- Key fields: `title`, `field_description`, `field_icon`, `field_badge_type` (ref: `badge_type` taxonomy), `field_criteria`, `status`

**Entity:** `lms_user_badge` (`UserBadge extends ContentEntityBase implements EntityOwnerInterface`)
- Key fields: `uid`, `field_badge` (ref: `lms_badge`), `field_awarded_at`, `field_context`

No routing, controller, award service, or templates implemented.

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schemas install |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PARTIAL | Entities creatable via code; no UI |
| G4 Adversarial | PARTIAL | No award trigger logic |

---

## Acceptance Criteria

- [x] `lms_badge` entity type registered
- [x] `lms_user_badge` entity type registered
- [x] Badge types linked to `badge_type` taxonomy
- [ ] Badge award service/triggers (PARTIAL — not implemented)
- [ ] Badge display on dashboard/profile (PARTIAL — not implemented)

---

## Known Debt / Issues

- No award logic, routing, or display templates — entity scaffold only.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-004 | `badge_type` taxonomy provides type options |

---

*End of U-054-badge-entities.md*
