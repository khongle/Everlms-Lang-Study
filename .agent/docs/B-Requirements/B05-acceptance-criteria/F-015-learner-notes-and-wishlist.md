# F-015 Acceptance Criteria - Learner notes & wishlist

## Core criteria
1. **Given** a learner on a lesson/course, **when** they save a note (`lms_note`), **then** it persists and is visible only to them.
2. **Given** a catalog course, **when** the learner adds it to wishlist (`lms_wishlist`), **then** it appears in their wishlist.
3. **Given** a wishlisted course, **when** the learner removes it, **then** it disappears from the wishlist.
4. **Given** a wishlisted paid course, **when** the learner checks out, **then** the wishlist item converts to an order (F-010).

## Evidence expected
- Modules: [lms_note](../../C-Design/C05-modules/lms_note.md), [lms_wishlist](../../C-Design/C05-modules/lms_wishlist.md).
- Test: note privacy test (other users cannot read); wishlist add/remove test (E03).
