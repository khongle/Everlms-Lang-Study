# D03 — Story Spec: LMS Course

**Story ID:** lms_course
**Story type:** module
**Title:** Course entity, public learner pages, /workspace admin shell, breadcrumb, and cross-entity hooks
**Primary source:** `../../C-Design/C05-modules/lms_course.md`
**Owning module(s):** lms_course
**Service(s):** `lms_course.breadcrumb`
**Epic:** E-02 (Content Layer)
**Contributes to Feature(s):** F-001 (Course Management), F-007 (User Management — workspace user CRUD)
**Design source(s):** `../../C-Design/C05-modules/lms_course.md`
**Round:** N/A
**Driver:** SA directive — core content module and admin shell
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** pre-2026-04-15 (pre-framework)
**Tasks:** see `../D04-tasks/` filtered by `Story ID: lms_course`

---

## Session Strategy

**Estimated session count:** 4+ (large — 5 controllers, 27 templates, 27 routes, 24 entity fields, 3 update hooks)
**Splitting strategy:**
- S1: `Course` entity + `CourseForm` + `CourseAccessControlHandler` + `CourseListBuilder` + `LmsCourseBreadcrumbBuilder` + permissions + install hooks (U-005)
- S2: `AdminController` + `WorkspaceContentController` + `ApproveOrderForm` + workspace templates (U-008)
- S3: `CoursePublicController` (homepage, catalog, detail, categories, glossary, instructor) + `PageController` + public templates (U-009, U-010, U-011, U-012)
- S4: `CourseEnrollmentController` + `lms_course.module` hooks (hook_entity_type_alter, hook_entity_access, hook_entity_create_access) + routing.yml (cross-entity routes)

---

## Scope (from primary source)

The largest single module in EverLMS — simultaneously the course entity owner, the public learner UI, and the `/workspace` admin shell:

1. **`lms_course` entity** — ContentEntityBase + EntityOwnerTrait; 24 base fields (name, description, thumbnail, banner, chapters ref, category/tags/level/language taxonomy refs, instructor refs, price, price_type, duration, rating, bestseller, featured, certificate_template, status, uid, created, changed); 4 form modes (default, add, edit, manager_edit, admin_edit); `CourseAccessControlHandler`.
2. **Public routes (12)** — homepage (`/homepage`), catalog (`/courses`), detail (`/courses/{lms_course}`), categories (`/categories`), category detail, glossary, instructor profile, about, contact, pricing, free enrollment, JSON search API.
3. **Workspace/Admin routes (14)** — `/workspace` dashboard; course/quiz/order/user CRUD; content management (slideshows, introductions, glossaries, blog, FAQs); chapter/lesson admin-edit (cross-entity, `_entity_form`).
4. **API route (1)** — `/api/courses/search` JSON endpoint.
5. **Hook implementations** — `hook_theme` (30+ templates), `hook_preprocess` (admin template variable injection), `hook_entity_type_alter` (registers admin_edit/manager_edit form modes on lms_course/lms_chapter/lms_lesson/user), `hook_entity_access` (blocks manager↔admin; enforces membership-gated access), `hook_entity_create_access` (managers can create users).
6. **Breadcrumb builder** — `LmsCourseBreadcrumbBuilder` (priority=100); applies to all `lms_course.*` and `entity.lms_course.canonical` routes.
7. **27 Twig templates** — public (homepage, catalog, course-detail, card, categories, instructor, glossary, about, contact, pricing, profile) + admin (dashboard, courses, quizzes, orders, users, reports, content, ratings, entity form/view wrappers, sidebar, user form, course create wizard).
8. **Install update hooks** — `lms_course_update_10001` (adds `field_rating`), `lms_course_update_10002` (creates manager_edit form mode + form display), `lms_course_update_10003` (grants text format permissions to manager role).

---

## Source Documents

| Source | Why loaded |
|--------|-----------|
| `../../C-Design/C05-modules/lms_course.md` | Owning module design (as-built) |
| `../../C-Design/C05-modules/lms_chapter.md` | Child entity (workspace routes hosted here) |
| `../../C-Design/C05-modules/lms_lesson.md` | Child entity (workspace routes hosted here) |
| `../../C-Design/C05-modules/lms_faq.md` | FAQ CRUD routes hosted in lms_course |
| `../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md` | Course CRUD, catalog, detail AC |
| `../../B-Requirements/B05-acceptance-criteria/AC-07-user-management.md` | User list, CRUD, role-based access AC |

---

## Requirements

