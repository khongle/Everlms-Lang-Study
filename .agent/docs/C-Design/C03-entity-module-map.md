# C03 — Entity-Module Map

> Roll-up view linking all 29 custom entities (28 ContentEntity + 1 ConfigEntity) to their owning modules,
> DB tables, API touchpoints, and key relationships.
> Documentation mode: **as-built** (reflects code and DB dump as of 2026-06-20).

---

## Entity Relationship Overview

```
COURSE 1──M CHAPTER 1──M LESSON ──1 QUIZ 1──M QUESTION (8 bundles)
                                                │
                                          QUESTION uses:
                                            ├─ ANSWER (single_choice, multiple_choice)
                                            └─ MATCHING_PAIR (matching)

QUIZ 1──M QUIZ_RESULT 1──M QUESTION_RESPONSE
QUIZ_RESULT M──1 USER
QUESTION_RESPONSE M──1 QUESTION, M──1 ANSWER (selected choices)

QUIZ_RESULT ──1 USER_CERTIFICATE (certificate issuance chain):
  QuizPassedEvent → CertificateEventSubscriber
      traverses: QUIZ_RESULT → QUIZ → LESSON → CHAPTER → COURSE
      reads:     COURSE.field_certificate_template → CERTIFICATE_TEMPLATE
      creates:   USER_CERTIFICATE (mPDF PDF generated)

USER_COURSE M──1 USER, M──1 COURSE    (enrollment + % progress)
USER_LESSON M──1 USER, M──1 LESSON   (per-lesson completion, video position)

ORDER 1──M ORDER_ITEM
ORDER M──1 USER
ORDER_ITEM → COURSE | MEMBERSHIP_PLAN (mutually exclusive per item)
USER_MEMBERSHIP M──1 USER, M──1 MEMBERSHIP_PLAN

FORUM_TOPIC M──1 COURSE, M──1 USER
FORUM_REPLY M──1 FORUM_TOPIC, M──1 USER
FORUM_REPLY self-ref: field_parent (threaded)

RATING M──1 COURSE, M──1 USER
BADGE 1──M USER_BADGE M──1 USER
COURSE_BUNDLE 1──M COURSE (via field_courses junction)
```

---

## Entity × Module × API Matrix

All 29 custom entities. `Kind`: **editorial** = staff/instructor-created content; **transactional** = user-generated events/records; **config** = Drupal config entity.

