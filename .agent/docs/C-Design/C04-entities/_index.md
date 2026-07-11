# C04-entities - Entity Index

29 custom entity type (28 content + 1 config `lms_question_type`). Field được định nghĩa dưới dạng **base field trong code** (`baseFieldDefinitions`), không phải configurable field (field configurable duy nhất trên một custom entity là `lms_lesson.field_h5p_upload`). Danh sách field bên dưới được lấy từ DB schema thực tế (`information_schema`, 2026-06-21).

| Entity | Module(s) | File |
| ------ | --------- | ---- |
| lms_course | lms_course | [lms_course.md](lms_course.md) |
| lms_chapter | lms_chapter | [lms_chapter.md](lms_chapter.md) |
| lms_lesson | lms_lesson | [lms_lesson.md](lms_lesson.md) |
| lms_course_bundle | lms_bundle | [lms_course_bundle.md](lms_course_bundle.md) |
| lms_quiz | lms_quiz | [lms_quiz.md](lms_quiz.md) |
| lms_quiz_result | lms_quiz | [lms_quiz_result.md](lms_quiz_result.md) |
| lms_question_response | lms_quiz | [lms_question_response.md](lms_question_response.md) |
| lms_question | lms_question | [lms_question.md](lms_question.md) |
| lms_question_type | lms_question | [lms_question_type.md](lms_question_type.md) |
| lms_answer | lms_question | [lms_answer.md](lms_answer.md) |
| lms_matching_pair | lms_question | [lms_matching_pair.md](lms_matching_pair.md) |
| lms_user_course | lms_progress | [lms_user_course.md](lms_user_course.md) |
| lms_user_lesson | lms_progress | [lms_user_lesson.md](lms_user_lesson.md) |
| lms_certificate_template | lms_certificate | [lms_certificate_template.md](lms_certificate_template.md) |
| lms_user_certificate | lms_certificate | [lms_user_certificate.md](lms_user_certificate.md) |
| lms_badge | lms_badge | [lms_badge.md](lms_badge.md) |
| lms_user_badge | lms_badge | [lms_user_badge.md](lms_user_badge.md) |
| lms_order | lms_order | [lms_order.md](lms_order.md) |
| lms_order_item | lms_order | [lms_order_item.md](lms_order_item.md) |
| lms_membership_plan | lms_membership | [lms_membership_plan.md](lms_membership_plan.md) |
| lms_user_membership | lms_membership | [lms_user_membership.md](lms_user_membership.md) |
| lms_forum_topic | lms_forum | [lms_forum_topic.md](lms_forum_topic.md) |
| lms_forum_reply | lms_forum | [lms_forum_reply.md](lms_forum_reply.md) |
| lms_rating | lms_rating | [lms_rating.md](lms_rating.md) |
| lms_note | lms_note | [lms_note.md](lms_note.md) |
| lms_wishlist | lms_wishlist | [lms_wishlist.md](lms_wishlist.md) |
| lms_notification | lms_notification | [lms_notification.md](lms_notification.md) |
| lms_article | lms_blog | [lms_article.md](lms_article.md) |
| lms_faq | lms_faq | [lms_faq.md](lms_faq.md) |

> 29 entity (data-derived). Các entity có thể translate (bảng `_field_data`): lms_course, lms_chapter, lms_lesson, lms_quiz, lms_question, lms_article, lms_faq.
