---
id: F-001
name: Course Management
priority: Must
status: APPROVED
parent-requirements: [BR-001]
owning_modules: [lms_course, lms_chapter, lms_lesson, lms_faq, lms_base]
primary_source: C05
story_anchors: []
---

# F-001 — Course Management

## User story

As an **instructor or administrator**, I want to create and manage courses with a structured curriculum of chapters and lessons, so that learners can discover and consume organised learning content.

## Description

Course Management is the core content backbone of EverLMS. It delivers the full lifecycle of a course: creation, structuring, publishing, and catalogue browsing. The hierarchy is three levels deep: **Course → Chapter → Lesson**. Courses carry rich metadata (category, level, language, price type, instructors, tags, thumbnail/banner), chapters carry ordered groups of lessons, and lessons support nine content types (video, text, quiz, H5P, SCORM, PDF, PPTX, Zoom, mixed).

The feature covers both the learner-facing public surface (catalogue, detail page, categories, glossary, free-enroll flow, instructor profile) and the manager/admin workspace (dashboard, CRUD forms with role-differentiated views, content listing, FAQ management). `lms_course` acts as the admin shell module, hosting workspace and admin routes that span courses, quizzes, orders, users, and CMS content.

Blog/news is a related but separate feature: the public `/news` front-end reads `node:blog` (Drupal core content); the `lms_article` entity backs the admin CRUD at `/manage/blog` only. FAQ entries have a three-scope model (global, course, lesson) consumed by `CoursePublicController`.

## In scope

- `lms_course` entity CRUD with all base fields: name, description, short description, what-to-learn, prerequisites, who-for, thumbnail, banner, chapters (reference), category, tags, level, language, instructor(s), price, price type (free/paid/membership), duration, rating, bestseller flag, featured flag, certificate template reference, status.
- `lms_chapter` entity CRUD: name, description, ordered lessons (reference), weight, status.
- `lms_lesson` entity CRUD supporting nine `field_type` values: `video`, `text`, `quiz`, `h5p`, `scorm`, `pdf`, `pptx`, `zoom`, `mixed`. Conditional form fields per type implemented via Drupal Form API `#states` in `LessonForm`.
- SCORM package auto-extraction on insert/update via `_lms_lesson_extract_scorm()` (PHP `ZipArchive`, reads `imsmanifest.xml`, creates `index.html` redirect).
- Drip-content field (`field_drip_days`): days after enrollment before a lesson unlocks.
- Free-preview flag (`field_free_preview`): lesson viewable without enrollment.
- Lesson file attachments (PDF, doc, ppt, xls, zip) and comments (`lesson_comment` comment type).
- Public course catalogue (`/courses`) with paginated, filterable listing (search, category, level, price, rating, sort); AJAX search API (`/api/courses/search`).
- Course detail page (`/courses/{lms_course}`) with curriculum tree, enrollment check (including membership gating via `hook_entity_access`), continue-URL calculation, per-course FAQs, reviews.
- Free-enroll flow: `CourseEnrollmentController::enrollFree()` creates `lms_user_course` and optional in-app notification.
- Category pages (`/categories`, `/categories/{taxonomy_term}`) backed by `course_category` vocabulary with thumbnail and featured-flag fields.
- Instructor profile page (`/instructor/{user}`).
- Workspace dashboard (`/workspace`) with KPI cards and recent orders.
- Workspace course list (`/workspace/courses`) with AJAX-capable filters and role-aware edit forms (`admin_edit` vs `manager_edit` form modes).
- Workspace content manager for CMS node bundles (slideshow, introduction, glossary, blog) and FAQ entities.
- `lms_faq` entity with three scopes (`global`, `course`, `lesson`) and category (`faq_category` vocabulary).
- `lms_article` entity (admin-only blog CRUD at `/manage/blog`) with optional AI writer widget (requires `lms_ai`).
- 11 taxonomy vocabularies supporting metadata: `course_category`, `course_level`, `course_language`, `course_tags`, `faq_category`, `blog_tags`, `badge_type`, `forum_category`, `quiz_difficulty`, `quiz_results`, `tags`.
- Pricing page (`/pricing`), about page (`/about`), contact page (`/contact`).
- Role-based permission set: `administer lms_course`, `create lms_course`, `view lms_course`, `edit lms_course`, `edit own lms_course`, `delete lms_course`.

## Out of scope

- Learning path / multi-course sequences with prerequisite enforcement between courses (no entity or routes exist for this — see D).
- Drag-and-drop curriculum reordering in the UI (the `weight` field exists on chapter and lesson, but no drag-drop JS is implemented in this module).
- Course duplication / clone functionality (not implemented).
- Bulk publish/unpublish of courses via the workspace (bulk action is implemented for CMS nodes, not for `lms_course` entities).
- Glossary and category-detail public methods (`categories()`, `categoryDetail()`, `glossary()`) are routed but their full implementation was not confirmed from available source coverage; treat as at-minimum stubbed.
- About-page team data is hardcoded placeholder names, not loaded from any entity.
- `lms_bundle` entity exists (data model only — no checkout integration, no listing route, no templates); bundle purchasing is not deliverable from this feature.

