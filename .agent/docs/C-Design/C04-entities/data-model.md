---
mode: as-built
---

# C04 — Data Model: Entity Relationships

**Purpose:** ERD-style reference for all entity relationships, the Course→Chapter→Lesson→Quiz hierarchy, progress/order/certificate relationships, denormalized field caches, and taxonomy-driven quiz_result pass/fail.
**Mode:** as-built — reflects `db-schema.md` from 2026-06-20 dump of `everlms_pro`.

---

## 1. Full Entity Relationship Map (text ERD)

```
user (Drupal core)
│
├── OWNS (uid FK) ─────────────────────────────────────────────────────────
│   lms_course          lms_quiz          lms_article
│   lms_forum_topic     lms_forum_reply   lms_user_badge
│   lms_user_certificate                  lms_user_membership
│   lms_user_course     lms_user_lesson   lms_note
│   lms_notification    lms_order         lms_rating
│   lms_wishlist
│
└── REFERENCED AS co-instructor ──────────────────────────────────────────
    lms_course.field_instructor[] → user (junction: lms_course__field_instructor)

────────────────────────────────────────────────────────────────────────────
CONTENT HIERARCHY
────────────────────────────────────────────────────────────────────────────

lms_course  ──field_chapters[]──►  lms_chapter  ──field_lessons[]──►  lms_lesson
  (lms_course__field_chapters)       (lms_chapter__field_lessons)
                                                         │
                                                field_quiz (single)
                                                         │
                                                         ▼
                                                     lms_quiz

────────────────────────────────────────────────────────────────────────────
ASSESSMENT HIERARCHY
────────────────────────────────────────────────────────────────────────────

lms_quiz
│ field_questions[]──────────────────────────────────────────────────────────
│   (lms_quiz__field_questions)                                              │
│                                                                            ▼
│                                                              lms_question (bundled)
│                                                              │  field_choices[]──► lms_answer
│                                                              │    (lms_question__field_choices)
│                                                              │  field_matching_pairs[]──► lms_matching_pair
│                                                              │    (lms_question__field_matching_pairs)
│                                                              │  field_correct_answers[] (string, fill_blank/short_answer)
│                                                              │    (lms_question__field_correct_answers)
│                                                              │  field_ordered_items[] (string, ordering)
│                                                              │    (lms_question__field_ordered_items)
│                                                              │  field_difficulty──► taxonomy_term (quiz_difficulty)
│
│ field_prerequisite_quizzes[]──► lms_quiz (self, lms_quiz__field_prerequisite_quizzes — currently empty)

lms_quiz_result
│  uid ──► user
│  field_quiz ──► lms_quiz
│  field_result ──► taxonomy_term (quiz_results)   ← PASS/FAIL IS TAXONOMY-DRIVEN
│  field_responses[]──► lms_question_response
│    (lms_quiz_result__field_responses)

lms_question_response (per-question graded record)
│  field_question ──► lms_question
│  field_quiz ──► lms_quiz
│  field_answers[]──► lms_answer   (selected choices, lms_question_response__field_answers)
│  field_text_answer (essay/short_answer)
│  field_correct (boolean, graded)
│  field_pending_review (boolean, essay awaiting instructor)
│  field_teacher_feedback

────────────────────────────────────────────────────────────────────────────
PROGRESS RELATIONSHIPS
────────────────────────────────────────────────────────────────────────────

lms_user_course  (enrollment + course-level progress)
│  uid ──► user
│  field_course ──► lms_course
│  field_progress  (decimal 5,2 — 0.00 to 100.00%)
│  field_status    (enrolled / in_progress / completed)
│  field_enrolled_at, field_completed_at

lms_user_lesson  (lesson-level progress)
│  uid ──► user
│  field_lesson ──► lms_lesson
│  field_status    (not_started / in_progress / completed)
│  field_video_position  (seconds resume point)
│  field_completed_at

NOTE: There is NO lms_user_chapter entity — chapter progress is not tracked at entity level.
      Course progress % is derived from lms_user_lesson completion counts, not from a chapter tracker.
      lms_user_course does not FK to lms_order — enrollment and payment are independent.

────────────────────────────────────────────────────────────────────────────
CERTIFICATE RELATIONSHIPS
────────────────────────────────────────────────────────────────────────────

lms_certificate_template  (reusable PDF layout)
│  Referenced by:
│  ├── lms_course.field_certificate_template ──► lms_certificate_template
│  └── lms_user_certificate.field_template ──► lms_certificate_template

lms_user_certificate  (issued certificate instance)
│  uid ──► user
│  field_template ──► lms_certificate_template
│  field_quiz_result ──► lms_quiz_result  (nullable — trigger event)
│  field_course ──► lms_course
│  field_pdf_file ──► file_managed  (generated PDF via mPDF)
│  field_certificate_number  (unique string)
│  field_issued_at  (Unix timestamp)
│  field_expiry_date  (varchar(20) — KNOWN BUG: stored as date string, not Unix int)
│  field_revoked, field_revoke_reason

ISSUANCE FLOW: QuizPassedEvent → lms_certificate EventSubscriber
  → traverses lms_quiz_result → lms_quiz → lms_lesson → lms_chapter → lms_course
  → reads lms_course.field_certificate_template
  → generates PDF via mPDF
  → creates lms_user_certificate entity

────────────────────────────────────────────────────────────────────────────
COMMERCE RELATIONSHIPS
────────────────────────────────────────────────────────────────────────────

lms_order
│  uid ──► user (buyer)
│  field_items[]──► lms_order_item
│    (lms_order__field_items)
│  field_status: pending / processing / completed / cancelled / refunded
│  field_payment_method: bank_transfer / stripe / (paypal — stub)
│  field_payment_id (gateway transaction ID)
│  field_payment_receipt ──► file_managed

lms_order_item (one per product in the order)
│  field_course ──► lms_course          (nullable — null if membership)
│  field_membership_plan ──► lms_membership_plan  (nullable — null if course)
│  field_type: course / bundle / membership
│  field_price (price at time of purchase — snapshot)

lms_membership_plan
│  field_billing_cycle: monthly / quarterly / yearly / lifetime
│  field_course_access: all / selected
│  field_features[] (string multi-value, lms_membership_plan__field_features)
│  Referenced by: lms_user_membership, lms_order_item

lms_user_membership
│  uid ──► user
│  field_plan ──► lms_membership_plan
│  field_status: active / trial / expired / cancelled
│  field_start_date, field_end_date  (Unix timestamps)
│  field_auto_renew

lms_course_bundle  (groups of courses sold together — NO DATA currently)
│  field_courses[]──► lms_course
│    (lms_course_bundle__field_courses — currently empty)
│  field_price, field_discount_percent
│  Status: schema present, zero rows in DB

────────────────────────────────────────────────────────────────────────────
RATING / REVIEW → DENORMALIZED CACHE
────────────────────────────────────────────────────────────────────────────

lms_rating (one per user per course)
│  uid ──► user
│  field_course ──► lms_course
│  field_score: integer 1–5
│  field_review (text_long)
│  field_approved (boolean, default TRUE)
│  field_is_featured (boolean — homepage highlight)

DENORMALIZED CACHE: lms_course_field_data.field_rating (decimal 3,1)
  This column stores the running average of all approved lms_rating.field_score
  values for a given course. It is NOT computed at query time — it is updated
  (denormalized write-through) whenever a rating is created or changed.
  The lms_rating module EventSubscriber / service is responsible for recalculating
  and saving the cache value to the course entity.

────────────────────────────────────────────────────────────────────────────
FORUM RELATIONSHIPS
────────────────────────────────────────────────────────────────────────────

lms_forum_topic
│  uid ──► user (author)
│  field_course ──► lms_course  (nullable — course-specific vs global)
│  field_category ──► taxonomy_term (forum_category)
│  field_replies_count (denormalized integer counter)
│  field_views (denormalized integer view counter)

lms_forum_reply
│  uid ──► user (author)
│  field_topic ──► lms_forum_topic
│  field_parent ──► lms_forum_reply  (self-referential — threaded replies)
│  field_best_answer (boolean)
│  field_upvotes (integer)

KNOWN BUG: ForumController::viewTopic throws a hard "HARD DEBUG!" error — forum
topic view is broken in current code.

────────────────────────────────────────────────────────────────────────────
BADGE RELATIONSHIPS (NEAR-STUB)
────────────────────────────────────────────────────────────────────────────

lms_badge
│  field_type ──► taxonomy_term (badge_type)
│  field_icon ──► file_managed
│  field_criteria (string 1024, JSON criteria)
│  field_xp_reward (integer)
│  Status: entities exist in schema, NO DATA in DB — zero award logic implemented

lms_user_badge
│  uid ──► user
│  field_badge ──► lms_badge
│  field_awarded_at (Unix timestamp)
│  Status: NO DATA in DB

────────────────────────────────────────────────────────────────────────────
COMMUNICATION RELATIONSHIPS
────────────────────────────────────────────────────────────────────────────

lms_notification
│  uid ──► user (recipient)
│  field_type: enrollment / quiz_result / certificate / payment / forum_reply
│              / membership_expiry / new_lesson / announcement / reminder / system
│  field_channel[] (multi-value string: in_app / email / push)
│    (lms_notification__field_channel)
│  field_reference_type (string) + field_reference_id (int) — raw polymorphic ref (no FK)
│  field_read (boolean)
│  Channels: in_app = REAL; email = STUB; push = STUB

lms_note (student private notes)
│  uid ──► user
│  field_course ──► lms_course
│  field_lesson ──► lms_lesson
│  field_video_timestamp (integer seconds)

lms_wishlist
│  uid ──► user
│  field_course ──► lms_course

lms_rating ── see Rating / Review section above

────────────────────────────────────────────────────────────────────────────
BLOG DUAL-TRACK
────────────────────────────────────────────────────────────────────────────

node:blog  (public frontend blog — Drupal core node)
│  field_blog_tags ──► taxonomy_term (blog_tags)
│  field_thumb ──► file_managed (image)
│  Rendered at /blog/* routes via lms_blog controller

lms_article  (admin CRUD backend only — lms_blog module)
│  Backs /manage/blog admin routes
│  title, content, thumbnail, seo_title, seo_description
│  NOT rendered on the public frontend
│  Status: as-built; dual-track is intentional

────────────────────────────────────────────────────────────────────────────
TAXONOMY RELATIONSHIPS (SUMMARY)
────────────────────────────────────────────────────────────────────────────

taxonomy_term (quiz_results)
  └── lms_quiz_result.field_result (PASS/FAIL term TID)
        The GradingStrategyFactory resolves a term TID for pass or fail,
        dispatches QuizPassedEvent on pass term.

taxonomy_term (quiz_difficulty)
  └── lms_question.field_difficulty

taxonomy_term (course_category)  [hierarchical]
  └── lms_course.field_category

taxonomy_term (course_level)
  └── lms_course.field_level

taxonomy_term (course_language)
  └── lms_course.field_language

taxonomy_term (course_tags)
  └── lms_course.field_tags[]  (junction lms_course__field_tags — currently empty)

taxonomy_term (badge_type)
  └── lms_badge.field_type

taxonomy_term (blog_tags)
  └── node:blog.field_blog_tags

taxonomy_term (faq_category)
  └── lms_faq.field_category

taxonomy_term (forum_category)
  └── lms_forum_topic.field_category

taxonomy_term (tags)
  └── node:article.field_tags[]
  └── user.field_preferred_categories[] (intended for course_category; field allows any vocab)
```

