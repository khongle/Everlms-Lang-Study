# D02 — Epic Spec: Content Layer

**Epic ID:** E-02  
**Maps to draft:** G-CONTENT  
**Features contributed to:** F-010 (Course Management), F-011 (Chapter/Lesson), F-012 (Catalog & Search), F-013 (Blog/FAQ)  
**Owner:** SA (primary) + Agent_Implementer (executes Stories); CP4 signed off by SA  
**Services affected:** Content Service (lms_course, lms_chapter, lms_lesson, lms_blog, lms_faq)  
**Dependencies:** E-01 (Foundation) — lms_base taxonomies and LmsHelper must exist  
**Estimated Duration:** 2 weeks  
**Status:** COMPLETE (as-built; some edge-case behavior stub-level — see notes)

---

## Purpose

Build the entire content layer: the Course / Chapter / Lesson entity hierarchy (with 9 lesson types), all public-facing pages (homepage with 7 skins, catalog, course detail, category pages, instructor profile, glossary, about, pricing, contact), workspace admin UI (course CRUD wizard, quiz/order/user lists, content management), plus blog (dual-track: lms_article admin + node:blog frontend) and FAQ entities. This layer is the structural backbone of the LMS — every other Epic depends on at least the Course entity.

---

## Stories

| Story ID | Title | Type | Primary source | Owning module(s) | Status | Dependencies | Priority |
|----------|-------|------|----------------|-----------------|--------|--------------|----------|
| S-02-01 | lms_course entity + access + forms | module | modules-content.md; [C05-modules/lms_course.md](../../C-Design/C05-modules/lms_course.md) | lms_course | as-built | E-01 | Must |
| S-02-02 | lms_chapter entity + access + forms | module | modules-content.md; [C05-modules/lms_chapter.md](../../C-Design/C05-modules/lms_chapter.md) | lms_chapter | as-built | S-02-01 | Must |
| S-02-03 | lms_lesson entity (9 types) + SCORM extraction | module | modules-content.md; [C05-modules/lms_lesson.md](../../C-Design/C05-modules/lms_lesson.md) | lms_lesson | as-built | S-02-02 | Must |
| S-02-04 | Public controllers — homepage, catalog, course detail, categories | page | modules-content.md; [C05-modules/lms_course.md](../../C-Design/C05-modules/lms_course.md) | lms_course | as-built | S-02-01, S-02-02, S-02-03 | Must |
| S-02-05 | Workspace admin controllers — dashboard, course CRUD, user/order/quiz lists | page | modules-content.md; [C05-modules/lms_course.md](../../C-Design/C05-modules/lms_course.md) | lms_course | as-built | S-02-01 | Must |
| S-02-06 | Workspace content management — blog/FAQ/slideshow/glossary CRUD | page | modules-content.md; [C05-modules/lms_course.md](../../C-Design/C05-modules/lms_course.md) | lms_course, lms_faq | as-built | S-02-01 | Must |
| S-02-07 | lms_blog — dual-track (lms_article admin + node:blog frontend) | module | modules-content.md; [C05-modules/lms_blog.md](../../C-Design/C05-modules/lms_blog.md) | lms_blog | as-built | S-02-01 | Should |
| S-02-08 | lms_faq — 3-scope FAQ entity (global/course/lesson) | module | modules-content.md; [C05-modules/lms_faq.md](../../C-Design/C05-modules/lms_faq.md) | lms_faq | as-built | S-02-01 | Should |

---

## Build Order Within Epic

1. **S-02-01** — lms_course entity (foundation for all content stories)
2. **S-02-02, S-02-03, S-02-08** — lms_chapter, lms_lesson, lms_faq entities (parallel; all depend on S-02-01)
3. **S-02-07** — lms_blog entity (parallel with step 2; depends on S-02-01)
4. **S-02-04** — Public controllers (depends on S-02-01 through S-02-03, S-02-08)
5. **S-02-05** — Workspace admin controllers (depends on S-02-01)
6. **S-02-06** — Workspace content management (depends on S-02-01, S-02-08; parallel with S-02-05)

---

## Story Details

