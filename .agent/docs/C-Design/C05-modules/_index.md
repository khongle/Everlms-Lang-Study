# C05-modules - Module Index

28 module custom (27 `lms_*` + `open_switcher`), cộng thêm **theme** `lms_theme` được tài liệu hóa tại đây cho đầy đủ (theme này không được tính vào 28 module; contract đầy đủ của theme ở [C09](../C09-themes/_index.md)). Phase/Epic theo [C01](../C01-architecture-decision/_index.md). Status: `done | in-progress | blocked` (as-built-backed, 2026-06-21).

| Module | Name | Phase | Epic | Status | Entities | Features | Workflows | Pages |
| ------ | ---- | ----- | ---- | ------ | -------- | -------- | --------- | ----- |
| [lms_base](lms_base.md) | Base / shared | 1 | E01 | done | — | F-023 | — | 0 |
| [open_switcher](open_switcher.md) | Impersonation | 1 | E01 | done | — | F-021 | WF-007 | 1 |
| [lms_theme](lms_theme.md) | Theme | 1 | E01 | done | — | F-024 | — | (theme) |
| [lms_course](lms_course.md) | Courses | 2 | E02 | done | lms_course | F-001, F-002 | WF-001, WF-006 | 4 |
| [lms_chapter](lms_chapter.md) | Chapters | 2 | E02 | done | lms_chapter | F-002 | WF-006 | 1 |
| [lms_lesson](lms_lesson.md) | Lessons | 2 | E02 | done | lms_lesson | F-003 | WF-001 | 2 |
| [lms_progress](lms_progress.md) | Progress | 3 | E03 | done | lms_user_course, lms_user_lesson | F-004, F-005 | WF-001 | 3 |
| [lms_question](lms_question.md) | Questions | 4 | E04 | done | lms_question, lms_question_type, lms_answer, lms_matching_pair | F-006 | WF-002 | 2 |
| [lms_quiz](lms_quiz.md) | Quizzes | 4 | E04 | done | lms_quiz, lms_quiz_result, lms_question_response | F-006 | WF-002 | 3 |
| [lms_certificate](lms_certificate.md) | Certificates | 5 | E05 | done | lms_certificate_template, lms_user_certificate | F-007 | WF-003 | 2 |
| [lms_badge](lms_badge.md) | Badges | 5 | E05 | blocked | lms_badge, lms_user_badge | F-008 | WF-002 | 1 |
| [lms_payment](lms_payment.md) | Payments | 6 | E06 | in-progress | — | F-011 | WF-004 | 1 |
| [lms_stripe](lms_stripe.md) | Stripe | 6 | E06 | done | — | F-011 | WF-004 | 0 |
| [lms_paypal](lms_paypal.md) | PayPal (dup) | 6 | E06 | blocked | — | F-011 | WF-004 | 0 |
| [lms_order](lms_order.md) | Orders | 6 | E06 | done | lms_order, lms_order_item | F-010 | WF-004, WF-009 | 2 |
| [lms_bundle](lms_bundle.md) | Bundles | 6 | E06 | done | lms_course_bundle | F-009 | WF-004 | 2 |
| [lms_membership](lms_membership.md) | Memberships | 7 | E07 | done | lms_membership_plan, lms_user_membership | F-012 | WF-005 | 2 |
| [lms_forum](lms_forum.md) | Forum | 8 | E08 | done | lms_forum_topic, lms_forum_reply | F-013 | WF-008 | 3 |
| [lms_rating](lms_rating.md) | Ratings | 8 | E08 | done | lms_rating | F-014 | WF-001 | 1 |
| [lms_note](lms_note.md) | Notes | 8 | E08 | done | lms_note | F-015 | WF-001 | 1 |
| [lms_wishlist](lms_wishlist.md) | Wishlist | 8 | E08 | done | lms_wishlist | F-015 | WF-001 | 1 |
| [lms_blog](lms_blog.md) | Blog | 8 | E08 | done | lms_article | F-016 | WF-006 | 2 |
| [lms_faq](lms_faq.md) | FAQ | 8 | E08 | done | lms_faq | F-016 | — | 1 |
| [lms_zoom](lms_zoom.md) | Zoom | 8 | E08 | in-progress | — | F-022 | — | 1 |
| [lms_notification](lms_notification.md) | Notifications | 9 | E09 | in-progress | lms_notification | F-017 | WF-008 | 1 |
| [lms_ai](lms_ai.md) | AI authoring | 10 | E10 | done | — | F-018 | WF-010 | 1 |
| [lms_mobile_api](lms_mobile_api.md) | Mobile API | 11 | E11 | done | — | F-019 | WF-007 | 0 |
| [lms_analytics](lms_analytics.md) | Analytics | 12 | E12 | in-progress | — | F-020 | — | 3 |
| [lms_content_tools](lms_content_tools.md) | Content tools | 13 | E13 | done | — | F-023 | — | 1 |

> 28 module (data-derived). Mỗi module đều có một file contract ở đây và một dòng trong bảng tổng hợp [D07 code-map](../../D-Development/D07-code-map/README.md); mỗi module có file code-map đào sâu (deep-dive) riêng (đủ 28/28).