| # | Entity (C04) | Kind | Owning Module | Base Table | Data Table | Key Relationships | API Surface | Editor UI | Role / Permission | Notes |
|---|--------------|------|---------------|------------|------------|-------------------|-------------|-----------|-------------------|-------|
| 1 | `lms_course` | editorial | [lms_course](C05-modules/lms_course.md) | `lms_course` | `lms_course_field_data` | → lms_chapter (field_chapters); → taxonomy_term (category/level/language/tags); → user (instructor); → lms_certificate_template | `/courses`, `/courses/{id}`, `/api/courses/search`, `/api/v1/courses/*` | `/workspace/courses`, `/workspace/courses/{id}/edit` | administer lms_course | Translatable; 3 form modes (default, admin_edit, manager_edit); field_rating is denormalized avg from lms_rating |
| 2 | `lms_chapter` | editorial | [lms_chapter](C05-modules/lms_chapter.md) | `lms_chapter` | `lms_chapter_field_data` | → lms_lesson (field_lessons); ← lms_course.field_chapters | none | `/workspace/chapters/{id}/admin-edit` | entity_access:lms_chapter.update | Translatable; weight-sorted within course |
| 3 | `lms_lesson` | editorial | [lms_lesson](C05-modules/lms_lesson.md) | `lms_lesson` | `lms_lesson_field_data` | → lms_quiz (field_quiz); ← lms_chapter.field_lessons; ← lms_user_lesson; ← lms_note | `/learn/{course}/{lesson}`, `/api/v1/courses/{id}/lessons/{id}` | `/workspace/lessons/{id}/admin-edit` | entity_access:lms_lesson.update | Translatable; 9 lesson types (video/text/quiz/h5p/scorm/pdf/pptx/zoom/mixed); field_free_preview bypasses enrollment check |
| 4 | `lms_quiz` | editorial | [lms_quiz](C05-modules/lms_quiz.md) | `lms_quiz` | `lms_quiz_field_data` | → lms_question (field_questions); self-ref field_prerequisite_quizzes; ← lms_lesson.field_quiz; ← lms_quiz_result | `/quiz/{id}/take`, `/quiz/{id}/submit`, `/api/v1/quiz/{id}`, `/api/v1/quiz/{id}/submit` | `/workspace/quizzes`, `/workspace/quizzes/{id}/edit` | entity_create_access:lms_quiz | Translatable; grading via static GradingStrategyFactory (not Plugin); essay → pending_review; QuizPassedEvent dispatched on pass |
| 5 | `lms_question` | editorial | [lms_question](C05-modules/lms_question.md) | `lms_question` | `lms_question_field_data` | bundled by lms_question_type; → lms_answer (field_choices); → lms_matching_pair (field_matching_pairs); ← lms_quiz.field_questions | (embedded in quiz API responses) | (within quiz editor) | entity_create_access:lms_question | 8 bundles: essay, fill_blank, matching, multiple_choice, ordering, short_answer, single_choice, true_false; all bundles share same base fields; bundle-conditional display via QuestionForm |
| 6 | `lms_question_type` | config | [lms_question](C05-modules/lms_question.md) | `key_value` (config) | — | bundle provider for lms_question | — | Drupal config admin | administer site configuration | Sole custom ConfigEntity; config prefix `lms_question.question_type`; 8 instances in config/sync |
| 7 | `lms_answer` | editorial | [lms_question](C05-modules/lms_question.md) | `lms_answer` | (flat — not translatable) | ← lms_question.field_choices; ← lms_question_response.field_answers | (embedded in quiz API responses) | (within question editor) | entity_access:lms_answer.* | Not translatable; ~817 rows; used by single_choice and multiple_choice bundles |
| 8 | `lms_matching_pair` | editorial | [lms_question](C05-modules/lms_question.md) | `lms_matching_pair` | (flat) | ← lms_question.field_matching_pairs | (embedded in quiz responses) | (within question editor) | entity_access:lms_matching_pair.* | ~39 rows; left/right pair columns; weight-ordered |
| 9 | `lms_quiz_result` | transactional | [lms_quiz](C05-modules/lms_quiz.md) | `lms_quiz_result` | (flat) | → lms_quiz; → user; → taxonomy_term (quiz_results); → lms_question_response (field_responses); ← lms_user_certificate.field_quiz_result | `/quiz-result/{id}`, `/api/v1/quiz/results/{id}`, `/api/v1/quiz/my-results`, `/my-quiz-results` | (read-only; admin via analytics) | uid (owner) | ~95 rows; field_result is entity_reference → taxonomy_term (pass/fail); auto-creates lms_user_certificate via event |
| 10 | `lms_question_response` | transactional | [lms_quiz](C05-modules/lms_quiz.md) | `lms_question_response` | (flat) | → lms_question; → lms_quiz; → lms_answer (field_answers); ← lms_quiz_result.field_responses | (embedded in result API responses) | `/admin/lms/reports/quizzes/{id}` (analytics) | uid (via quiz_result) | ~183 rows; field_pending_review=1 for essay awaiting manual grade; field_teacher_feedback for instructor |
| 11 | `lms_user_course` | transactional | [lms_progress](C05-modules/lms_progress.md) | `lms_user_course` | (flat) | → user; → lms_course | `/my-courses`, `/dashboard`, `/api/v1/my-courses`, `/api/v1/dashboard`, `/api/lms/progress/mark-complete`, `/api/v1/progress/mark-complete` | `/workspace/users` (via user detail) | uid (owner) | ~175 rows; enrollment is independent of order (manual enrollment possible); no DB unique constraint on (uid, course) — app must prevent duplicates; field_status: enrolled/in_progress/completed |
| 12 | `lms_user_lesson` | transactional | [lms_progress](C05-modules/lms_progress.md) | `lms_user_lesson` | (flat) | → user; → lms_lesson | `/api/lms/progress/mark-complete`, `/api/v1/progress/mark-complete` | (read-only in lesson view) | uid (owner) | ~38 rows; field_video_position stores last watched second; field_status: not_started/in_progress/completed |
| 13 | `lms_order` | transactional | [lms_order](C05-modules/lms_order.md) | `lms_order` | (flat) | → user; → lms_order_item (field_items) | `/checkout/{course_id}`, `/checkout/membership/{plan_id}`, `/checkout/bank-transfer/{order}` | `/workspace/orders`, `/workspace/orders/{id}/edit` | administer lms_course | ~77 rows; field_status: pending/processing/completed/cancelled/refunded; field_payment_method stores gateway key; no webhook endpoints exist — webhook signature verification is TODO |
| 14 | `lms_order_item` | transactional | [lms_order](C05-modules/lms_order.md) | `lms_order_item` | (flat) | → lms_course (nullable); → lms_membership_plan (nullable); ← lms_order.field_items | (embedded in checkout flow) | (within order edit form) | (via order ownership) | ~91 rows; field_type: course/bundle/membership; price captured at purchase time |
| 15 | `lms_membership_plan` | editorial | [lms_membership](C05-modules/lms_membership.md) | `lms_membership_plan` | (flat) | ← lms_user_membership; ← lms_order_item | `/pricing`, `/my-membership` | `/workspace/pricing`, `/workspace/pricing/{id}/edit`, `/workspace/pricing/{id}/toggle` | administer lms_course | ~10 rows; field_billing_cycle: monthly/quarterly/yearly/lifetime; field_course_access: all/selected |
| 16 | `lms_user_membership` | transactional | [lms_membership](C05-modules/lms_membership.md) | `lms_user_membership` | (flat) | → user; → lms_membership_plan | `/my-membership`, `/my-membership/{id}/cancel` | `/workspace/user-memberships`, `/workspace/user-memberships/{id}/edit` | uid (owner) | ~48 rows; field_status: active/trial/expired/cancelled; field_auto_renew default TRUE |
| 17 | `lms_certificate_template` | editorial | [lms_certificate](C05-modules/lms_certificate.md) | `lms_certificate_template` | (flat) | ← lms_course.field_certificate_template; ← lms_user_certificate.field_template | `/certificate/verify` (reads cert, not template) | `/workspace/certificate-templates`, `/workspace/certificate-templates/{id}/edit`, `/workspace/certificate-templates/{id}/preview` | administer lms_course | ~36 rows; field_body uses Twig+token HTML; mPDF renders PDF; field_design: classic/modern/elegant/minimal |
| 18 | `lms_user_certificate` | transactional | [lms_certificate](C05-modules/lms_certificate.md) | `lms_user_certificate` | (flat) | → user; → lms_certificate_template; → lms_quiz_result; → lms_course | `/my-certificates`, `/certificate/verify`, `/workspace/certificates`, `/workspace/certificates/{id}/regenerate`, `/admin/lms/certificates/{id}/revoke` | `/workspace/certificates`, `/admin/lms/certificates/{id}/revoke` | uid (owner) | ~52 rows; auto-issued by CertificateEventSubscriber on QuizPassedEvent; **BUG**: field_expiry_date is varchar(20) not int — inconsistent with all other date fields (see E02-issues) |
| 19 | `lms_badge` | editorial | [lms_badge](C05-modules/lms_badge.md) | `lms_badge` | (flat) | → taxonomy_term (badge_type); ← lms_user_badge | (none) | (none — no workspace UI) | administer site configuration | **stub**: table exists but NO DATA; zero award logic implemented; entities only |
| 20 | `lms_user_badge` | transactional | [lms_badge](C05-modules/lms_badge.md) | `lms_user_badge` | (flat) | → user; → lms_badge | (none) | (none) | uid (owner) | **stub**: NO DATA; no award/grant mechanism; created but never populated |
| 21 | `lms_notification` | transactional | [lms_notification](C05-modules/lms_notification.md) | `lms_notification` | (flat) | → user (recipient); polymorphic ref: field_reference_type + field_reference_id (no FK constraint) | `/notifications`, `/api/lms/notifications/unread-count` | `/admin/lms/notifications/broadcast` | uid (owner) | ~163 rows; in-app channel works; **email and push are stubs**; field_channel is multi-value junction table; field_type: enrollment/quiz_result/certificate/payment/forum_reply/membership_expiry/new_lesson/announcement/reminder/system |
| 22 | `lms_forum_topic` | transactional | [lms_forum](C05-modules/lms_forum.md) | `lms_forum_topic` | (flat) | → user; → lms_course; → taxonomy_term (forum_category); ← lms_forum_reply | `/forum`, `/forum/{id}` | (none — learner-created) | entity_create_access:lms_forum_topic | ~39 rows; field_replies_count and field_views are denormalized; **BUG**: ForumController::viewTopic throws "HARD DEBUG!" (use LmsForumController instead — RouteSubscriber overrides canonical route) |
| 23 | `lms_forum_reply` | transactional | [lms_forum](C05-modules/lms_forum.md) | `lms_forum_reply` | (flat) | → user; → lms_forum_topic (field_topic); self-ref field_parent (threading) | `/forum/{topic}/reply`, `/forum/reply/{id}/edit`, `/forum/reply/{id}/delete` | (learner-created) | entity_create_access:lms_forum_reply | ~164 rows; threaded via self-referential field_parent; field_best_answer flag |
| 24 | `lms_rating` | transactional | [lms_rating](C05-modules/lms_rating.md) | `lms_rating` | (flat) | → user; → lms_course | `/courses/{id}` (display), `/workspace/ratings`, `/workspace/ratings/{id}/{action}` | `/workspace/ratings` | uid (owner) | ~160 rows; 1–5 star + text review; field_approved default TRUE; **BUG**: 3 runtime routes missing (see E02-issues) |
| 25 | `lms_faq` | editorial | [lms_faq](C05-modules/lms_faq.md) | `lms_faq` | `lms_faq_field_data` | → lms_course (nullable); → taxonomy_term (faq_category) | `/courses/{id}` (course-scoped FAQs) | `/workspace/content/faqs/add`, `/workspace/content/faqs/{id}/edit` | administer lms_course | ~224 rows; translatable; field_scope: global/course; shown on course detail page when field_course matches |
| 26 | `lms_note` | transactional | [lms_note](C05-modules/lms_note.md) | `lms_note` | (flat) | → user; → lms_lesson; → lms_course | `/my-notes`, `/api/lms/note/save`, `/api/lms/note/delete`, `/api/lms/note/get` | `/my-notes` (read-only list) | uid (owner) | ~265 rows; field_video_timestamp stores second in video where note was taken |
| 27 | `lms_article` | editorial | [lms_blog](C05-modules/lms_blog.md) | `lms_article` | `lms_article_field_data` | (none — standalone) | `/manage/blog` CRUD only; public reads core node:blog | `/manage/blog`, `/manage/blog/add`, `/manage/blog/{id}/edit`, `/manage/blog/{id}/delete` | administer lms_blog | ~10 rows; **dual-track**: public `/news` reads `node:blog`; this entity backs manage UI only; translatable |
| 28 | `lms_wishlist` | transactional | [lms_wishlist](C05-modules/lms_wishlist.md) | `lms_wishlist` | (flat) | → user; → lms_course | `/my-wishlist`, `/api/lms/wishlist/toggle`, `/api/lms/wishlist/status` | `/my-wishlist` (read-only) | uid (owner) | ~85 rows; simple join entity (uid + course FK); no additional metadata |
| 29 | `lms_course_bundle` | editorial | [lms_bundle](C05-modules/lms_bundle.md) | `lms_course_bundle` | (flat) | → lms_course (field_courses) | (none — no routes) | (none — no workspace UI) | administer lms_course | **stub**: table exists but NO DATA; no checkout/order integration for bundles; field_courses junction table also empty |