### S-02-01 — lms_course entity + access + forms
**As-built.** Entity `lms_course` (base_table: `lms_course`, translatable). 28 base fields including `field_price_type` (free/paid/membership), `field_certificate_template` (→ lms_certificate_template), `field_chapters` (→ lms_chapter, unlimited), `field_rating` (added in update_10001). Handlers: CourseForm (default/add/edit/manager_edit/admin_edit), CourseAccessControlHandler, CourseListBuilder, LmsCourseBreadcrumbBuilder. 6 custom permissions (`administer lms_course`, `create lms_course`, `view lms_course`, `edit lms_course`, `edit own lms_course`, `delete lms_course`). `hook_entity_access` enforces membership requirement for membership-type courses. Update hooks: update_10002 (manager_edit form mode), update_10003 (text format grants). Source: [C05-modules/lms_course.md](../../C-Design/C05-modules/lms_course.md).

### S-02-02 — lms_chapter entity + access + forms
**As-built.** Entity `lms_chapter` (translatable). Fields: `name`, `field_description`, `field_lessons` (→ lms_lesson, unlimited), `weight`. Borrows admin_permission from lms_course (`administer lms_course`). No standalone permissions, no routes — all admin routes auto-generated via AdminHtmlRouteProvider. ChapterAccessControlHandler delegates to lms_course permissions. Note: No `field_course` back-reference on lms_chapter — parent relationship stored as `field_chapters` on Course. Source: [C05-modules/lms_chapter.md](../../C-Design/C05-modules/lms_chapter.md).

### S-02-03 — lms_lesson entity (9 lesson types) + SCORM extraction
**As-built.** Entity `lms_lesson` (translatable). `field_type` allowed values: video, text, quiz, h5p, scorm, pdf, pptx, zoom, mixed (9 types). LessonForm uses Drupal `#states` API for conditional field visibility per type. SCORM extraction: `hook_entity_insert/update` calls `_lms_lesson_extract_scorm()` (PHP ZipArchive + imsmanifest.xml auto-detection, index.html redirect generation). Fields for SCORM: `field_scorm_package` (file, zip). Field `field_quiz` (→ lms_quiz) and `field_comments` (lesson_comment type). Source: [C05-modules/lms_lesson.md](../../C-Design/C05-modules/lms_lesson.md).

### S-02-04 — Public controllers: homepage, catalog, course detail, categories
**As-built.** `CoursePublicController`: homepage (7 style variants via theme settings, aggregates 15+ data sources), catalog (paginated 12/page, multi-filter), searchApi (JSON AJAX endpoint), detail (curriculum tree, enrollment check + membership check, continue URL, FAQs, reviews, rating form), instructorProfile, categories, categoryDetail, glossary. `PageController`: about (hardcoded team data — target-state: load from entities), pricing (loads lms_membership_plan), contact. `CourseEnrollmentController::enrollFree` (creates lms_user_course, optional notification). Free enrollment route: `/courses/{lms_course}/enroll-free`. Known mock: About page team members are hardcoded placeholder names. Source: [C05-modules/lms_course.md](../../C-Design/C05-modules/lms_course.md).

### S-02-05 — Workspace admin controllers: dashboard, course CRUD, user/order/quiz lists
**As-built.** `AdminController`: dashboard (KPI cards: course count, learner count, revenue, quiz results + last 10 orders), courseList (paginated, AJAX-capable), quizList (**known mock: `pending_grading` hardcoded to 5**), orderList, userList (role/status/search; managers cannot see administrators), bulkUsers (activate/deactivate/XLSX export via shuchkin/simplexlsxgen), reports (delegates to lms_analytics if present), courseCreateWizard, courseEditForm, quizCreateForm/quizEditForm (patches inline_entity_form settings on-the-fly), userCreateForm/userEditForm, orderEditForm (ApproveOrderForm). 17 workspace routes + 5 entity-access routes. Source: [C05-modules/lms_course.md](../../C-Design/C05-modules/lms_course.md).

### S-02-06 — Workspace content management: blog/FAQ/slideshow/glossary CRUD
**As-built.** `WorkspaceContentController`: contentList (slideshows, introductions, glossaries, news/blog, lms_faq; AJAX-capable), bulkAction (publish/unpublish via direct DB update bypassing entity API), contentEditForm/contentCreateForm/contentView, faqCreateForm/faqEditForm/faqView. Routes at `/workspace/content/{content_type}`. Source: [C05-modules/lms_course.md](../../C-Design/C05-modules/lms_course.md).

### S-02-07 — lms_blog — dual-track (lms_article admin + node:blog frontend)
**As-built.** Architectural split: `lms_article` entity backs `/manage/blog` admin CRUD; `LmsBlogController` reads `node:blog` for public frontend at `/news`. Public routes (`/news`, `/news/{node}`) use `_access: TRUE`. `LmsArticleForm` has optional AI Blog Writer widget (requires lms_ai + openai provider). `lms_article` entity has no `uid` field — no author tracking on the entity itself. No `field_tags` on lms_article (tags exist on node:blog). Blog thumbnail field named `thumbnail` on lms_article vs `field_thumb` on node:blog. Source: [C05-modules/lms_blog.md](../../C-Design/C05-modules/lms_blog.md).

