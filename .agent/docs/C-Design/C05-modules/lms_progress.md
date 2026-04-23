# lms_progress — Learning Progress & User Portal Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_progress` |
| Name | LMS Progress |
| Layer | **Progress** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_course`, `lms_chapter`, `lms_lesson` |

## Mô tả

Module theo dõi tiến độ học tập của user (enrollment, lesson completion, dashboard) và cung cấp user portal routes. Module cũng chứa comment form alter logic phức tạp cho lesson comment. Đây là module có nhiều hook và business logic nhất trong Progress layer.

---

## Provided Entities

### `lms_user_course` (Content Entity)
Enrollment + tiến độ khóa học: uid, course ref, percent_complete, enrolled_at, completed_at.

### `lms_user_lesson` (Content Entity)
Tiến độ bài học: uid, lesson ref, completed (bool), completed_at, video_position.

> Lưu ý: `lms_user_chapter` được đề cập trong C03 nhưng không thấy trong `src/Entity/` — có thể là derived từ lesson progress.

---

## Provided Routes

| Route ID | Path | Controller | Access |
|----------|------|-----------|--------|
| `lms_progress.my_courses` | `/my-courses` | `UserPortalController::myCourses` | logged in |
| `lms_progress.lesson_view` | `/learn/{lms_course}/{lms_lesson}` | `UserPortalController::lessonView` | logged in |
| `lms_progress.dashboard` | `/dashboard` | `UserPortalController::dashboard` | logged in |
| `lms_progress.certificates` | `/my-certificates` | `UserPortalController::myCertificates` | logged in |
| `lms_progress.api_mark_complete` | `/api/lms/progress/mark-complete` [POST] | `ProgressApiController::markComplete` | logged in |

Route `/profile` trong `lms_course.routing.yml` cũng point đến `UserPortalController::profile`.

---

## Provided Services

Không có services file riêng. Controllers inject service trực tiếp qua `\Drupal::service()` (anti-pattern đã ghi nhận).

---

## Provided Plugins

Không có plugin.

---

## Hook Implementations

| Hook | Purpose |
|------|---------|
| `hook_theme()` | 4 templates: lms_dashboard, lms_my_courses, lms_lesson_view, lms_my_certificates |
| `hook_form_alter` (comment form) | `lms_progress_form_comment_lesson_comment_form_alter` — ẩn subject/author fields, đảm bảo text format access, inject after_build handler |
| Comment after_build | `lms_progress_comment_form_after_build` — ẩn format selector, wrap form cho AJAX |
| Comment submit handler | `lms_progress_comment_form_submit_redirect` — redirect về lesson view sau comment |
| Comment submit parent | `lms_progress_set_comment_parent` — set `pid` cho threaded reply |

---

## Templates & Cache Strategy

| Template | Key Variables |
|----------|--------------|
| `lms-dashboard` | stats[], recent_courses[] |
| `lms-my-courses` | courses[] |
| `lms-lesson-view` | course, lesson, curriculum[], video_position, quiz_id, comments[], existing_note |
| `lms-my-certificates` | certificates[] |

- `lms-lesson-view` là template phức tạp nhất, tích hợp video player, sidebar curriculum, quiz embed, comments, và notes.
- Cache: per-user, không cache học tập vì video_position cần realtime.

---

## Related Units

- **C03** § Progress Domain — entities UserCourse, UserChapter, UserLesson
- **C05** `03-progress-commerce.md` — layer overview
- `lms_course.md`, `lms_lesson.md`, `lms_chapter.md` — content entities
- `lms_certificate.md` — `/my-certificates` route
- `lms_note.md` — note display trong lesson view
- `lms_quiz.md` — quiz embed trong lesson

---

## Known Issues / Debt

- Comment form alter quá phức tạp: tự grant text format permission trong runtime hook — RISK security + performance.
- `lms_progress_comment_form_submit_redirect` có lỗi: function kết thúc với `return $response` nhưng `$response` không được khởi tạo ở code path đó — PHP undefined variable warning.
- `UserPortalController` và `ProgressApiController` thiếu service injection chuẩn — nên refactor để inject dependency.
- Không có `lms_user_chapter` entity file trong src/ dù C03 liệt kê — cần clarify.
