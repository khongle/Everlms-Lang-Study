---
module: lms_course
service: course
route-groups: [Public, Manage, Admin, API]
status: DONE
documentation-mode: as-built
---

# C05 — Module: LMS Course

**Directory:** `web/modules/custom/lms_course/`
**Responsibility:** Core workspace/admin hub and public course-facing module — owns the `lms_course` entity, all public learner pages (homepage, catalog, detail, categories, glossary, marketing pages), the entire `/workspace/*` custom admin UI shell, breadcrumb builder, and cross-entity hooks for access control and form registration.

---

## Route Bindings

| Route Group | Prefix | Controller | Paths |
|-------------|--------|------------|-------|
| Public | `/` | `CoursePublicController` | `/homepage`, `/courses`, `/courses/{lms_course}`, `/categories`, `/categories/{taxonomy_term}`, `/glossary`, `/instructor/{user}` |
| Public | `/` | `PageController` | `/about`, `/contact`, `/pricing` |
| Public | `/courses/{lms_course}/` | `CourseEnrollmentController` | `/courses/{lms_course}/enroll-free` |
| App (Learn) | `/profile` | `UserPortalController` (lms_progress) | `/profile` — route declared here, controller in lms_progress |
| Workspace | `/workspace` | `AdminController` | `/workspace`, `/workspace/courses/*`, `/workspace/quizzes/*`, `/workspace/orders/*`, `/workspace/users/*`, `/workspace/reports` |
| Workspace | `/workspace/content/` | `WorkspaceContentController` | `/workspace/content/{content_type}`, add/edit/view/bulk, plus `/workspace/content/faqs/*` |
| Workspace | `/workspace/chapters/`, `/workspace/lessons/` | entity form (_entity_form) | `/workspace/chapters/{lms_chapter}/admin-edit`, `/workspace/lessons/{lms_lesson}/admin-edit` |
| API | `/api/courses/` | `CoursePublicController` | `/api/courses/search` (JSON) |

**Total routes in lms_course.routing.yml: 27** (12 Public/App + 14 Workspace/Admin + 1 API)

**Controllers: 5** — `AdminController`, `CoursePublicController`, `CourseEnrollmentController`, `PageController`, `WorkspaceContentController`

---

## Public Interface

| Export | Type | Description |
|--------|------|-------------|
| `lms_course.breadcrumb` | Service (`LmsCourseBreadcrumbBuilder`) | Breadcrumb builder (priority=100); applies to all routes starting with `lms_course.` or `entity.lms_course.canonical`; builds Home > Courses > [term/course name] |
| `CourseAccessControlHandler` | Access Handler | view: `view lms_course`; update: `edit lms_course` or own; delete: `delete lms_course`; create: `create lms_course` |
| `administer lms_course` | Permission | Gateway permission used by lms_chapter, lms_lesson, lms_faq as their admin_permission |

---

## Internal Structure

```
web/modules/custom/lms_course/
+-- lms_course.info.yml              # dependencies: lms_base, drupal:taxonomy
+-- lms_course.routing.yml           # 27 routes across Public, Workspace/Admin, API groups
+-- lms_course.services.yml          # lms_course.breadcrumb service
+-- lms_course.permissions.yml       # 6 permissions (administer, create, view, edit, edit own, delete)
+-- lms_course.module                # hook_theme (30+ templates), hook_preprocess, hook_entity_type_alter, hook_entity_access, hook_entity_create_access
+-- lms_course.install               # update hooks: 10001 (field_rating), 10002 (manager_edit form mode), 10003 (text format perms)
+-- src/
|   +-- Entity/
|   |   +-- Course.php               # lms_course ContentEntity; translatable; EntityOwnerTrait; 24 base fields
|   +-- Controller/
|   |   +-- AdminController.php      # /workspace hub: dashboard + CRUD for courses, quizzes, orders, users, reports
|   |   +-- CoursePublicController.php  # homepage, catalog, detail, categories, glossary, instructor, searchApi
|   |   +-- CourseEnrollmentController.php  # enrollFree
|   |   +-- PageController.php       # about, contact, pricing (static marketing pages)
|   |   +-- WorkspaceContentController.php  # node + FAQ CRUD in workspace
|   +-- Form/
|   |   +-- CourseForm.php           # ContentEntityForm; default/add/edit/manager_edit/admin_edit
|   |   +-- ApproveOrderForm.php     # Order review form (approve/reject with status + receipt)
|   +-- Access/
|   |   +-- CourseAccessControlHandler.php
|   +-- Breadcrumb/
|   |   +-- LmsCourseBreadcrumbBuilder.php
|   +-- CourseListBuilder.php        # Columns: name, category, level, price, status
+-- templates/                       # 27 Twig templates (see table below)
```

