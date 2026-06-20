# C04 — Entities: Folder Index

**Purpose:** Per-entity canonical specs for all 29 custom EverLMS entities (28 ContentEntity + 1 ConfigEntity) plus cross-cutting design documents (data model, taxonomy catalog).
**Authority:** [`../C03-entity-module-map.md`](../C03-entity-module-map.md) — rolls up this folder into the entity↔module matrix.
**Stack:** Drupal 11.3.5, MariaDB 10.11 (`everlms_pro`), PHP 8.3.

---

## File Index

| File | Mode | Contents |
|------|------|----------|
| [README.md](./README.md) | — | This index |
| [data-model.md](./data-model.md) | as-built | ERD-style reference: all entity relationships, Course→Chapter→Lesson→Quiz hierarchy, progress/order/certificate relationships, denormalized caches, taxonomy-driven quiz_result pass/fail, known bugs |
| [06-taxonomies.md](./06-taxonomies.md) | as-built | All 11 taxonomy vocabularies: id, label, hierarchy, what it categorizes, referencing entity fields, seeded terms, extra configurable fields |
| 01-content-entities.md | as-built | lms_course, lms_chapter, lms_lesson, lms_article, lms_faq |
| 02-assessment-entities.md | as-built | lms_quiz, lms_question (8 bundles), lms_answer, lms_matching_pair, lms_quiz_result, lms_question_response, lms_question_type |
| 03-progress-entities.md | as-built | lms_user_course, lms_user_lesson, lms_note |
| 04-commerce-entities.md | as-built | lms_order, lms_order_item, lms_membership_plan, lms_user_membership, lms_wishlist, lms_course_bundle |
| 05-certificate-entities.md | as-built | lms_certificate_template, lms_user_certificate |
| 07-communication-entities.md | as-built | lms_notification, lms_forum_topic, lms_forum_reply, lms_rating |
| 08-gamification-entities.md | as-built | lms_badge, lms_user_badge |

---

## Entity Summary (29 custom entities)

### Content Entities (28 ContentEntity)

| # | Entity ID | Label | Module | Base Table | Translatable | Bundled |
|---|-----------|-------|--------|------------|-------------|---------|
| 1 | `lms_badge` | Badge | lms_badge | lms_badge | No | No |
| 2 | `lms_user_badge` | User Badge | lms_badge | lms_user_badge | No | No |
| 3 | `lms_article` | Article | lms_blog | lms_article | **Yes** | No |
| 4 | `lms_course_bundle` | Course Bundle | lms_bundle | lms_course_bundle | No | No |
| 5 | `lms_certificate_template` | Certificate Template | lms_certificate | lms_certificate_template | No | No |
| 6 | `lms_user_certificate` | User Certificate | lms_certificate | lms_user_certificate | No | No |
| 7 | `lms_chapter` | Chapter | lms_chapter | lms_chapter | **Yes** | No |
| 8 | `lms_course` | Course | lms_course | lms_course | **Yes** | No |
| 9 | `lms_faq` | FAQ | lms_faq | lms_faq | **Yes** | No |
| 10 | `lms_forum_reply` | Forum Reply | lms_forum | lms_forum_reply | No | No |
| 11 | `lms_forum_topic` | Forum Topic | lms_forum | lms_forum_topic | No | No |
| 12 | `lms_lesson` | Lesson | lms_lesson | lms_lesson | **Yes** | No |
| 13 | `lms_membership_plan` | Membership Plan | lms_membership | lms_membership_plan | No | No |
| 14 | `lms_user_membership` | User Membership | lms_membership | lms_user_membership | No | No |
| 15 | `lms_note` | Student Note | lms_note | lms_note | No | No |
| 16 | `lms_notification` | Notification | lms_notification | lms_notification | No | No |
| 17 | `lms_order` | Order | lms_order | lms_order | No | No |
| 18 | `lms_order_item` | Order Item | lms_order | lms_order_item | No | No |
| 19 | `lms_user_course` | User Course Progress | lms_progress | lms_user_course | No | No |
| 20 | `lms_user_lesson` | User Lesson Progress | lms_progress | lms_user_lesson | No | No |
| 21 | `lms_answer` | Answer | lms_question | lms_answer | No | No |
| 22 | `lms_matching_pair` | Matching Pair | lms_question | lms_matching_pair | No | No |
| 23 | `lms_question` | Question | lms_question | lms_question | **Yes** | **Yes** (8 bundles) |
| 24 | `lms_question_response` | Question Response | lms_quiz | lms_question_response | No | No |
| 25 | `lms_quiz` | Quiz | lms_quiz | lms_quiz | **Yes** | No |
| 26 | `lms_quiz_result` | Quiz Result | lms_quiz | lms_quiz_result | No | No |
| 27 | `lms_rating` | Rating | lms_rating | lms_rating | No | No |
| 28 | `lms_wishlist` | Wishlist Item | lms_wishlist | lms_wishlist | No | No |

