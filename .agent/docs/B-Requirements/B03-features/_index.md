# B03-features - Feature Index

24 features (`F-NNN`), one file each, mirrored 1:1 by [B05-acceptance-criteria/](../B05-acceptance-criteria/_index.md), [E05-traceability](../../E-Quality/E05-traceability/_index.md), and [U03-feature-guides/](../../U-Userguide/U03-feature-guides/README.md). "Epic (D02)" links the owning epic in [D02-epics/](../../D-Development/D02-epics/) (hierarchical WBS `E##`). Status uses the rebuild refinement: **built** = as-built-backed · **partial** = works but incomplete · **stub** = defined, not functional.

| ID | Feature | Module | Epic (D02) | Workflows | Status |
| -- | ------- | ------ | ---------- | --------- | ------ |
| [F-001](F-001-course-catalog-and-discovery.md) | Course catalog & discovery | lms_course | E02 | WF-001 | built |
| [F-002](F-002-course-authoring-and-structure.md) | Course authoring & structure | lms_course, lms_chapter, lms_lesson | E02 | WF-006 | built |
| [F-003](F-003-lesson-delivery-and-media.md) | Lesson delivery & media | lms_lesson | E02 | WF-001 | built |
| [F-004](F-004-enrollment-and-content-gating.md) | Enrollment & content gating | lms_progress, lms_course | E03 | WF-001 | built |
| [F-005](F-005-progress-tracking-and-resume.md) | Progress tracking & resume | lms_progress | E03 | WF-001 | built |
| [F-006](F-006-quiz-and-assessment-engine.md) | Quiz & assessment engine | lms_quiz, lms_question | E04 | WF-002 | built |
| [F-007](F-007-certificate-issuance.md) | Certificate issuance | lms_certificate | E05 | WF-003 | built |
| [F-008](F-008-badges-and-gamification.md) | Badges & gamification | lms_badge | E05 | WF-002 | stub |
| [F-009](F-009-course-bundles.md) | Course bundles | lms_bundle | E06 | WF-004 | built |
| [F-010](F-010-orders-and-checkout.md) | Orders & checkout | lms_order | E06 | WF-004, WF-009 | built |
| [F-011](F-011-payments-and-gateways.md) | Payments & gateways | lms_payment, lms_stripe, lms_paypal | E06 | WF-004 | partial |
| [F-012](F-012-memberships-and-subscriptions.md) | Memberships & subscriptions | lms_membership | E07 | WF-005 | built |
| [F-013](F-013-community-forum.md) | Community forum | lms_forum | E08 | WF-008 | built |
| [F-014](F-014-ratings-and-reviews.md) | Ratings & reviews | lms_rating | E08 | WF-001 | built |
| [F-015](F-015-learner-notes-and-wishlist.md) | Learner notes & wishlist | lms_note, lms_wishlist | E08 | WF-001 | built |
| [F-016](F-016-blog-and-faq-content.md) | Blog & FAQ content | lms_blog, lms_faq | E08 | WF-006 | built |
| [F-017](F-017-notifications.md) | Notifications | lms_notification | E09 | WF-001, WF-003, WF-004 | partial |
| [F-018](F-018-ai-authoring-assistant.md) | AI authoring assistant | lms_ai | E10 | WF-010 | built |
| [F-019](F-019-mobile-app-api.md) | Mobile app API | lms_mobile_api | E11 | WF-001, WF-007 | built |
| [F-020](F-020-analytics-and-reporting.md) | Analytics & reporting | lms_analytics | E12 | — | partial |
| [F-021](F-021-users-roles-and-impersonation.md) | Users, roles & impersonation | open_switcher | E01 | WF-007 | built |
| [F-022](F-022-live-sessions-zoom.md) | Live sessions (Zoom) | lms_zoom | E08 | — | partial |
| [F-023](F-023-content-tools-and-seeding.md) | Content tools & seeding | lms_content_tools | E13 | — | built |
| [F-024](F-024-theming-and-homepage-skins.md) | Theming & homepage skins | lms_theme | E01 | — | built |

> Count (24) is data-derived from this project's module/behavior inventory (2026-06-21). Mirror invariant: B03 file count == B05 file count == E05 rows == U03 guides.
