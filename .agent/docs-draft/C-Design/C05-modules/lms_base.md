# lms_base — Foundation Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_base` |
| Name | LMS Base |
| Layer | **Foundation** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | _(none — zero upstream custom deps)_ |

## Mô tả

`lms_base` là module nền tảng của toàn hệ thống. Nó không expose entity nghiệp vụ nào, nhưng cung cấp 9 taxonomy vocabularies dùng chung, service helper tiện ích, và Drush command seed dữ liệu demo. Tất cả module khác trong EverLMS đều phụ thuộc vào `lms_base`.

---

## Provided Entities

Không có Content Entity. Module chỉ quản lý cấu hình taxonomy được cài đặt qua install hooks / config/install.

| Vocabulary | Machine name | Mục đích |
|-----------|-------------|----------|
| Course Category | `lms_course_category` | Phân loại khóa học, multi-level |
| Course Level | `lms_course_level` | Beginner / Intermediate / Advanced |
| Course Language | `lms_course_language` | Ngôn ngữ giảng dạy |
| Course Tag | `lms_course_tag` | Tags cho SEO & search |
| Lesson Type | `lms_lesson_type` | Video / Text / H5P / SCORM / Quiz / Zoom / Mixed |
| Question Type | `lms_question_type` | 8 bundle types cho lms_question |
| Notification Type | `lms_notification_type` | 6 loại notification |
| Certificate Status | `lms_certificate_status` | Active / Revoked |
| Order Status | `lms_order_status` | Pending / Completed / Failed / Refunded |

---

## Provided Routes

Không có routing file. Module không expose route nào trực tiếp.

---

## Provided Services

| Service ID | Class | Purpose |
|-----------|-------|---------|
| `lms_base.helper` | `Drupal\lms_base\Service\LmsHelper` | Utility helper: inject `entity_type.manager` + `current_user`, dùng cho các module khác |

**drush.services.yml:** khai báo thêm Drush command provider (xem Commands bên dưới).

---

## Provided Plugins

Không có plugin type riêng.

---

## Commands (Drush)

| Class | Command | Purpose |
|-------|---------|---------|
| `LmsDemoContentCommand` | `lms:demo-content` (inferred) | Seed dữ liệu demo cho môi trường dev |

---

## Hook Implementations

Module không có `.module` file — không implement hook nào ở runtime.

---

## Templates & Cache Strategy

Không có template. Không có cache tag/context riêng.

---

## Related Units

- **C03** § Entity Groups — taxonomy foundation (9 vocabs)
- **C05** `01-foundation-content.md` — layer overview
- **D01** dev-overview — foundation layer là base cho toàn bộ dependency graph

---

## Known Issues / Debt

- `LmsHelper` hiện chỉ inject 2 service cơ bản — nếu cần thêm helper chung, cần đánh giá xem nên thêm vào đây hay tạo service riêng ở module chuyên biệt.
- Taxonomy terms được seed bằng Drush command, chưa có config/install YML hoàn chỉnh — RISK nếu deploy lên môi trường fresh không chạy drush seed.