---

## 2. Content Hierarchy: Course → Chapter → Lesson → Quiz

The primary learning content is organized as a strict 4-level hierarchy. The hierarchy is implemented via multi-value entity_reference fields (stored in Drupal junction tables), NOT via parent FK columns on child records.

```
lms_course  (top of hierarchy)
  ├── uuid, name, uid (instructor), status
  ├── Taxonomy refs: field_category, field_level, field_language, field_tags[]
  ├── Media: field_thumbnail, field_banner
  ├── Commercial: field_price, field_price_type
  ├── field_certificate_template ──► lms_certificate_template
  ├── field_rating (DENORMALIZED average — updated on rating save)
  │
  └── field_chapters[]  (junction: lms_course__field_chapters)
        ▼
      lms_chapter
        ├── name, weight, status
        │
        └── field_lessons[]  (junction: lms_chapter__field_lessons)
              ▼
            lms_lesson
              ├── name, field_type, weight, status
              ├── Content: field_content, field_video_url, field_video_file
              │           field_h5p_embed, field_scorm_package, field_pptx_attachment
              ├── field_free_preview, field_drip_days, field_duration
              │
              └── field_quiz (single ref)
                    ▼
                  lms_quiz
                    ├── name, field_threshold, field_time_limit
                    ├── field_form_style, field_question_count
                    ├── field_take_quiz_number (max attempts)
                    ├── field_prerequisite_quizzes[] (self-ref — currently no data)
                    │
                    └── field_questions[]  (junction: lms_quiz__field_questions)
                          ▼
                        lms_question  (bundle = lms_question_type)
                          ├── Bundles: essay, fill_blank, matching, multiple_choice,
                          │           ordering, short_answer, single_choice, true_false
                          ├── name (question stem), field_points, field_difficulty
                          ├── field_choices[]──► lms_answer  (single/multiple_choice)
                          ├── field_matching_pairs[]──► lms_matching_pair  (matching)
                          ├── field_correct_answers[]  (fill_blank, short_answer)
                          └── field_ordered_items[]  (ordering)
```

