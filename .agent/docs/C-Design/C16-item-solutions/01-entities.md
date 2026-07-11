# C16 Sheet - 01 entities

Data object → Drupal primitive. Nguồn: [C04-entities](../C04-entities/_index.md), [C03](../C03-entity-module-map/_index.md).

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Key fields/config | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | ----------------- | --------------- | ------------ |
| Catalog | lms_course | Custom content entity (translatable) | lms_course | base fields + entity-ref to chapters/instructor/tags | field_chapters, field_price, taxonomy | C04 lms_course | kernel CRUD/access |
| Curriculum | lms_chapter | Custom content entity | lms_chapter | ref from course; ref to lessons | field_lessons, weight | C04 lms_chapter | kernel CRUD |
| Content | lms_lesson | Content entity + Media/File + H5P field | lms_lesson | media/video/scorm/h5p + quiz ref | field_content, field_attachments, field_quiz | C04 lms_lesson | kernel + access (drip) |
| Bundle | lms_course_bundle | Content entity | lms_bundle | multi-ref courses | field_courses, field_price | C04 lms_course_bundle | purchase fan-out |
| Assessment | lms_quiz | Content entity | lms_quiz | ref questions + prereqs | field_questions, field_threshold | C04 lms_quiz | grading test |
| Assessment | lms_question (+type) | Content entity (bundleable) + Config entity (type) | lms_question | 8 bundles; per-type answer fields | field_choices, field_correct_answers | C04 lms_question | per-strategy grading |
| Assessment | lms_answer / lms_matching_pair | Content entity (inline) | lms_question | inline sub-entities | field_correct, left/right | C04 | grading |
| Result | lms_quiz_result / lms_question_response | Content entity | lms_quiz | attempt + responses | field_score, field_responses | C04 | event-on-pass |
| Progress | lms_user_course / lms_user_lesson | Content entity | lms_progress | user↔course/lesson join | field_progress, field_status | C04 | rollup test |
| Certification | lms_certificate_template | Content entity (editor layout) | lms_certificate | mPDF render | field_design, images | C04 | render test |
| Certification | lms_user_certificate | Content entity + event subscriber | lms_certificate | issued on QuizPassedEvent (idempotent) | field_pdf_file, field_certificate_number | C04 | idempotency |
| Gamification | lms_badge / lms_user_badge | Content entity (+ ⚠️ missing award subscriber) | lms_badge | define + (TODO) grant | field_criteria | C04 / R-003 | award trigger (red) |
| Commerce | lms_order / lms_order_item | Content entity (custom commerce) | lms_order | header + lines + state | field_status, field_items | C04 | state machine |
| Commerce | lms_membership_plan / lms_user_membership | Content entity | lms_membership | plan + subscription | field_duration_days, field_status | C04 | entitlement |
| Community | lms_forum_topic / lms_forum_reply | Content entity | lms_forum | topic + threaded replies | field_topic, field_best_answer | C04 | CRUD + perms |
| Community | lms_rating | Content entity | lms_rating | rating + review (moderated) | field_score, field_approved | C04 | aggregate |
| Learner | lms_note / lms_wishlist | Content entity (private) | lms_note/lms_wishlist | user-owned | field_lesson, field_course | C04 | privacy |
| Comms | lms_notification | Content entity + event/cron | lms_notification | multi-channel | field_channel, field_reference_* | C04 / R-005 | in-app ok; email/push gap |
| Marketing | lms_article / lms_faq | Content entity (translatable) | lms_blog/lms_faq | SEO/category | seo_*, field_category | C04 | publish/visibility |