---

## Domain Groupings

| Domain | Entities | Count |
|--------|----------|-------|
| Content | lms_course, lms_chapter, lms_lesson | 3 |
| Assessment | lms_quiz, lms_question (+lms_question_type config), lms_answer, lms_matching_pair, lms_quiz_result, lms_question_response | 6 + 1 config |
| Progress | lms_user_course, lms_user_lesson | 2 |
| Commerce | lms_order, lms_order_item, lms_membership_plan, lms_user_membership | 4 |
| Certificate | lms_certificate_template, lms_user_certificate | 2 |
| Communication | lms_notification, lms_forum_topic, lms_forum_reply, lms_rating, lms_faq, lms_note | 6 |
| Blog | lms_article | 1 |
| Commerce (support) | lms_wishlist, lms_course_bundle | 2 |
| Gamification | lms_badge, lms_user_badge | 2 |
| **Total** | | **29** |

---

## Module × Entity Ownership Summary

| Owning Module | Entities Owned | Entity Count |
|---------------|----------------|-------------|
| `lms_course` | lms_course | 1 |
| `lms_chapter` | lms_chapter | 1 |
| `lms_lesson` | lms_lesson | 1 |
| `lms_quiz` | lms_quiz, lms_quiz_result, lms_question_response | 3 |
| `lms_question` | lms_question, lms_question_type (config), lms_answer, lms_matching_pair | 4 |
| `lms_progress` | lms_user_course, lms_user_lesson | 2 |
| `lms_order` | lms_order, lms_order_item | 2 |
| `lms_membership` | lms_membership_plan, lms_user_membership | 2 |
| `lms_certificate` | lms_certificate_template, lms_user_certificate | 2 |
| `lms_badge` | lms_badge, lms_user_badge | 2 |
| `lms_notification` | lms_notification | 1 |
| `lms_forum` | lms_forum_topic, lms_forum_reply | 2 |
| `lms_rating` | lms_rating | 1 |
| `lms_faq` | lms_faq | 1 |
| `lms_note` | lms_note | 1 |
| `lms_blog` | lms_article | 1 |
| `lms_wishlist` | lms_wishlist | 1 |
| `lms_bundle` | lms_course_bundle | 1 |
| **Total** | | **29** |

