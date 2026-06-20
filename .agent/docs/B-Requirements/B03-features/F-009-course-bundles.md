# F-009 - Course bundles

**Status:** built
**Primary module:** [lms_bundle](../../C-Design/C05-modules/lms_bundle.md)
**Epic:** [E06 Commerce](../../D-Development/D02-epics/E06-commerce.md)
**Related workflows:** [WF-004 Order checkout & payment](../B04-business-workflows/WF-004-order-checkout-and-payment.md)
**Acceptance:** [B05 F-009](../B05-acceptance-criteria/F-009-course-bundles.md)
**Source:** `web/modules/custom/lms_bundle/`

## Requirement
Editors can group multiple courses into a **bundle** (`lms_course_bundle.field_courses`) sold at a single bundle price. Purchasing a bundle enrolls the buyer in all included courses.

## Business value
Bundles increase average order value and let organisations package role-based learning paths (e.g. a full occupational-safety program).

## Scope notes
- `lms_course_bundle` references courses via `field_courses`.
- Bundle purchase fans out to per-course enrollment (F-004) at order completion.
- Pricing/discount relative to buying courses individually is a bundle-level field.

## Cross references
- Design: [C04 lms_course_bundle](../../C-Design/C04-entities/lms_course_bundle.md), [C05 lms_bundle](../../C-Design/C05-modules/lms_bundle.md)
- Related: F-010 Orders, F-004 Enrollment