## Acceptance summary

A manager can create a course with at least title, category, price type, and one chapter containing one lesson of any supported type; the course appears on the public catalogue and detail page; a learner can enroll for free and access the lesson view. Full Given/When/Then lives in `B05-acceptance-criteria/F-001.md`.

## Content ownership / editability

| Visible content family | Business owner | Editor role | Expected edit surface | Notes |
|---|---|---|---|---|
| Course cards / catalogue | Instructors, Admins | manager, administrator | `/workspace/courses` entity forms | `admin_edit` and `manager_edit` form modes exist in config |
| Chapter & lesson curriculum | Instructors, Admins | manager, administrator | `/workspace/courses/{id}/edit` inline entity form | Inline Entity Form contrib handles chapter/lesson nesting |
| Homepage copy (hero, stats, section headings) | Platform owner | administrator | `lms_theme` settings form | 7 switchable homepage skin styles; copy defaults per style |
| Categories | Admins | administrator | Drupal taxonomy UI + workspace | `course_category` vocab; thumbnail and featured-flag per term |
| FAQs (global/course/lesson) | Content team | manager, administrator | `/workspace/content/faqs` | Scope drives display location; no publish/unpublish on FAQ |
| CMS content (slideshows, introductions, glossaries, news) | Content team | manager, administrator | `/workspace/content/{content_type}` | Bulk action available for nodes (not for custom entities) |
| Pricing page copy | Platform owner | administrator | `lms_theme` settings form | Reads from theme settings with defaults |

## Dependencies (requirement-level only)

| Type | Reference | Reason |
|---|---|---|
| Feature | F-002 | Lessons reference quizzes (lms_quiz); quiz results drive lesson completion |
| Feature | F-003 | Enrollment entity (lms_user_course) tracks course and lesson progress |
| Feature | F-004 | Paid and membership-gated courses require orders and membership checks |
| Workflow | WF-001 | Enrollment and learning workflow starts at course detail |

## Backing modules and entities

| Module | Role | Maturity |
|---|---|---|
| `lms_course` | Course entity, admin shell, workspace/admin routes, public catalogue routes, enrollment | as-built |
| `lms_chapter` | Chapter entity (thin module, delegates permissions to lms_course) | as-built |
| `lms_lesson` | Lesson entity, multi-type content, SCORM extraction, conditional form | as-built |
| `lms_faq` | FAQ entity (entity-only, no routes of its own) | as-built |
| `lms_blog` | lms_article entity (admin CRUD); LmsBlogController reads node:blog for frontend | as-built (dual-track architecture) |
| `lms_bundle` | CourseBundle entity — data model only | stub / planned |

| Entity | Key fields | Maturity |
|---|---|---|
| `lms_course` | name, field_chapters, field_price, field_price_type (free/paid/membership), field_category, field_level, field_rating, field_certificate_template | as-built |
| `lms_chapter` | name, field_lessons, weight | as-built |
| `lms_lesson` | field_type (9 values), field_video_url, field_video_file, field_scorm_package, field_pptx_attachment, field_h5p_embed, field_quiz, field_free_preview, field_drip_days, field_attachments, field_comments | as-built |
| `lms_faq` | question, field_answer, field_scope (global/course/lesson), field_course, field_category | as-built |
| `lms_article` | title, content, thumbnail, seo_title, seo_description | as-built (admin only; no uid/author field) |
| `lms_course_bundle` | name, field_courses, field_price, field_discount_percent, field_thumbnail | stub |

## Priority matrix

| Capability | Priority | Maturity |
|---|---|---|
| Course + Chapter + Lesson CRUD | Must | as-built |
| Multi-type lesson (video, text, quiz, H5P, SCORM, PDF, PPTX, Zoom, mixed) | Must | as-built |
| Public catalogue + detail + free-enroll | Must | as-built |
| Role-differentiated edit forms (admin vs manager) | Must | as-built |
| Drip-content unlock (`field_drip_days`) | Should | as-built (field + access check via hook_entity_access; UI enforcement depends on lms_progress) |
| Free-preview flag | Should | as-built |
| Lesson comments | Should | as-built (comment field on lesson; UI rendered in lms_progress lesson view) |
| FAQ (global/course/lesson) | Should | as-built |
| Glossary / category-detail public pages | Should | stub (routes declared; full method implementation unconfirmed) |
| Blog / news admin CRUD + frontend | Should | as-built (dual-track: lms_article admin + node:blog frontend) |
| AI blog writer | Could | as-built (requires lms_ai + OpenAI configured) |
| Course bundle entity + checkout | Could | stub — data model only, no checkout integration |
| Course duplication / learning paths | Could | planned — not implemented |

## Open questions

- [ ] `categories()`, `categoryDetail()`, and `glossary()` methods in `CoursePublicController` — confirm full implementation (route-declared, body not confirmed from available source read).
- [ ] The `lms_chapter` entity has no `field_course` back-reference; how the membership access check in `hook_entity_access` traverses Chapter → Course needs verification against production data.
- [ ] About-page team members are hardcoded strings — is this acceptable long-term or should a Team entity/block be created?
- [ ] `lms_bundle` — when is bundle checkout integration planned?