Modules with no owned custom entities (service/integration only): `lms_base`, `lms_content_tools`, `lms_payment`, `lms_stripe`, `lms_paypal`, `lms_zoom`, `lms_ai`, `lms_analytics`, `lms_mobile_api`, `open_switcher`.

---

## Certificate Issuance Chain (as-built)

```
lms_quiz.submit (POST /quiz/{id}/submit)
  └─ QuizController::submitQuiz
       └─ GradingStrategyFactory::grade(quiz, responses)   [static PHP factory]
            └─ score >= field_threshold → passed = TRUE
                 └─ dispatch(QuizPassedEvent)
                      └─ CertificateEventSubscriber::onQuizPassed
                           ├─ load lms_quiz_result
                           ├─ traverse: lms_quiz → lms_lesson → lms_chapter → lms_course
                           ├─ read: lms_course.field_certificate_template → lms_certificate_template
                           ├─ render: field_layout / field_body (Twig + tokens)
                           ├─ generate PDF via mPDF → store as file entity
                           └─ create lms_user_certificate {uid, template, quiz_result, course, pdf_file}
```

No rollback or compensation logic exists on partial failure. Essay questions skip auto-grading (field_pending_review = 1); QuizPassedEvent is not dispatched until essay is manually graded.

---

## DB Table Patterns (as-built)

