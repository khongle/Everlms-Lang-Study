# C06-01 — Content API

> Module: `lms_course` (+ entity form routes cho `lms_chapter`, `lms_lesson`).
> lms_chapter và lms_lesson không có routing.yml — CRUD qua entity form system của Drupal.

---

## Route Table

| Route ID | Path | Method | Controller | Auth | Response |
|----------|------|--------|-----------|------|----------|
| `lms_course.homepage` | `/homepage` | GET | `CoursePublicController::homepage` | anon | HTML |
| `lms_course.catalog` | `/courses` | GET | `CoursePublicController::catalog` | anon | HTML |
| `lms_course.detail` | `/courses/{lms_course}` | GET | `CoursePublicController::detail` | anon | HTML |
| `lms_course.enroll_free` | `/courses/{lms_course}/enroll-free` | GET | `CourseEnrollmentController::enrollFree` | anon* | HTML/redirect |
| `lms_course.category` | `/categories` | GET | `CoursePublicController::categories` | anon | HTML |
| `lms_course.category_detail` | `/categories/{taxonomy_term}` | GET | `CoursePublicController::categoryDetail` | anon | HTML |
| `lms_course.glossary` | `/glossary` | GET | `CoursePublicController::glossary` | anon | HTML |
| `lms_course.instructor_profile` | `/instructor/{user}` | GET | `CoursePublicController::instructorProfile` | anon | HTML |
| `lms_course.api_search` | `/api/courses/search` | GET | `CoursePublicController::searchApi` | anon | JSON |
| `lms_course.about` | `/about` | GET | `PageController::about` | anon | HTML |
| `lms_course.contact` | `/contact` | GET | `PageController::contact` | anon | HTML |
| `lms_course.pricing` | `/pricing` | GET | `PageController::pricing` | anon | HTML |
| `lms_course.profile` | `/profile` | GET | `UserPortalController::profile` | login | HTML |
| `lms_course.admin_dashboard` | `/workspace` | GET | `AdminController::dashboard` | perm:administer lms_course | HTML |
| `lms_course.admin_courses` | `/workspace/courses` | GET | `AdminController::courseList` | perm:administer lms_course | HTML |
| `lms_course.admin_course_create` | `/workspace/courses/create` | GET | `AdminController::courseCreateWizard` | perm:administer lms_course | HTML |
| `lms_course.admin.course.edit` | `/workspace/courses/{lms_course}/edit` | GET | `AdminController::courseEditForm` | entity:lms_course.update | HTML/Form |
| `lms_course.admin_quizzes` | `/workspace/quizzes` | GET | `AdminController::quizList` | perm:administer lms_course | HTML |
| `lms_course.admin_quiz.create` | `/workspace/quizzes/create` | GET | `AdminController::quizCreateForm` | entity_create:lms_quiz | HTML/Form |
| `lms_course.admin_quiz.edit` | `/workspace/quizzes/{lms_quiz}/edit` | GET | `AdminController::quizEditForm` | entity:lms_quiz.update | HTML/Form |
| `lms_course.admin_orders` | `/workspace/orders` | GET | `AdminController::orderList` | perm:administer lms_course | HTML |
| `lms_course.admin_order.edit` | `/workspace/orders/{lms_order}/edit` | GET | `AdminController::orderEditForm` | perm:administer lms_course | HTML/Form |
| `lms_course.admin_users` | `/workspace/users` | GET | `AdminController::userList` | perm:administer lms_course | HTML |
| `lms_course.admin.user.create` | `/workspace/users/create` | GET | `AdminController::userCreateForm` | perm:administer lms_course | HTML/Form |
| `lms_course.admin.user.edit` | `/workspace/users/{user}/admin-edit` | GET | `AdminController::userEditForm` | entity:user.update | HTML/Form |
| `lms_course.admin.users.bulk` | `/workspace/users/bulk` | POST | `AdminController::bulkUsers` | perm:administer lms_course | JSON/redirect |
| `lms_course.admin_reports` | `/workspace/reports` | GET | `AdminController::reports` | perm:administer lms_course | HTML |
| `lms_course.admin.chapter.edit` | `/workspace/chapters/{lms_chapter}/admin-edit` | GET | `_entity_form:lms_chapter.admin_edit` | entity:lms_chapter.update | HTML/Form |
| `lms_course.admin.lesson.edit` | `/workspace/lessons/{lms_lesson}/admin-edit` | GET | `_entity_form:lms_lesson.admin_edit` | entity:lms_lesson.update | HTML/Form |
| `lms_course.workspace_content.list` | `/workspace/content/{content_type}` | GET | `WorkspaceContentController::contentList` | perm:administer lms_course | HTML |
| `lms_course.workspace_content.add` | `/workspace/content/{content_type}/add` | GET | `WorkspaceContentController::contentCreateForm` | perm:administer lms_course | HTML/Form |
| `lms_course.workspace_content.edit` | `/workspace/content/{content_type}/{node}/edit` | GET | `WorkspaceContentController::contentEditForm` | perm:administer lms_course | HTML/Form |
| `lms_course.workspace_content.view` | `/workspace/content/{content_type}/{node}/view` | GET | `WorkspaceContentController::contentView` | perm:administer lms_course | HTML |
| `lms_course.workspace_content.bulk` | `/workspace/content/bulk` | POST | `WorkspaceContentController::bulkAction` | perm:administer lms_course | JSON/redirect |
| `lms_course.workspace_faq.add` | `/workspace/content/faqs/add` | GET | `WorkspaceContentController::faqCreateForm` | perm:administer lms_course | HTML/Form |
| `lms_course.workspace_faq.edit` | `/workspace/content/faqs/{lms_faq}/edit` | GET | `WorkspaceContentController::faqEditForm` | perm:administer lms_course | HTML/Form |
| `lms_course.workspace_faq.view` | `/workspace/content/faqs/{lms_faq}/view` | GET | `WorkspaceContentController::faqView` | perm:administer lms_course | HTML |