**No `config/install/` or `config/schema/` directories.**

### Key Templates

| Template File | Theme Hook | Route / Purpose |
|--------------|-----------|-----------------|
| `lms-homepage.html.twig` | `lms_homepage` | `/homepage` — delegates to `lms_theme` style variants |
| `lms-catalog.html.twig` | `lms_catalog` | `/courses` — paginated with filters |
| `lms-course-detail.html.twig` | `lms_course_detail` | `/courses/{lms_course}` |
| `lms-course-card.html.twig` | `lms_course_card` | Reusable sub-component |
| `lms-categories.html.twig` | `lms_categories` | `/categories` |
| `lms-instructor-profile.html.twig` | `lms_instructor_profile` | `/instructor/{user}` |
| `lms-glossary.html.twig` | `lms_glossary` | `/glossary` |
| `lms-about.html.twig` | `lms_about` | `/about` |
| `lms-contact.html.twig` | `lms_contact` | `/contact` |
| `lms-pricing.html.twig` | `lms_pricing` | `/pricing` |
| `lms-profile.html.twig` | `lms_profile` | `/profile` |
| `lms-admin-dashboard.html.twig` | `lms_admin_dashboard` | `/workspace` |
| `lms-admin-courses.html.twig` | `lms_admin_courses` | `/workspace/courses` |
| `lms-admin-courses-table.html.twig` | `lms_admin_courses_table` | AJAX partial for course table |
| `lms-admin-course-create.html.twig` | `lms_admin_course_create` | `/workspace/courses/create` |
| `lms-admin-quizzes.html.twig` | `lms_admin_quizzes` | `/workspace/quizzes` |
| `lms-admin-orders.html.twig` | `lms_admin_orders` | `/workspace/orders` |
| `lms-admin-users.html.twig` | `lms_admin_users` | `/workspace/users` |
| `lms-admin-users-table.html.twig` | `lms_admin_users_table` | AJAX partial for users table |
| `lms-admin-user-form.html.twig` | `lms_admin_user_form` | User create/edit form wrapper |
| `lms-admin-content.html.twig` | `lms_admin_content` | `/workspace/content/{type}` |
| `lms-admin-content-table.html.twig` | `lms_admin_content_table` | AJAX partial for content table |
| `lms-admin-ratings.html.twig` | `lms_admin_ratings` | `/workspace/ratings` (rendered by lms_rating controller) |
| `lms-admin-reports.html.twig` | `lms_admin_reports_v2` | `/workspace/reports` |
| `lms-admin-entity-form.html.twig` | `lms_admin_entity_form` | Generic entity form wrapper (shared across workspace forms) |
| `lms-admin-entity-view.html.twig` | `lms_admin_entity_view` | Generic entity view wrapper |
| `lms-admin-sidebar.html.twig` | _(sub-include)_ | Shared sidebar navigation in all admin templates |

---

## Entity: lms_course

| Property | Value |
|----------|-------|
| Type | ContentEntityBase + EntityOwnerTrait |
| Base table | `lms_course` |
| Data table | `lms_course_field_data` |
| Translatable | TRUE |
| Admin permission | `administer lms_course` |
| Entity keys | id, uuid, label=name, langcode, owner=uid, published=status |
| Canonical URL | `/manage/courses/{lms_course}` |
| Collection | `/admin/structure/lms_course` |

**Base Fields (24 total):**