**Key architecture facts:**
- The hierarchy direction is top-down: course references chapters, chapters reference lessons, lesson references quiz. Child records do NOT store a parent FK — navigation up the tree requires a reverse lookup.
- `GradingStrategyFactory` selects the correct grading strategy per question bundle using a static PHP factory (not a Drupal Plugin manager).
- All grading is **strictly binary**: correct or incorrect (no partial credit). Essay results in `pending_review` status until instructor grades manually.
- Certificate issuance traverses the hierarchy bottom-up: `QuizPassedEvent` → `lms_quiz_result` → `lms_quiz` → `lms_lesson` (via reverse lookup) → `lms_chapter` → `lms_course` → `field_certificate_template` → generate PDF via mPDF.

---

## 3. Progress / Enrollment Relationships

```
[Student enrolls in course]
        │
        ▼
lms_user_course  (enrollment record + course-level progress aggregate)
  uid ──► user
  field_course ──► lms_course
  field_progress: 0.00–100.00%  (updated as lessons complete)
  field_status: enrolled → in_progress → completed
  field_enrolled_at, field_completed_at

[Student completes a lesson]
        │
        ▼
lms_user_lesson  (per-lesson progress)
  uid ──► user
  field_lesson ──► lms_lesson
  field_status: not_started / in_progress / completed
  field_video_position: last watched second (resume)
  field_completed_at

NOTE: lms_user_course.field_progress is calculated from
  (count of completed lms_user_lesson records for this course's lessons)
  / (total lessons in course) × 100

NOTE: lms_user_course is NOT FK-linked to lms_order — students can be
  manually enrolled without payment.
```