### Config Entities (1 ConfigEntity)

| Entity ID | Label | Module | Config Prefix | Bundle of |
|-----------|-------|--------|--------------|-----------|
| `lms_question_type` | Question Type | lms_question | `lms_question.question_type` | `lms_question` |

---

## Question Bundles (8)

`lms_question` is the only bundled custom entity. Bundles are defined by the `lms_question_type` config entity.

| Bundle ID | Label | Key fields |
|-----------|-------|-----------|
| `essay` | Essay | `field_min_words`, `field_max_words`; graded manually (pending_review) |
| `fill_blank` | Fill Blank | `field_correct_answers[]`, `field_case_sensitive` |
| `matching` | Matching | `field_matching_pairs[]` → lms_matching_pair |
| `multiple_choice` | Multiple Choice | `field_choices[]` → lms_answer (multiple correct) |
| `ordering` | Ordering | `field_ordered_items[]` (string multi-value) |
| `short_answer` | Short Answer | `field_correct_answers[]`, `field_case_sensitive` |
| `single_choice` | Single Choice | `field_choices[]` → lms_answer (one correct) |
| `true_false` | True/False | `field_correct_answer` (boolean) |

---

## Taxonomy Vocabularies (11)

All 11 active vocabularies — see [06-taxonomies.md](./06-taxonomies.md) for full detail.

| vid | Label | Hierarchy | Used by |
|-----|-------|-----------|---------|
| `badge_type` | Badge Type | Flat | `lms_badge.field_type` |
| `blog_tags` | Blog Tags | Flat | `node:blog.field_blog_tags` |
| `course_category` | Course Category | **Nested** | `lms_course.field_category` |
| `course_language` | Course Language | Flat | `lms_course.field_language` |
| `course_level` | Course Level | Flat | `lms_course.field_level` |
| `course_tags` | Course Tags | Flat | `lms_course.field_tags[]` |
| `faq_category` | FAQ Category | Flat | `lms_faq.field_category` |
| `forum_category` | Forum Category | Flat | `lms_forum_topic.field_category` |
| `quiz_difficulty` | Quiz Difficulty | Flat | `lms_question.field_difficulty` |
| `quiz_results` | Quiz Results | Flat | `lms_quiz_result.field_result` (pass/fail term) |
| `tags` | Tags | Flat | `node:article.field_tags[]` |

---

## State Machines

| Entity | States | Module | Trigger |
|--------|--------|--------|---------|
| `lms_course` | draft (status=0) → published (status=1) | lms_course | Editor publish action |
| `lms_lesson` | draft → published | lms_lesson | Editor publish action |
| `lms_quiz` | draft → published | lms_quiz | Editor publish action |
| `lms_order` | pending → processing → completed \| cancelled \| refunded | lms_order / lms_payment | Payment gateway callback |
| `lms_user_course` | enrolled → in_progress → completed | lms_progress | Lesson completion event |
| `lms_user_lesson` | not_started → in_progress → completed | lms_progress | Student lesson interaction |
| `lms_user_membership` | active \| trial → expired \| cancelled | lms_membership | Cron / payment event |
| `lms_quiz_result` | (graded) → Passed \| Failed; essay: pending_review (Pending term) | lms_quiz | GradingStrategyFactory; manual instructor grade |

---

## Core / Contributed Entity Types (active)

| Entity ID | Provider | Notes |
|-----------|----------|-------|
| `node` | Drupal core | 6 bundles: article, blog, glossary, introduction, page, slideshow |
| `taxonomy_term` | Drupal core | 11 vocabularies |
| `user` | Drupal core | 14 custom profile fields; email login via lms_base |
| `comment` | Drupal core | 2 types: comment (node:article), lesson_comment (lms_lesson) |
| `block_content` | Drupal core | 2 types: basic, slideshow_item |
| `file` | Drupal core | Used as target by image/file fields across all entities |

---

## Cross-References

- Entity↔module ownership matrix: [`../C03-entity-module-map.md`](../C03-entity-module-map.md)
- Module specifications: [`../C05-modules/`](../C05-modules/)
- Entity relationship diagram and hierarchy: [data-model.md](./data-model.md)
- Taxonomy vocabularies: [06-taxonomies.md](./06-taxonomies.md)
- API routes per entity: [`../C06-apis/`](../C06-apis/) **
- Acceptance criteria: [`../../B-Requirements/B05-acceptance-criteria/`](../../B-Requirements/B05-acceptance-criteria/)
- Known issues: [`../../E-Quality/E02-issues/`](../../E-Quality/E02-issues/) **