| Field | Type | Notes |
|-------|------|-------|
| name | string | required, max_length=255, translatable |
| field_description | text_long | translatable |
| field_short_description | text_long | translatable |
| field_what_learn | text_long | translatable |
| field_prerequisites | text_long | translatable |
| field_who_for | text_long | translatable |
| field_thumbnail | image | `courses/thumbnails`, alt_field=TRUE |
| field_banner | image | `courses/banners`, alt_field=TRUE |
| field_chapters | entity_reference→lms_chapter | UNLIMITED; forward ref (parent stores children) |
| field_category | entity_reference→taxonomy_term | bundle: course_category |
| field_tags | entity_reference→taxonomy_term | UNLIMITED; bundle: course_tags |
| field_level | entity_reference→taxonomy_term | bundle: course_level |
| field_language | entity_reference→taxonomy_term | bundle: course_language |
| field_instructor | entity_reference→user | UNLIMITED |
| field_price | decimal | precision=10, scale=2, default=0 |
| field_price_type | list_string | allowed: free, paid, membership; default=free |
| field_duration | integer | unit=minutes (auto-calculated); default=0 |
| field_rating | decimal | precision=3, scale=1, default=5.0 (added via update_10001) |
| field_bestseller | boolean | default=FALSE |
| field_featured | boolean | default=FALSE |
| field_certificate_template | entity_reference→lms_certificate_template | 1 |
| status | boolean | default=TRUE |
| created / changed | timestamps | — |
| uid | entity_reference→user | owner (EntityOwnerTrait) |

**Form modes:** default, add, edit, manager_edit (registered via update_10002), admin_edit (registered via hook_entity_type_alter in lms_course.module).

---

## Hook Implementations

| Hook | Behavior |
|------|----------|
| `hook_theme()` | Registers 30+ theme templates covering all public, admin, profile, and card templates |
| `hook_preprocess()` | Injects `current_user_name`, `site_logo_url`, `site_name` into all `lms_admin_*` templates |
| `hook_entity_type_alter()` | Registers `admin_edit` form class on lms_course, lms_chapter, lms_lesson; `admin/manager` form classes on user |
| `hook_entity_access()` | (1) Blocks managers from viewing administrators; (2) Enforces membership requirement: if course/lesson has `field_price_type=membership`, unenrolled users without active membership are denied view |
| `hook_entity_create_access()` | Allows managers with `administer lms_course` permission to create users |

---

## Permissions

| Machine Name | Title |
|-------------|-------|
| `administer lms_course` | Full admin access (also used by lms_chapter, lms_lesson, lms_faq) |
| `create lms_course` | Create courses |
| `view lms_course` | View courses |
| `edit lms_course` | Edit any course |
| `edit own lms_course` | Edit own courses |
| `delete lms_course` | Delete courses |

---

## Dependencies

| Dependency | Type | Why |
|-----------|------|-----|
| `lms_base` | Internal | `LmsHelper` service; base utilities |
| `drupal:taxonomy` | Core | `course_category`, `course_tags`, `course_level`, `course_language` vocabularies |
| `lms_quiz` | Internal (runtime) | AdminController creates/edits lms_quiz entities via workspace routes |
| `lms_order` | Internal (runtime) | AdminController lists/edits orders; `ApproveOrderForm` |
| `lms_progress` | Internal (runtime) | `/profile` route delegates to `UserPortalController` from lms_progress |
| `lms_rating` | Internal (runtime) | `lms_admin_ratings` template rendered for workspace ratings section |
| `lms_analytics` | Internal (runtime, optional) | `AdminController::reports()` delegates to lms_analytics services if present |
| `shuchkin/simplexlsxgen` | External (optional) | `bulkUsers()` XLSX export; gracefully skipped if not installed |

---

## Known Issues / Debt

- **Single-responsibility violation (stub):** `lms_course.routing.yml` declares routes for quiz CRUD (`/workspace/quizzes/*`), chapter/lesson admin-edit, order edit, and user management — all belong to their respective modules. The lms_course module acts as an admin shell that crosses domain boundaries.
- **Static service call anti-pattern:** `hook_entity_access()` calls `\Drupal::service()` directly instead of injecting dependencies. Should be refactored to a proper event subscriber or service.
- **Coarse permissions:** All `/workspace` actions gate on a single `administer lms_course` permission. No granular per-feature workspace permissions exist.
- **Hardcoded mock data:** `AdminController::quizList()` has `pending_grading` hardcoded to `5`. `PageController::about()` has team member names hardcoded (John Smith, Sarah Johnson, etc.) — not entity-driven.
- **Partial controller confirmation:** `categories()`, `categoryDetail()`, and `glossary()` methods in `CoursePublicController` are declared in routing.yml. Their implementation is confirmed present (the controller file is 1969 lines; these methods appear in the unread tail at line 1621+) but detailed behavior is «unverified».

---

## Cross-Links

- Entity detail: [lms_course entity](../C04-entities/01-content-entities.md)
- Child modules: [lms_chapter](./lms_chapter.md), [lms_lesson](./lms_lesson.md), [lms_faq](./lms_faq.md)
- Route group authority: [C07 Sitemap](../C07-sitemap.md)
