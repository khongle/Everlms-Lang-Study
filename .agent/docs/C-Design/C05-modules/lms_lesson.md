# lms_lesson — Lesson Management Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_lesson` |
| Name | LMS Lesson |
| Layer | **Content** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `drupal:datetime`, `drupal:file`, `drupal:comment`, `lms_base`, `lms_chapter` |

## Mô tả

Module quản lý bài học (lesson) — lá cuối trong hierarchy Course → Chapter → Lesson. Hỗ trợ 7 loại nội dung: video, text, h5p, scorm, quiz, zoom, mixed. Module có hook quan trọng để auto-extract SCORM package (.zip) khi insert/update lesson.

---

## Provided Entities

### `lms_lesson` (Content Entity)

| Attribute | Value |
|-----------|-------|
| Base table | `lms_lesson` |
| Entity keys | id, uuid, label, langcode, owner, published (status) |
| Form handler | `LessonForm` |
| Access handler | `LessonAccessControlHandler` |

Key fields (từ C03): field_type (7 values), field_chapter (ref), field_video_url, field_duration, field_scorm_package (file), field_h5p_content, field_zoom_meeting_id, v.v. Tổng ~20 fields.

---

## Provided Routes

Không có routing file riêng. Routes được cung cấp qua `AdminHtmlRouteProvider` + workspace route trong `lms_course.routing.yml`:

- `/workspace/lessons/{lms_lesson}/admin-edit` — khai báo trong `lms_course.routing.yml`

---

## Provided Services

Không có services file.

---

## Provided Plugins

Không có plugin.

---

## Hook Implementations

| Hook | Purpose |
|------|---------|
| `hook_entity_access()` | Cho phép `view` lms_lesson nếu user có `access content` |
| `hook_entity_insert()` | Gọi `_lms_lesson_extract_scorm()` — auto-unzip SCORM package |
| `hook_entity_update()` | Gọi `_lms_lesson_extract_scorm()` — cập nhật SCORM khi lesson thay đổi |

### SCORM Auto-Extraction Logic

Khi lesson có `field_type = 'scorm'` và `field_scorm_package` có file:
1. Xác định real path của file zip.
2. Clear `public://scorm_extracted/{lesson_id}/`.
3. Unzip vào thư mục đó.
4. Parse `imsmanifest.xml` để tìm entry point `href`.
5. Nếu thiếu `index.html`, tạo redirect HTML tự động.

---

## Templates & Cache Strategy

Không có template riêng trong module này. Lesson view được render bởi template `lms_lesson_view` của `lms_progress`.

Lưu ý: SCORM content được serve từ `public://scorm_extracted/{id}/index.html` — không qua Drupal cache.

---

## Related Units

- **C03** § Content Domain — entity `lms_lesson`
- **C05** `01-foundation-content.md` — layer overview
- `lms_chapter.md` — parent entity
- `lms_progress.md` — lesson view / mark complete
- `lms_zoom.md` — Zoom lesson type integration

---

## Known Issues / Debt

- SCORM extraction chạy synchronously trong `hook_entity_insert/update` — với file lớn sẽ timeout request. Nên chuyển sang Drupal Queue Worker.
- Không validate `imsmanifest.xml` trước khi parse — nếu file manifest malformed sẽ silently fail.
- `_lms_lesson_extract_scorm` dùng `file_put_contents` trực tiếp thay vì Drupal File API — bypass stream wrapper.
