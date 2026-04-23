# lms_bundle — Course Bundle Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_bundle` |
| Name | LMS Bundle |
| Layer | **Commerce** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_course` |

## Mô tả

Module gói khóa học (bundle/package). Cho phép nhóm nhiều khóa học lại và bán theo gói với giá riêng. Module minimal — chỉ có entity definition `CourseBundle`.

---

## Provided Entities

### `lms_course_bundle` (Content Entity — `CourseBundle`)
| Fields | Description |
|--------|-------------|
| name | Tên gói |
| description | Mô tả |
| courses | Multi-ref → lms_course |
| price | Giá gói |
| thumbnail | Ảnh gói |
| status | Published/unpublished |

---

## Provided Routes

Không có routing file riêng. CRUD qua `AdminHtmlRouteProvider`.

---

## Provided Services

Không có services file.

---

## Provided Plugins

Không có plugin.

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

Bundle được render trong `lms-homepage` template của `lms_course` (biến `bundles`).

---

## Related Units

- **C03** § Commerce Domain — entity CourseBundle
- **C05** `03-progress-commerce.md` — layer overview
- `lms_course.md` — homepage hiển thị bundles
- `lms_order.md` — bundle purchase flow

---

## Known Issues / Debt

- Module scaffold-only level — entity definition nhưng thiếu business logic.
- Không có checkout flow riêng cho bundle — không rõ bundle purchase được handle trong `lms_order` như thế nào.
- Không có discount calculation khi buy bundle vs individual courses.