| Pattern | Tables | Example Entities |
|---------|--------|-----------------|
| Translatable: base + `_field_data` | lms_course + lms_course_field_data | lms_course, lms_chapter, lms_lesson, lms_quiz, lms_question, lms_faq, lms_article |
| Flat (non-translatable, single table) | lms_order (flat) | lms_order, lms_order_item, lms_quiz_result, lms_user_course, lms_user_lesson, lms_rating, lms_notification, lms_note, lms_wishlist, lms_membership_plan, lms_user_membership, lms_certificate_template, lms_user_certificate, lms_badge, lms_user_badge, lms_forum_topic, lms_forum_reply, lms_answer, lms_matching_pair, lms_question_response, lms_course_bundle |
| Multi-value field junction (`__field_*`) | lms_course__field_chapters | used where cardinality = -1 (unlimited) |
| Empty tables (stub/dead) | lms_badge, lms_user_badge, lms_course_bundle, lms_course_bundle__field_courses, lms_quiz__field_prerequisite_quizzes | no data; schema only |

No revision tables exist for any lms_* entity. No FK constraints enforced at DB level for most references — application enforces integrity.

---

## Known Issues Touching Entities

| Issue | Entity / Field | Severity |
|-------|---------------|---------|
| `lms_user_certificate.field_expiry_date` is `varchar(20)` not `int` | lms_user_certificate | BUG — type inconsistency; see [E02-issues](../E-Quality/E02-issues/) |
| `ForumController::viewTopic` throws `"HARD DEBUG!"` | lms_forum_topic | BUG — use LmsForumController (RouteSubscriber override is correct); legacy class is dead code |
| `lms_rating` 3 runtime routes missing | lms_rating | BUG — RatingController operation links may 404 |
| lms_badge / lms_user_badge: zero award logic | lms_badge, lms_user_badge | stub — entities exist, no automation |
| lms_course_bundle: no order/checkout integration | lms_course_bundle | stub — schema exists, no data, no routes |
| lms_notification email/push channels | lms_notification.field_channel | stub — in_app works; email and push are unimplemented |
| No webhook endpoints for payment gateways | lms_order, lms_order_item | security/reliability risk — Stripe webhook signature verification is TODO |
| Stripe PaymentIntent created on form BUILD | lms_order | BUG — one PaymentIntent per page load, not per submit |

---

## Ownership Rules (as per template)

- Each entity has exactly one owning module (writes). Other modules read via service calls or direct entity API.
- Cross-module entity reads (e.g., `lms_certificate` reading `lms_course` and `lms_quiz_result`) are acceptable within the certificate issuance chain but should not proliferate.
- `lms_course`'s `AdminController` directly manages quiz, order, and user entities — a deliberate hub-and-spoke pattern for the workspace UI, documented as an architectural decision in [C01-architecture-decision.md](C01-architecture-decision.md).

---

## Cross-References

- Entity detail: [C04-entities/](C04-entities/)
- Module detail: [C05-modules/](C05-modules/)
- API surface detail: [C06-apis/](C06-apis/)
- Page implementations: [C11-pages-components/](C11-pages-components/)
- Workflows: [C12-workflows/](C12-workflows/)
- Known issues: [E02-issues/](../E-Quality/E02-issues/)
