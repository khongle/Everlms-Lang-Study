# C06-03 — Progress API

> Module: `lms_progress`.
> Mix giữa page routes (HTML) và một JSON API endpoint thuần. Dashboard + learning portal nằm trong module này.

---

## Route Table

| Route ID | Path | Method | Controller | Auth | Response |
|----------|------|--------|-----------|------|----------|
| `lms_progress.dashboard` | `/dashboard` | GET | `UserPortalController::dashboard` | login | HTML |
| `lms_progress.my_courses` | `/my-courses` | GET | `UserPortalController::myCourses` | login | HTML |
| `lms_progress.lesson_view` | `/learn/{lms_course}/{lms_lesson}` | GET | `UserPortalController::lessonView` | login | HTML |
| `lms_progress.certificates` | `/my-certificates` | GET | `UserPortalController::myCertificates` | login | HTML |
| `lms_progress.api_mark_complete` | `/api/lms/progress/mark-complete` | POST | `ProgressApiController::markComplete` | login | JSON |

> Route `lms_course.profile` (`/profile`) cũng handled bởi `UserPortalController::profile` nhưng khai báo trong `lms_course.routing.yml`.

---

## Route Chi Tiết

### GET `/dashboard`

**Auth:** `_user_is_logged_in: TRUE`
**Controller:** `UserPortalController::dashboard`

**Dữ liệu render:**
- Tổng số khóa học đang học / đã hoàn thành.
- Tổng số chứng chỉ.
- Recent activity (lessons viewed recently).
- Upcoming deadlines (nếu có).

---

### GET `/my-courses`

**Auth:** `_user_is_logged_in: TRUE`
**Controller:** `UserPortalController::myCourses`

**Dữ liệu render:** Danh sách enrolled courses với `lms_user_course.progress_percent`, status (in-progress / completed).

---

### GET `/learn/{lms_course}/{lms_lesson}`

**Auth:** `_user_is_logged_in: TRUE`
**Path params:**
- `lms_course` — upcast to `lms_course` entity
- `lms_lesson` — upcast to `lms_lesson` entity

**Business logic:**
- Kiểm tra enroll access cho course.
- Kiểm tra lesson thuộc chapter thuộc course.
- Render lesson theo type (video/text/h5p/scorm/quiz/zoom/mixed).
- Tự động tạo / cập nhật `lms_user_lesson` record khi vào trang.

**Gap:** Lesson completion chỉ được trigger qua `mark-complete` POST — trang view không tự mark complete.

---

### GET `/my-certificates`

**Auth:** `_user_is_logged_in: TRUE`
**Controller:** `UserPortalController::myCertificates`
**Dữ liệu render:** Danh sách `lms_user_certificate` của user, với link download PDF.

---

### POST `/api/lms/progress/mark-complete`

Đây là endpoint JSON API duy nhất trong progress domain.

**Auth:** `_user_is_logged_in: TRUE`
**Controller:** `ProgressApiController::markComplete`
**Content-Type:** `application/json`

#### Request Body

```json
{
  "lesson_id": 123,
  "course_id": 45
}
```

| Field | Type | Required | Mô tả |
|-------|------|----------|-------|
| `lesson_id` | integer | Có | ID của `lms_lesson` entity |
| `course_id` | integer | Có | ID của `lms_course` entity |

#### Response (JSON)

**Success (200):**
```json
{
  "success": true,
  "lesson_complete": true,
  "chapter_complete": false,
  "course_complete": false,
  "progress_percent": 45.5,
  "next_lesson_url": "/learn/45/124"
}
```

**Error:**
```json
{
  "success": false,
  "message": "Access denied or lesson not found."
}
```

#### Business Logic

1. Validate `lesson_id` và `course_id` — kiểm tra enroll.
2. Upsert `lms_user_lesson` (completed = TRUE).
3. Recalculate chapter progress → upsert `lms_user_chapter`.
4. Recalculate course progress → upsert `lms_user_course`.
5. Nếu course 100% → kiểm tra điều kiện cấp certificate.
6. Trả `next_lesson_url` để frontend navigate.

> **RISK-002:** Race condition nếu nhiều tab/thiết bị gửi mark-complete đồng thời — có thể gây course_complete tính sai. Cần DB transaction + row-level lock hoặc queue.

---

## Gap: Progress Domain

| Thiếu | Mô tả |
|-------|-------|
| `GET /api/lms/progress/{course_id}` | Không có endpoint để đọc progress theo course — phải query qua trang HTML |
| SCORM tracking endpoint | SCORM lessontype cần xAPI/SCORM endpoint riêng |
| H5P xAPI endpoint | H5P completion tracking endpoint không có route |
| Progress batch update | Không có bulk mark-complete (e.g., skip chapter) |

---

## Known Issues

| Ref | Mô tả |
|-----|-------|
| RISK-001 | `mark-complete` POST thiếu server-side field validation schema — `lesson_id` / `course_id` không validated type/range |
| RISK-002 | Concurrent mark-complete → race condition trên progress recalculation |
| — | `/learn/{course}/{lesson}` không có check lesson thuộc về course — có thể gọi với mismatched IDs |

---

*Module path: `web/modules/custom/lms_progress/`*
