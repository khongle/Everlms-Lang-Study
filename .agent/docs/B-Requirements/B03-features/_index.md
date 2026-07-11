# B03-features - Feature Index

24 feature (`F-NNN`), mỗi feature một file, được mirror 1:1 bởi [B05-acceptance-criteria/](../B05-acceptance-criteria/_index.md), [E05-traceability](../../E-Quality/E05-traceability/_index.md), và [U03-feature-guides/](../../U-Userguide/U03-feature-guides/README.md). "Epic (D02)" link tới epic sở hữu trong [D02-epics/](../../D-Development/D02-epics/) (hierarchical WBS `E##`). Status dùng cách phân loại refinement của đợt rebuild: **built** = đã có bằng chứng as-built · **partial** = hoạt động nhưng chưa hoàn chỉnh · **stub** = đã định nghĩa, chưa functional.

| ID | Feature | MoSCoW | Module | Epic (D02) | Workflows | Status |
| -- | ------- | --- | ------ | ---------- | --------- | ------ |
| F-001 | [Course catalog & discovery](F-001-course-catalog-and-discovery.md) | Must | lms_course | E02 | WF-001 | built |
| F-002 | [Course authoring & structure](F-002-course-authoring-and-structure.md) | Must | lms_course, lms_chapter, lms_lesson | E02 | WF-006 | built |
| F-003 | [Lesson delivery & media](F-003-lesson-delivery-and-media.md) | Must | lms_lesson | E02 | WF-001 | built |
| F-004 | [Enrollment & content gating](F-004-enrollment-and-content-gating.md) | Must | lms_progress, lms_course | E03 | WF-001 | built |
| F-005 | [Progress tracking & resume](F-005-progress-tracking-and-resume.md) | Must | lms_progress | E03 | WF-001 | built |
| F-006 | [Quiz & assessment engine](F-006-quiz-and-assessment-engine.md) | Must | lms_quiz, lms_question | E04 | WF-002 | built |
| F-007 | [Certificate issuance](F-007-certificate-issuance.md) | Must | lms_certificate | E05 | WF-003 | built |
| F-008 | [Badges & gamification](F-008-badges-and-gamification.md) | Must | lms_badge | E05 | WF-002 | stub |
| F-009 | [Course bundles](F-009-course-bundles.md) | Must | lms_bundle | E06 | WF-004 | built |
| F-010 | [Orders & checkout](F-010-orders-and-checkout.md) | Must | lms_order | E06 | WF-004, WF-009 | built |
| F-011 | [Payments & gateways](F-011-payments-and-gateways.md) | Should | lms_payment, lms_stripe, lms_paypal | E06 | WF-004 | partial |
| F-012 | [Memberships & subscriptions](F-012-memberships-and-subscriptions.md) | Should | lms_membership | E07 | WF-005 | built |
| F-013 | [Community forum](F-013-community-forum.md) | Must | lms_forum | E08 | WF-008 | built |
| F-014 | [Ratings & reviews](F-014-ratings-and-reviews.md) | Should | lms_rating | E08 | WF-001 | built |
| F-015 | [Learner notes & wishlist](F-015-learner-notes-and-wishlist.md) | Should | lms_note, lms_wishlist | E08 | WF-001 | built |
| F-016 | [Blog & FAQ content](F-016-blog-and-faq-content.md) | Must | lms_blog, lms_faq | E08 | WF-006 | built |
| F-017 | [Notifications](F-017-notifications.md) | Should | lms_notification | E09 | WF-001, WF-003, WF-004 | partial |
| F-018 | [AI authoring assistant](F-018-ai-authoring-assistant.md) | Could | lms_ai | E10 | WF-010 | built |
| F-019 | [Mobile app API](F-019-mobile-app-api.md) | Must | lms_mobile_api | E11 | WF-001, WF-007 | built |
| F-020 | [Analytics & reporting](F-020-analytics-and-reporting.md) | Should | lms_analytics | E12 | — | partial |
| F-021 | [Users, roles & impersonation](F-021-users-roles-and-impersonation.md) | Should | open_switcher | E01 | WF-007 | built |
| F-022 | [Live sessions (Zoom)](F-022-live-sessions-zoom.md) | Could | lms_zoom | E08 | — | partial |
| F-023 | [Content tools & seeding](F-023-content-tools-and-seeding.md) | Could | lms_content_tools | E13 | — | built |
| F-024 | [Theming & homepage skins](F-024-theming-and-homepage-skins.md) | Could | lms_theme | E01 | — | built |

> Con số (24) được data-derived từ module/behavior inventory của project này (2026-06-21). Mirror invariant: số file B03 == số file B05 == số row E05 == số guide U03.
