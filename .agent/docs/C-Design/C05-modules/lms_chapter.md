# lms_chapter — Chapter Management Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_chapter` |
| Name | LMS Chapter |
| Layer | **Content** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_course` |

## Mô tả

Module quản lý chương (chapter) trong khóa học. `lms_chapter` là entity trung gian trong hierarchy Course → Chapter → Lesson. Module cung cấp entity, form, và access handler — không có routing hay service file riêng.

---

## Provided Entities

### `lms_chapter` (Content Entity)

| Attribute | Value |
|-----------|-------|
| Base table | `lms_chapter` |
| Entity keys | id, uuid, label, langcode |
| Form handler | `ChapterForm` |
| Access handler | `ChapterAccessControlHandler` |

Fields (từ C03): title, description, field_course (ref → lms_course), field_lessons (ref → lms_lesson), weight/order.

---

## Provided Routes

Không có routing file riêng. Các route CRUD được cung cấp qua `AdminHtmlRouteProvider` tiêu chuẩn Drupal và qua `lms_course` module (workspace routes):

- `/workspace/chapters/{lms_chapter}/admin-edit` — được khai báo trong `lms_course.routing.yml`

---

## Provided Services

Không có services file.

---

## Provided Plugins

Không có plugin.

---

## Hook Implementations

Không có `.module` file — không implement hook nào.

---

## Templates & Cache Strategy

Không có template riêng. Chapter được render thông qua template `lms_lesson_view` của `lms_progress`.

---

## Related Units

- **C03** § Content Domain — entity `lms_chapter`
- **C05** `01-foundation-content.md` — layer overview
- `lms_course.md` — parent entity + workspace routes
- `lms_lesson.md` — child entity

---

## Known Issues / Debt

- Module rất minimal — chỉ có 3 PHP class. Logic nghiệp vụ chapter (ordering, prerequisite) chưa được implement.
- Không có service layer riêng — các thao tác chapter hiện nay được xử lý trực tiếp qua entity API trong các controller của `lms_course`.
