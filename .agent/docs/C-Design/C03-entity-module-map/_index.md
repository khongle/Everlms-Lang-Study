# C03-entity-module-map - Entity to Module Map

| Entity | Owning module(s) | Design files |
| ------ | ---------------- | ------------ |
| lms_answer | lms_question | [C04](../C04-entities/lms_answer.md) · [C05](../C05-modules/lms_question.md) |
| lms_article | lms_blog | [C04](../C04-entities/lms_article.md) · [C05](../C05-modules/lms_blog.md) |
| lms_badge | lms_badge | [C04](../C04-entities/lms_badge.md) · [C05](../C05-modules/lms_badge.md) |
| lms_certificate_template | lms_certificate | [C04](../C04-entities/lms_certificate_template.md) · [C05](../C05-modules/lms_certificate.md) |
| lms_chapter | lms_chapter | [C04](../C04-entities/lms_chapter.md) · [C05](../C05-modules/lms_chapter.md) |
| lms_course | lms_course | [C04](../C04-entities/lms_course.md) · [C05](../C05-modules/lms_course.md) |
| lms_course_bundle | lms_bundle | [C04](../C04-entities/lms_course_bundle.md) · [C05](../C05-modules/lms_bundle.md) |
| lms_faq | lms_faq | [C04](../C04-entities/lms_faq.md) · [C05](../C05-modules/lms_faq.md) |
| lms_forum_reply | lms_forum | [C04](../C04-entities/lms_forum_reply.md) · [C05](../C05-modules/lms_forum.md) |
| lms_forum_topic | lms_forum | [C04](../C04-entities/lms_forum_topic.md) · [C05](../C05-modules/lms_forum.md) |
| lms_lesson | lms_lesson | [C04](../C04-entities/lms_lesson.md) · [C05](../C05-modules/lms_lesson.md) |
| lms_matching_pair | lms_question | [C04](../C04-entities/lms_matching_pair.md) · [C05](../C05-modules/lms_question.md) |
| lms_membership_plan | lms_membership | [C04](../C04-entities/lms_membership_plan.md) · [C05](../C05-modules/lms_membership.md) |
| lms_note | lms_note | [C04](../C04-entities/lms_note.md) · [C05](../C05-modules/lms_note.md) |
| lms_notification | lms_notification | [C04](../C04-entities/lms_notification.md) · [C05](../C05-modules/lms_notification.md) |
| lms_order | lms_order | [C04](../C04-entities/lms_order.md) · [C05](../C05-modules/lms_order.md) |
| lms_order_item | lms_order | [C04](../C04-entities/lms_order_item.md) · [C05](../C05-modules/lms_order.md) |
| lms_question | lms_question | [C04](../C04-entities/lms_question.md) · [C05](../C05-modules/lms_question.md) |
| lms_question_response | lms_quiz | [C04](../C04-entities/lms_question_response.md) · [C05](../C05-modules/lms_quiz.md) |
| lms_question_type | lms_question | [C04](../C04-entities/lms_question_type.md) · [C05](../C05-modules/lms_question.md) |
| lms_quiz | lms_quiz | [C04](../C04-entities/lms_quiz.md) · [C05](../C05-modules/lms_quiz.md) |
| lms_quiz_result | lms_quiz | [C04](../C04-entities/lms_quiz_result.md) · [C05](../C05-modules/lms_quiz.md) |
| lms_rating | lms_rating | [C04](../C04-entities/lms_rating.md) · [C05](../C05-modules/lms_rating.md) |
| lms_user_badge | lms_badge | [C04](../C04-entities/lms_user_badge.md) · [C05](../C05-modules/lms_badge.md) |
| lms_user_certificate | lms_certificate | [C04](../C04-entities/lms_user_certificate.md) · [C05](../C05-modules/lms_certificate.md) |
| lms_user_course | lms_progress | [C04](../C04-entities/lms_user_course.md) · [C05](../C05-modules/lms_progress.md) |
| lms_user_lesson | lms_progress | [C04](../C04-entities/lms_user_lesson.md) · [C05](../C05-modules/lms_progress.md) |
| lms_user_membership | lms_membership | [C04](../C04-entities/lms_user_membership.md) · [C05](../C05-modules/lms_membership.md) |
| lms_wishlist | lms_wishlist | [C04](../C04-entities/lms_wishlist.md) · [C05](../C05-modules/lms_wishlist.md) |

> 29 entity type (28 content + 1 config `lms_question_type`, entity này cũng định nghĩa 8 question bundle). Bidirectional closure: mỗi dòng đều có một file C04 và mỗi module sở hữu đều có một file C05.