### S-02-08 — lms_faq — 3-scope FAQ entity
**As-built.** Entity `lms_faq`. No status/published field. `field_scope`: global (homepage), course (detail page filtered by `field_course`), lesson (lesson view). No custom routes, no custom access handler — all via AdminHtmlRouteProvider + core default access. FAQ workspace CRUD handled by lms_course's WorkspaceContentController. Source: [C05-modules/lms_faq.md](../../C-Design/C05-modules/lms_faq.md).

---

## Affected Modules

| Module | Role in Epic |
|--------|-------------|
| lms_course | Primary: entity, all public + admin controllers, 30+ theme hooks, permissions, breadcrumb |
| lms_chapter | Entity definition; borrows permissions from lms_course |
| lms_lesson | Entity definition + SCORM extraction; 9 lesson types |
| lms_blog | Dual-track blog: lms_article admin + node:blog frontend |
| lms_faq | Minimal entity-only FAQ with 3-scope field |

---

## Acceptance Criteria (from B05)

| AC ID | Scenario | Feature | Contributing Story(ies) |
|-------|----------|---------|-------------------------|
| AC-010 | Catalog page lists courses with search/filter/pagination | F-012 | S-02-04 |
| AC-011 | Course detail renders curriculum tree and enrollment button | F-010 | S-02-04 |
| AC-012 | Course CRUD wizard creates course with chapters/lessons | F-010 | S-02-05 |
| AC-013 | SCORM zip auto-extracted on lesson save | F-011 | S-02-03 |
| AC-014 | Homepage renders with 7 switchable skin styles | F-003 | S-02-04 |
| AC-015 | FAQ renders on homepage (global scope) and course detail (course scope) | F-013 | S-02-08 |
| AC-016 | Blog frontend at /news lists node:blog content | F-013 | S-02-07 |
| AC-017 | Workspace admin dashboard shows live KPIs | F-010 | S-02-05 |

---

## Integration Points (from B06)

| INT ID | Integration | Contributing Story(ies) | Notes |
|--------|------------|-------------------------|-------|
| INT-010 | lms_course.entity ← lms_certificate_template reference | S-02-01 | field_certificate_template; consumed by E-06 |
| INT-011 | lms_lesson.field_quiz ← lms_quiz reference | S-02-03 | Consumed by E-03 (Assessment) |
| INT-012 | lms_lesson.field_type=zoom ← Zoom lesson integration | S-02-03 | Consumed by lms_zoom |
| INT-013 | WorkspaceContentController bulk publish → direct DB (bypasses entity API) | S-02-06 | No entity hooks fired on bulk; cache may be stale |

---

## Test Requirements

- **Unit + integration tests per Story:** coverage >= 70% per Story
- **Cross-Story integration tests:** Course→Chapter→Lesson hierarchy creation and retrieval; catalog search API returns correct JSON; SCORM extraction round-trip; enrollment check via membership gateway
- **Cross-Story Integration Test Owner:** `S-02-01` (Course entity is the anchor for all cross-entity traversal)
- **Acceptance tests:** AC-010 through AC-017

---

## Feature Flag Plan

| Flag ID | Story | Description | Risk Level | Rollout Strategy |
|---------|-------|-------------|-----------|-----------------|
| FF_FEAT_AI_BLOG_WRITER | S-02-07 | Enable AI Blog Writer widget in lms_article admin form | Medium | 0% default; enable per-admin |

---

## CP4 Readiness Criteria

- [x] Every Story in this Epic has status: DONE (all as-built)
- [x] G1-G4 passed for every Story (per draft G-CONTENT exit criteria)
- [x] Cross-Story integration tests within Epic passing
- [x] D07 code-map delta complete for all files touched in this Epic
- [x] E05 traceability complete for every contributed Feature
- [ ] E07 epic retrospective written (E07-retrospectives/) — pending
- [ ] Feature flags registered for FF_FEAT_AI_BLOG_WRITER
- [ ] Open G4 issues logged in E02-issues/ (pending_grading mock=5 in quizList; hardcoded About team; bulk-publish bypasses entity API)
- [x] Every Story that touched code/config/UI lists relevant D00-conventions/ files