---

## 4. Order / Payment Relationships

```
lms_order  (one per checkout session)
  uid ──► user (buyer)
  field_order_number (human-readable, e.g. ORD-2024-001)
  field_status: pending → processing → completed | cancelled | refunded
  field_payment_method: bank_transfer (REAL) | stripe (REAL) | paypal (STUB) | zalopay (STUB)
  field_payment_id: gateway transaction ID
  field_payment_receipt ──► file_managed (bank transfer upload)
  field_items[]──► lms_order_item
    (junction: lms_order__field_items)

lms_order_item  (one per product)
  field_type: course | bundle | membership
  field_course ──► lms_course        (null when type = membership)
  field_membership_plan ──► lms_membership_plan  (null when type = course)
  field_price: price SNAPSHOT at time of purchase (not live price)

PAYMENT GATEWAY PLUGIN HOST: lms_payment module
  Plugin: PaymentGatewayManager (@PaymentGateway annotation, PaymentGatewayInterface)
  BankTransferGateway: REAL — manual transfer + receipt upload
  Stripe: REAL — PaymentIntent via lms_stripe
    KNOWN BUG: PaymentIntent created on form BUILD (every page load, not on submit)
  PayPal (lms_paypal): DEAD CODE — wrong interface, no @PaymentGateway annotation
  Webhook endpoints: NOT IMPLEMENTED — no webhook routes exist anywhere
```

---

## 5. Quiz Result → Pass/Fail → Certificate (taxonomy-driven)

```
Student submits quiz
        │
        ▼
GradingStrategyFactory (static PHP factory in lms_quiz)
  Selects strategy class per lms_question bundle:
    MultipleChoiceStrategy, SingleChoiceStrategy, TrueFalseStrategy,
    FillBlankStrategy, ShortAnswerStrategy, MatchingStrategy,
    OrderingStrategy, EssayStrategy
  Binary grading: correct or incorrect (no partial credit)
  EssayStrategy → sets field_pending_review = TRUE
        │
        ▼
lms_quiz_result (created)
  field_score: decimal(5,2) percentage
  field_result ──► taxonomy_term (quiz_results)
                   PASS term: score >= lms_quiz.field_threshold
                   FAIL term: score < lms_quiz.field_threshold
                   PENDING: when essay pending_review (result not set until graded)
  field_responses[]──► lms_question_response (one per question)
        │
        ├── [if PASS]
        │       ▼
        │   QuizPassedEvent dispatched
        │       │
        │       ▼
        │   lms_certificate EventSubscriber
        │       traverses: quiz_result → quiz → lesson (reverse lookup)
        │                             → chapter → course
        │                             → course.field_certificate_template
        │       Creates lms_user_certificate
        │       Generates PDF via mPDF
        │       Saves to file_managed
        │
        └── [if FAIL] — no further action; student may retry if take_quiz_number allows
```

