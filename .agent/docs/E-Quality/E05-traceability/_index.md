# E05-traceability - Traceability Matrix

**Last updated:** 2026-07-11

The requirement axis `BR → F → AC → Test`, plus Feature → Module → Workflow projection. `Feature` cell carries the `F-NNN` ID plus display name; `Module` carries the clean [E06](../E06-implementation-status/_index.md) module ID(s) (bidirectional join — every module named here has an E06 row, and vice versa). One row per feature (24) + 1 cross-cutting infra row; slug matches [B03](../../B-Requirements/B03-features/_index.md)/B05/[U03](../../U-Userguide/U03-feature-guides/README.md).

| Feature | Module | Requirement (B03) | AC (B05) | Design (C05) | Workflow(s) | Test (E03) | Status |
| ------- | ------ | ------------------ | -------- | ------------- | ----------- | ---------- | ------ |
| F-001 Course catalog | lms_course | [B03](../../B-Requirements/B03-features/F-001-course-catalog-and-discovery.md) | [B05](../../B-Requirements/B05-acceptance-criteria/F-001-course-catalog-and-discovery.md) | [C05](../../C-Design/C05-modules/lms_course.md) | WF-001 | catalog-listing | built |
| F-002 Course authoring | lms_course, lms_chapter, lms_lesson | B03 | B05 | C05 | WF-006 | entity-create | built |
| F-003 Lesson delivery | lms_lesson | B03 | B05 | C05 | WF-001 | lesson-access | built |
| F-004 Enrollment & gating | lms_progress | B03 | B05 | C05 | WF-001 | gating-matrix | built |
| F-005 Progress tracking | lms_progress | B03 | B05 | C05 | WF-001 | progress-rollup | built |
| F-006 Quiz engine | lms_quiz, lms_question | B03 | B05 | C05 | WF-002 | grading-per-strategy | built |
| F-007 Certificate issuance | lms_certificate | B03 | B05 | C05 | WF-003 | issuance-idempotency | built |
| F-008 Badges | lms_badge | B03 | B05 | C05 | WF-002 | award-trigger | **stub (FAIL)** |
| F-009 Course bundles | lms_bundle | B03 | B05 | C05 | WF-004 | bundle-fanout | built |
| F-010 Orders & checkout | lms_order | B03 | B05 | C05 | WF-004/009 | order-state | built |
| F-011 Payments | lms_payment, lms_stripe, lms_paypal | B03 | B05 | C05 | WF-004 | gateway-capture | **partial** |
| F-012 Memberships | lms_membership | B03 | B05 | C05 | WF-005 | entitlement | built |
| F-013 Forum | lms_forum | B03 | B05 | C05 | WF-008 | topic-reply-perms | built |
| F-014 Ratings | lms_rating | B03 | B05 | C05 | WF-001 | rating-aggregate | built |
| F-015 Notes & wishlist | lms_note, lms_wishlist | B03 | B05 | C05 | WF-001 | note-privacy | built |
| F-016 Blog & FAQ | lms_blog, lms_faq | B03 | B05 | C05 | WF-006 | publish-visibility | built |
| F-017 Notifications | lms_notification | B03 | B05 | C05 | WF-001/003/004 | in-app + email/push | **partial** |
| F-018 AI authoring | lms_ai | B03 | B05 | C05 | WF-010 | provider-routing | built |
| F-019 Mobile API | lms_mobile_api | B03 | B05 | C05 | WF-001/007 | api-auth/parity | built |
| F-020 Analytics | lms_analytics | B03 | B05 | C05 | — | report-scope | **partial** |
| F-021 Users/roles/impersonation | open_switcher | B03 | B05 | C05 | WF-007 | access-matrix | built |
| F-022 Live sessions (Zoom) | lms_zoom | B03 | B05 | C05 | — | schedule-meeting | **partial** |
| F-023 Content tools | lms_content_tools | B03 | B05 | C05 | — | import-count | built |
| F-024 Theming & skins | lms_theme | B03 | B05 | C05 | — | skin-switch | built |
| — (cross-cutting infra, no dedicated F-NNN) | lms_base | — | — | [C05](../../C-Design/C05-modules/lms_base.md) | — | — | built |

> Mirror invariant: 24 F-NNN rows == B03 == B05 == U03 (the trailing `lms_base` row is an infra/no-requirement row per the E-quality contract's richer-form allowance, not a 25th feature). Test column names the E03 definition (most are TARGET — suites not yet built, R-006). Feature→module→workflow projection recorded here per the architecture chain. Module column is the E06 join key — every token here has a matching [E06](../E06-implementation-status/_index.md) row, and every E06 row is named in this column at least once.
