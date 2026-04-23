# lms_course — Course Management Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_course` |
| Name | LMS Course |
| Layer | **Content** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `drupal:taxonomy` |

## Mô tả

Module trung tâm của EverLMS. Cung cấp entity `lms_course`, toàn bộ public-facing routes (homepage, catalog, detail), workspace admin routes, và nhiều hook quan trọng liên quan đến access control, entity type form, và theme. `lms_course` cũng đóng vai trò "shell" admin — nhiều route workspace dùng controller của module này để điều phối chức năng từ các module khác (quiz, order, user).

---

## Provided Entities

### `lms_course` (Content Entity)

| Attribute | Value |
|-----------|-------|
| Base table | `lms_course` |
| Data table | `lms_course_field_data` |
| Translatable | Yes |
| Admin permission | `administer lms_course` |
| Entity keys | id, uuid, label (name), langcode, owner (uid), published (status) |
| Form handlers | default, add, edit, manager_edit, admin_edit, delete |
| Access handler | `CourseAccessControlHandler` |
| List builder | `CourseListBuilder` |

---

## Provided Routes

| Route ID | Path | Controller | Access |
|----------|------|-----------|--------|
| `lms_course.homepage` | `/homepage` | `CoursePublicController::homepage` | `access content` |
| `lms_course.catalog` | `/courses` | `CoursePublicController::catalog` | `access content` |
| `lms_course.detail` | `/courses/{lms_course}` | `CoursePublicController::detail` | `access content` |
| `lms_course.enroll_free` | `/courses/{lms_course}/enroll-free` | `CourseEnrollmentController::enrollFree` | `access content` |
| `lms_course.category` | `/categories` | `CoursePublicController::categories` | `access content` |
| `lms_course.category_detail` | `/categories/{taxonomy_term}` | `CoursePublicController::categoryDetail` | `access content` |
| `lms_course.glossary` | `/glossary` | `CoursePublicController::glossary` | `access content` |
| `lms_course.about` | `/about` | `PageController::about` | `access content` |
| `lms_course.contact` | `/contact` | `PageController::contact` | `access content` |
| `lms_course.pricing` | `/pricing` | `PageController::pricing` | `access content` |
| `lms_course.instructor_profile` | `/instructor/{user}` | `CoursePublicController::instructorProfile` | `access content` |
| `lms_course.api_search` | `/api/courses/search` | `CoursePublicController::searchApi` | `access content` |
| `lms_course.admin_dashboard` | `/workspace` | `AdminController::dashboard` | `administer lms_course` |
| `lms_course.admin_courses` | `/workspace/courses` | `AdminController::courseList` | `administer lms_course` |
| `lms_course.admin_course_create` | `/workspace/courses/create` | `AdminController::courseCreateWizard` | `administer lms_course` |
| `lms_course.admin.course.edit` | `/workspace/courses/{lms_course}/edit` | `AdminController::courseEditForm` | `lms_course.update` |
| `lms_course.admin_quizzes` | `/workspace/quizzes` | `AdminController::quizList` | `administer lms_course` |
| `lms_course.admin_orders` | `/workspace/orders` | `AdminController::orderList` | `administer lms_course` |
| `lms_course.admin_users` | `/workspace/users` | `AdminController::userList` | `administer lms_course` |
| `lms_course.admin_reports` | `/workspace/reports` | `AdminController::reports` | `administer lms_course` |
| `lms_course.workspace_content.*` | `/workspace/content/...` | `WorkspaceContentController` | `administer lms_course` |
| `lms_course.profile` | `/profile` | `UserPortalController::profile` (lms_progress) | `_user_is_logged_in` |

---

## Provided Services

| Service ID | Class | Purpose |
|-----------|-------|---------|
| `lms_course.breadcrumb` | `LmsCourseBreadcrumbBuilder` | Breadcrumb builder priority 100 cho các route course |

---

## Provided Plugins

Không có plugin type riêng. Tuy nhiên module khai báo form mode `admin_edit` cho `lms_course`, `lms_chapter`, `lms_lesson`, và `user` qua `hook_entity_type_alter`.

---

## Hook Implementations

| Hook | Purpose |
|------|---------|
| `hook_theme()` | Khai báo 20+ templates: lms_homepage, lms_catalog, lms_course_detail, lms_course_card, admin dashboard/courses/quizzes/orders/users/content/ratings/reports, lms_profile, v.v. |
| `hook_preprocess()` | Inject `current_user_name` vào tất cả template có prefix `lms_admin_` |
| `hook_entity_type_alter()` | Set form class `admin_edit` cho course/chapter/lesson/user |
| `hook_entity_access()` | (1) Ngăn manager truy cập administrator users; (2) Kiểm tra membership access cho lms_course/lms_lesson có `field_price_type = membership` |
| `hook_entity_create_access()` | Cho phép manager tạo user nếu có `administer lms_course` |

---

## Templates & Cache Strategy

Module khai báo nhiều Twig templates. Cache:
- `lms_course_detail` render với `addCacheableDependency($entity)` + `cachePerUser()` cho membership check.
- Route `/api/courses/search` không cache mặc định (controller JSON response).

---

## Related Units

- **C03** § Content Domain — entity `lms_course`
- **C05** `01-foundation-content.md` — layer overview
- `lms_chapter.md`, `lms_lesson.md` — child entities
- `lms_progress.md` — enrollment / lesson view routes
- `lms_membership.md` — membership access check trong `hook_entity_access`

---

## Known Issues / Debt

- `lms_course` routing file kiêm nhiều routes của các domain khác (quiz create/edit, order edit, chapter/lesson admin edit) — vi phạm single-responsibility, nên tách ra các module tương ứng.
- `hook_entity_access` gọi `\Drupal::service()` trực tiếp (anti-pattern static service call) thay vì inject dependency.
- `/workspace` là "super hub" admin nhưng thiếu permission granular — mọi action đều dùng chung `administer lms_course`.
