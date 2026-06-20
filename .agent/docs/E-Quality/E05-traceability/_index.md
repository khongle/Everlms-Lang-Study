# E05-traceability - Traceability Matrix

**Last updated:** 2026-06-21

The requirement axis `BR → F → AC → Test`, plus Feature → Module(s) → Workflow projection. One row per feature (24); slug matches [B03](../../B-Requirements/B03-features/_index.md)/B05/[U03](../../U-Userguide/U03-feature-guides/README.md).

| F-NNN | Feature | Module(s) | Workflow(s) | AC (B05) | Test (E03) | Status |
| ----- | ------- | --------- | ----------- | -------- | ---------- | ------ |
| F-001 | Course catalog | lms_course | WF-001 | [AC](../../B-Requirements/B05-acceptance-criteria/F-001-course-catalog-and-discovery.md) | catalog-listing | built |
| F-002 | Course authoring | lms_course/chapter/lesson | WF-006 | AC | entity-create | built |
| F-003 | Lesson delivery | lms_lesson | WF-001 | AC | lesson-access | built |
| F-004 | Enrollment & gating | lms_progress | WF-001 | AC | gating-matrix | built |
| F-005 | Progress tracking | lms_progress | WF-001 | AC | progress-rollup | built |
| F-006 | Quiz engine | lms_quiz/question | WF-002 | AC | grading-per-strategy | built |
| F-007 | Certificate issuance | lms_certificate | WF-003 | AC | issuance-idempotency | built |
| F-008 | Badges | lms_badge | WF-002 | AC | award-trigger | **stub (FAIL)** |
| F-009 | Course bundles | lms_bundle | WF-004 | AC | bundle-fanout | built |
| F-010 | Orders & checkout | lms_order | WF-004/009 | AC | order-state | built |
| F-011 | Payments | lms_payment/stripe/paypal | WF-004 | AC | gateway-capture | **partial** |
| F-012 | Memberships | lms_membership | WF-005 | AC | entitlement | built |
| F-013 | Forum | lms_forum | WF-008 | AC | topic-reply-perms | built |
| F-014 | Ratings | lms_rating | WF-001 | AC | rating-aggregate | built |
| F-015 | Notes & wishlist | lms_note/wishlist | WF-001 | AC | note-privacy | built |
| F-016 | Blog & FAQ | lms_blog/faq | WF-006 | AC | publish-visibility | built |
| F-017 | Notifications | lms_notification | WF-001/003/004 | AC | in-app + email/push | **partial** |
| F-018 | AI authoring | lms_ai | WF-010 | AC | provider-routing | built |
| F-019 | Mobile API | lms_mobile_api | WF-001/007 | AC | api-auth/parity | built |
| F-020 | Analytics | lms_analytics | — | AC | report-scope | **partial** |
| F-021 | Users/roles/impersonation | open_switcher | WF-007 | AC | access-matrix | built |
| F-022 | Live sessions (Zoom) | lms_zoom | — | AC | schedule-meeting | **partial** |
| F-023 | Content tools | lms_content_tools | — | AC | import-count | built |
| F-024 | Theming & skins | lms_theme | — | AC | skin-switch | built |

> Mirror invariant: 24 rows == B03 == B05 == U03. Test column names the E03 definition (most are TARGET — suites not yet built, R-006). Feature→module→workflow projection recorded here per the architecture chain.