> *`enroll_free`: permission khai báo là `access content` (anon), nhưng business logic trong controller phải kiểm tra user đã login. Đây là **RISK-001** — thiếu auth guard ở routing layer.

---

## API Chi Tiết: GET `/api/courses/search`

Đây là route duy nhất trả JSON thuần trong content domain.

**Auth:** `access content` (public)
**Method:** GET

### Query Parameters

| Param | Type | Required | Mô tả |
|-------|------|----------|-------|
| `q` | string | Không | Từ khóa tìm kiếm |
| `category` | integer | Không | ID taxonomy term |
| `level` | string | Không | Beginner / Intermediate / Advanced |
| `page` | integer | Không | Trang (paged results) |

### Response (JSON)

```json
{
  "courses": [
    {
      "id": 1,
      "title": "string",
      "slug": "string",
      "category": "string",
      "price": 0.00,
      "thumbnail": "url"
    }
  ],
  "total": 0,
  "page": 1
}
```

---

## Gap: Chapter / Lesson Routes

| Thiếu | Mô tả | Khuyến nghị |
|-------|-------|------------|
| `GET /courses/{id}/chapters` | Danh sách chapters của course | Cần route hoặc include trong course detail response |
| `GET /learn/{course}/{lesson}` | Lesson view (có trong lms_progress, không phải lms_lesson) | Đã cover bởi `lms_progress.lesson_view` |
| CRUD chapter/lesson API | Không có REST endpoint — chỉ có admin form | Nếu cần SPA builder, phải thêm JSON endpoint |

---

## Known Issues

| Ref | Mô tả |
|-----|-------|
| RISK-001 | `enroll_free` thiếu `_user_is_logged_in` ở routing layer — lộ endpoint |
| RISK-001 | `workspace_content.bulk` POST endpoint: không có CSRF token validation rõ ràng trong routing |
| DEC-001 | Input validation cho search params chưa có schema rõ ràng |

---

*Module path: `web/modules/custom/lms_course/`*