---

## 6. Denormalized Caches

| Cache field | Location | Computed from | Updated when |
|-------------|----------|--------------|--------------|
| `field_rating` | `lms_course_field_data` (decimal 3,1) | Average of approved `lms_rating.field_score` rows for the course | Rating created, updated, or deleted; responsibility of `lms_rating` module service/subscriber |
| `field_replies_count` | `lms_forum_topic` (int) | Count of `lms_forum_reply` rows for the topic | Forum reply created or deleted |
| `field_views` | `lms_forum_topic` (int) | Incremented on topic view | Each topic page request (via ForumController) |
| `field_duration` | `lms_course_field_data` (int, minutes) | Sum of `lms_lesson.field_duration` for all lessons in course | «unverified» — field exists, update trigger not confirmed in facts |

---

## 7. Data Integrity Notes and Known Bugs

| # | Finding | Severity | Location |
|---|---------|----------|----------|
| 1 | `lms_user_certificate.field_expiry_date` is `varchar(20)` not `int` | Bug | `lms_user_certificate` table; inconsistent with all other date fields which use Unix int |
| 2 | Polymorphic ref in `lms_notification` (`field_reference_type` + `field_reference_id`) has no FK constraint and no entity_reference validation | Risk | `lms_notification` table |
| 3 | `lms_course__field_tags` junction table is empty — no course has tags assigned | Observation | `lms_course__field_tags` |
| 4 | `lms_quiz__field_prerequisite_quizzes` junction table is empty — prerequisite quiz chain unused | Observation | `lms_quiz__field_prerequisite_quizzes` |
| 5 | `lms_badge` and `lms_user_badge` have no data — badge award logic is not implemented | Stub | `lms_badge`, `lms_user_badge` |
| 6 | `lms_course_bundle` has no data — bundle commerce is not implemented | Stub | `lms_course_bundle` |
| 7 | `lms_rating` missing 3 runtime routes | Bug | `lms_rating` module |
| 8 | `ForumController::viewTopic` throws "HARD DEBUG!" | Bug | `lms_forum` module |
| 9 | Stripe `PaymentIntent` created on form BUILD (one per page load, not per submit) | Bug | `lms_stripe` module |
| 10 | No webhook endpoints exist — Stripe/PayPal signature verification is not implemented | Risk | `lms_payment`, `lms_stripe` |

---

## 8. Table Storage Patterns

Drupal 11 uses two distinct storage patterns for the custom entities:

| Pattern | Entities | Tables |
|---------|----------|--------|
| **Translatable** (base + field_data) | lms_course, lms_chapter, lms_lesson, lms_quiz, lms_question, lms_faq, lms_article | `{entity}` (id/uuid/langcode) + `{entity}_field_data` (all field columns, PK = id+langcode) |
| **Flat** (all fields in base table) | All other 21 custom entities | Single `{entity}` table holds all columns |

Multi-value fields always use a separate `{entity}__{field_name}` junction table regardless of pattern.

No revision tables exist for any `lms_*` entity.

---

## Cross-References

- Per-vocabulary detail: [06-taxonomies.md](./06-taxonomies.md)
- Content entities (Course, Chapter, Lesson): [01-content-entities.md](./01-content-entities.md)
- Assessment entities (Quiz, Question, etc.): [02-assessment-entities.md](./02-assessment-entities.md)
- Progress entities: [03-progress-entities.md](./03-progress-entities.md)
- Commerce entities: [04-commerce-entities.md](./04-commerce-entities.md)
- Certificate entities: [05-certificate-entities.md](./05-certificate-entities.md)
- Module that installs taxonomies and base utilities: [`lms_base`](../C05-modules/lms_base.md)
- Quiz grading architecture: [`lms_quiz`](../C05-modules/lms_quiz.md)
- Payment plugin host: [`lms_payment`](../C05-modules/lms_payment.md)
- Certificate issuance: [`lms_certificate`](../C05-modules/lms_certificate.md)
- Entity/module matrix: [`../C03-entity-module-map.md`](../C03-entity-module-map.md)
- Known issues tracker: [`../../E-Quality/E02-issues/`](../../E-Quality/E02-issues/)
