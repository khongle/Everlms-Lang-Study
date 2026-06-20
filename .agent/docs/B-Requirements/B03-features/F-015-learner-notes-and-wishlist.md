# F-015 - Learner notes & wishlist

**Status:** built
**Primary module:** [lms_note](../../C-Design/C05-modules/lms_note.md), [lms_wishlist](../../C-Design/C05-modules/lms_wishlist.md)
**Epic:** [E08 Community](../../D-Development/D02-epics/E08-community.md)
**Related workflows:** [WF-001 Enrollment & learning](../B04-business-workflows/WF-001-enrollment-and-learning.md)
**Acceptance:** [B05 F-015](../B05-acceptance-criteria/F-015-learner-notes-and-wishlist.md)
**Source:** `web/modules/custom/lms_note/`, `lms_wishlist/`

## Requirement
Learners take personal **notes** (`lms_note`) against a lesson/course (private to the user) and add courses to a **wishlist** (`lms_wishlist`) for later purchase/enrollment. Both are surfaced on the dashboard and mobile API.

## Business value
Notes improve study effectiveness and stickiness; wishlist captures purchase intent for re-marketing.

## Scope notes
- `lms_note`: user-private, attached to lesson/course context.
- `lms_wishlist`: user ↔ course saved list; convertible to an order (F-010).
- Two small but distinct entities grouped as "learner personal tools."

## Cross references
- Design: [C04 lms_note](../../C-Design/C04-entities/lms_note.md), [C04 lms_wishlist](../../C-Design/C04-entities/lms_wishlist.md)