| Requirement | Source | Type |
|-------------|--------|------|
| Course CRUD (create, list, edit, delete) functional in workspace | AC-01-course-management.md | Acceptance |
| Public catalog with search/filter/pagination | AC-01-course-management.md | Acceptance |
| Course detail page shows curriculum, enrollment status, FAQs, reviews | AC-01-course-management.md | Acceptance |
| Homepage renders featured courses, categories, memberships, news | AC-01-course-management.md | Acceptance |
| Free enrollment via single-click | AC-01-course-management.md | Acceptance |
| Membership-gated courses blocked for unenrolled non-members | AC-01-course-management.md | Acceptance |
| Workspace accessible only with administer lms_course | Internal design | Non-Functional |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_course/lms_course.info.yml` | Create | deps: lms_base, drupal:taxonomy | U-005 | pre-2026-04-15 |
| `web/modules/custom/lms_course/lms_course.routing.yml` | Create | 27 routes: Public/Workspace/Admin/API | U-005, U-008 | pre-2026-04-15 |
| `web/modules/custom/lms_course/lms_course.services.yml` | Create | lms_course.breadcrumb service | U-005 | pre-2026-04-15 |
| `web/modules/custom/lms_course/lms_course.permissions.yml` | Create | 6 permissions | U-005 | pre-2026-04-15 |
| `web/modules/custom/lms_course/lms_course.module` | Create | hook_theme (30+), hook_preprocess, hook_entity_type_alter, hook_entity_access, hook_entity_create_access | U-005, U-008 | pre-2026-04-15 |
| `web/modules/custom/lms_course/lms_course.install` | Create | 3 update hooks | U-005 | pre-2026-04-15 |
| `web/modules/custom/lms_course/src/Entity/Course.php` | Create | lms_course ContentEntity; 24 base fields; EntityOwnerTrait | U-005 | pre-2026-04-15 |
| `web/modules/custom/lms_course/src/Access/CourseAccessControlHandler.php` | Create | view/update/delete/create permission checks | U-005 | pre-2026-04-15 |
| `web/modules/custom/lms_course/src/CourseListBuilder.php` | Create | Name/Category/Level/Price/Status columns | U-005 | pre-2026-04-15 |
| `web/modules/custom/lms_course/src/Form/CourseForm.php` | Create | ContentEntityForm (5 form modes) | U-005 | pre-2026-04-15 |
| `web/modules/custom/lms_course/src/Form/ApproveOrderForm.php` | Create | Order review form (status + receipt) | U-008 | pre-2026-04-15 |
| `web/modules/custom/lms_course/src/Breadcrumb/LmsCourseBreadcrumbBuilder.php` | Create | Priority 100 breadcrumb builder | U-005 | pre-2026-04-15 |
| `web/modules/custom/lms_course/src/Controller/AdminController.php` | Create | dashboard, courseList, quizList, orderList, userList, bulkUsers, reports, CRUD methods | U-008 | pre-2026-04-15 |
| `web/modules/custom/lms_course/src/Controller/CoursePublicController.php` | Create | homepage, catalog, detail, categories, glossary, instructor, searchApi | U-009, U-010, U-011, U-012 | pre-2026-04-15 |
| `web/modules/custom/lms_course/src/Controller/CourseEnrollmentController.php` | Create | enrollFree() | U-008 | pre-2026-04-15 |
| `web/modules/custom/lms_course/src/Controller/PageController.php` | Create | about, contact, pricing | U-008 | pre-2026-04-15 |
| `web/modules/custom/lms_course/src/Controller/WorkspaceContentController.php` | Create | node + FAQ CRUD in workspace; bulkAction; preview | U-008 | pre-2026-04-15 |
| `web/modules/custom/lms_course/templates/*.html.twig` | Create | 27 Twig templates (public + admin + shared) | U-008, U-009, U-011, U-012 | pre-2026-04-15 |

---

## Dependencies

| Dependency | Type | Source |
|------------|------|--------|
| `lms_base` | Intra-Epic | LmsHelper service; taxonomy vocabs |
| `drupal:taxonomy` | External (Drupal core) | course_category, course_tags, course_level, course_language |
| `lms_chapter` | Cross-Story | entity_reference field_chapters; workspace admin-edit route |
| `lms_lesson` | Cross-Story | workspace admin-edit route |
| `lms_quiz` | Cross-Epic (runtime) | AdminController creates/edits quizzes |
| `lms_order` | Cross-Epic (runtime) | AdminController lists/edits orders; ApproveOrderForm |
| `lms_progress` | Cross-Epic (runtime) | /profile route delegates to UserPortalController |
| `lms_rating` | Cross-Epic (runtime) | lms_admin_ratings template rendered for workspace ratings |
| `lms_analytics` | Cross-Epic (runtime, optional) | AdminController::reports() delegates if present |
| `shuchkin/simplexlsxgen` | External (optional) | bulkUsers() XLSX export |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|------------|-----------|---------|
| _admin_route: TRUE on workspace routes | All /workspace/* routes | Verified in routing.yml |
| entity:lms_course param upcasting | /courses/{lms_course} | Confirmed in routing.yml |
| administer lms_course as shared admin permission | lms_chapter, lms_lesson, lms_faq borrow this | Confirmed in info.yml deps |

---

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission |
|---------------------|----------------|-----------------|-----------|-------------------|
| `/homepage` | Featured courses, categories, memberships, news, FAQs, stats | lms_course, taxonomy_term, lms_membership_plan, node:blog, lms_faq, lms_rating, lms_theme settings | `/workspace/courses` + theme settings | administer lms_course |
| `/courses` | Paginated course catalog (12/page) | lms_course entities | `/workspace/courses` | administer lms_course |
| `/courses/{lms_course}` | Course detail: chapters, lessons, instructors, FAQs, reviews | lms_course, lms_chapter, lms_lesson, lms_faq, lms_rating | `/workspace/courses/{id}/edit` | administer lms_course / edit lms_course |
| `/about` | Team members, branding | Hardcoded PHP (PageController) | None — hardcoded | n/a |
| `/pricing` | Membership plan pricing | lms_membership_plan entities | `/manage/membership-plans` (lms_membership) | administer lms_course |
| `/workspace` | KPI dashboard: course count, learner count, revenue, recent orders | Live DB queries | n/a (read-only dashboard) | administer lms_course |

**Hardcoded content exceptions:** `PageController::about()` team member names (John Smith, Sarah Johnson, etc.) are hardcoded PHP strings — not entity-driven. This is known debt; see `../../C-Design/C05-modules/lms_course.md` §Known Issues.

---

## Feature Flags Introduced or Read

None.

---

## What Was Built (from facts base)

All code is **as-built**. Status confirmed by `modules-content.md` and `modules-foundation.md`:

- All 5 controllers fully implemented (no stubs, no TODOs).
- `AdminController::quizList()` has one known mock: `pending_grading` hardcoded to `5`.
- `CoursePublicController::categories()` (lines 1624–1658): loads all `course_category` terms, filters to those with `count > 0` published courses (entity query with `field_category = term->id()` + `status = TRUE`), loads `field_category_thumbnail` image per term, passes as `#categories` array. No `field_icon` storage config exists; the `hasField()` guard means `icon_url` is always empty. Confirmed fully read.
- `CoursePublicController::categoryDetail()` (lines 1663–1683): queries `lms_course` by `field_category = taxonomy_term->id()`, `status = TRUE`, `sort created DESC`, paginated 12/page; renders via `lms_catalog` theme hook (reuses catalog template). Confirmed fully read.
- `CoursePublicController::glossary()` (lines 1885–1966): loads all published `node:glossary` nodes, groups by first letter (A–Z + `#` bucket), sorts each group by title ASC, passes `#terms` + `#alphabet` to `lms_glossary` template. Optional node fields read: `field_glossary_category` (taxonomy), `field_related_terms` (node references), `body` (primary) / `field_definition` (fallback). Client-side JS search confirmed in `lms-glossary.html.twig`. Confirmed fully read.
- 27 templates all present; AJAX partials (`lms-admin-courses-table`, `lms-admin-users-table`, `lms-admin-content-table`) confirmed.
- 3 update hooks implemented and tested.
- `hook_entity_access` uses `\Drupal::service()` static call (anti-pattern) — tracked as debt.

---

## Tasks (draft unit references)

| Task / Draft ID | Description | Status |
|----------------|-------------|--------|
| U-005 | Course entity + handlers (entity, form, access, breadcrumb, permissions) | DONE |
| U-008 | Course CRUD controller + templates (AdminController, workspace, enrollment) | DONE |
| U-009 | Catalog page + search/filter (CoursePublicController::catalog, searchApi) | DONE |
| U-010 | Category management (CoursePublicController::categories, categoryDetail) | DONE |
| U-011 | Course detail page (CoursePublicController::detail) | DONE |
| U-012 | Homepage controller (CoursePublicController::homepage) | DONE |

---

## Acceptance Link

See [`../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md`](../../B-Requirements/B05-acceptance-criteria/AC-01-course-management.md).

---

## DONE Evidence

| Check | Result |
|-------|--------|
| lms_course entity installs and CRUD functional | PASS — entity verified in facts |
| /workspace dashboard renders KPI cards + recent orders | PASS — AdminController::dashboard() verified |
| /courses catalog with pagination and filters | PASS — CoursePublicController::catalog() + searchApi() verified |
| /courses/{id} detail with curriculum, FAQs, reviews | PASS — CoursePublicController::detail() verified |
| Free enrollment creates lms_user_course | PASS — CourseEnrollmentController::enrollFree() verified |
| Membership-gated courses blocked via hook_entity_access | PASS — hook logic verified in facts |
| 27 templates present | PASS — templates directory confirmed |
| Update hooks 10001/10002/10003 applied | PASS — install file verified |

---

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | All 27 routes resolve; entity installs; templates render | 1/3 | pre-2026-04-15 |
| G2 Lint | PASS | No violations reported | 1/3 | pre-2026-04-15 |
| G3 Tests | PASS | Course CRUD cycle verified; catalog filter returns correct results | 1/3 | pre-2026-04-15 |
| G4 Adversarial | PASS | Static service call debt noted; hardcoded About page noted; pending_grading mock noted | 1/3 | pre-2026-04-15 |

---

## Notes

- lms_course acts as the **admin shell** for the entire `/workspace` UI, hosting routes for quizzes, orders, users, chapters, lessons, FAQs, and content types — a single-responsibility violation documented as known debt.
- `hook_entity_access` calls `\Drupal::service()` directly — should be refactored to a proper event subscriber. Tracked in [`../../E-Quality/E02-issues/`](../../E-Quality/E02-issues/).
- All `/workspace` actions gate on a single `administer lms_course` permission; no granular per-feature workspace permissions exist.
