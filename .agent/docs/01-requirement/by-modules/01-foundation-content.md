# Foundation + Content Layer

---

## `lms_base` — Core Utilities

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Shared services, taxonomy installations, base utilities, global config |
| **Entities** | None (provides shared services only) |
| **Dependencies** | `drupal:system` |
| **Services** | `lms_base.helper`, `lms_base.config` |
| **Provides** | Base entity classes, common interfaces, event definitions |

### Key Services
- `lms_base.helper` — Utility functions (date formatting, slug generation, etc.)
- `lms_base.config` — Global LMS settings (site name, default currency, email templates)

---

## `lms_course` — Course Management

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Course CRUD, listing, search, category management |
| **Entities** | `lms_course`, `lms_course_type` |
| **Dependencies** | `lms_base` |
| **Services** | `lms_course.manager`, `lms_course.search` |

### Routes
| Route | Path | Controller |
|-------|------|-----------|
| `lms_course.listing` | `/courses` | `CourseListingController` |
| `lms_course.detail` | `/courses/{slug}` | `CourseDetailController` |
| `lms_course.manage_list` | `/manage/courses` | `ManageCourseController::list` |
| `lms_course.manage_create` | `/manage/courses/create` | `ManageCourseController::create` |
| `lms_course.manage_edit` | `/manage/courses/{id}/edit` | `ManageCourseController::edit` |

---

## `lms_chapter` — Chapter Management

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Chapter CRUD, ordering within course |
| **Entities** | `lms_chapter`, `lms_chapter_type` |
| **Dependencies** | `lms_base`, `lms_course` |
| **Services** | `lms_chapter.manager` |

### Key Services
- `lms_chapter.manager` — CRUD + reorder chapters via drag-drop

---

## `lms_lesson` — Lesson Management

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Lesson CRUD, multi-type content handling, media processing |
| **Entities** | `lms_lesson`, `lms_lesson_type` |
| **Dependencies** | `lms_base`, `lms_chapter` |
| **Services** | `lms_lesson.manager`, `lms_lesson.media_handler` |

### Key Services
- `lms_lesson.manager` — CRUD with type-specific field validation
- `lms_lesson.media_handler` — Video upload processing, SCORM extraction, H5P integration

### Routes
| Route | Path | Controller |
|-------|------|-----------|
| `lms_lesson.view` | `/learn/{course}/{lesson}` | `LessonViewController` |
