# F-009 Acceptance Criteria - Course bundles

## Core criteria
1. **Given** an editor, **when** they create a bundle and add courses (`field_courses`), **then** the bundle and its price persist.
2. **Given** a bundle, **when** a buyer purchases it (WF-004), **then** the buyer is enrolled in **all** included courses.
3. **Given** a bundle detail page, **when** viewed, **then** it lists included courses and the bundle price.

## Evidence expected
- Module: [lms_bundle](../../C-Design/C05-modules/lms_bundle.md).
- Test: bundle-purchase fan-out test (enrollment created per included course) (E03).
