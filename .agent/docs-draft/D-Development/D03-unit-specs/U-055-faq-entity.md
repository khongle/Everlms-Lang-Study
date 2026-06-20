# D03 — Unit Spec: FAQ Entity (3-scope)

**Unit ID:** U-055
**Group:** G-ADDITIONAL
**Title:** FAQ entity (3-scope)
**Status:** DONE
**Priority:** P2

---

## Scope

Implements the `lms_faq` custom content entity for Frequently Asked Questions with a 3-value `field_scope` discriminator (`global`, `course`, `lesson`) and optional course reference. FAQs are managed via the workspace content CRUD in U-008.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_faq/lms_faq.info.yml` | Module declaration |
| `web/modules/custom/lms_faq/src/Entity/Faq.php` | FAQ entity class |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_faq` (`Faq extends ContentEntityBase`)
- Key fields: `title`, `field_answer` (text_long), `field_scope` (`global`/`course`/`lesson`), `field_course` (ref: `lms_course`), `field_category` (ref: `faq_category` taxonomy), `weight`, `status`

No dedicated controller or templates — managed via generic workspace content CRUD routes (U-008).

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schema installs |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | FAQ CRUD via workspace routes functional |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_faq` entity type registered
- [x] 3-scope field: global, course, lesson
- [x] FAQ can be scoped to a specific course
- [x] FAQ category references `faq_category` taxonomy
- [x] Admin can create/edit FAQs via workspace content routes

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
| U-004 | `faq_category` taxonomy vocabulary |
| U-005 | Course entity optionally referenced |
| U-008 | Workspace CRUD routes manage FAQs |

---

*End of U-055-faq-entity.md*
