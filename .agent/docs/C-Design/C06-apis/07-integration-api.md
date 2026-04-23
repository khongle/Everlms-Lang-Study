# C06-07 — Integration API

> Modules: `lms_ai`, `lms_zoom`, `lms_analytics`.
> Tất cả 3 modules có admin config routes. `lms_zoom` có 2 runtime endpoints. `lms_analytics` có report + export routes. `lms_ai` có preview JSON endpoint.

---

## Route Table

### lms_ai

| Route ID | Path | Method | Controller / Form | Auth | Response |
|----------|------|--------|-------------------|------|----------|
| `lms_ai.settings` | `/admin/config/lms/ai` | GET/POST | `AiSettingsForm` | perm:administer site configuration | HTML/Form |
| `lms_ai.course_builder_form` | `/manage/courses/ai-builder` | GET/POST | `CourseBuilderForm` | perm:administer lms_course | HTML/Form |
| `lms_ai.engine_users_form` | `/admin/everlms-ai-engine` | GET/POST | `AiEngineUserForm` | perm:administer site configuration | HTML/Form |
| `lms_ai.preview_users_json` | `/admin/everlms-ai-engine/preview-users-json` | GET | `AiPreviewController::previewUsersJson` | perm:administer site configuration | JSON |

### lms_zoom

| Route ID | Path | Method | Controller | Auth | Response |
|----------|------|--------|-----------|------|----------|
| `lms_zoom.settings` | `/admin/config/lms/zoom` | GET/POST | `ZoomSettingsForm` | perm:administer site configuration | HTML/Form |
| `lms_zoom.join_meeting` | `/zoom/join/{meeting_id}` | GET | `ZoomController::joinMeeting` | login | HTML/redirect |
| `lms_zoom.generate_signature` | `/api/lms/websdk/signature` | POST | `ZoomController::generateSignature` | login | JSON |

### lms_analytics

| Route ID | Path | Method | Controller | Auth | Response |
|----------|------|--------|-----------|------|----------|
| `lms_analytics.course_report` | `/admin/lms/reports/courses` | GET | `CourseReportController::build` | perm:administer lms_course | HTML |
| `lms_analytics.course_report_export` | `/admin/lms/reports/courses/export` | GET | `CourseReportController::export` | perm:administer lms_course | CSV/XLSX |
| `lms_analytics.user_report` | `/admin/lms/reports/users` | GET | `UserReportController::build` | perm:administer users | HTML |
| `lms_analytics.user_report_export` | `/admin/lms/reports/users/export` | GET | `UserReportController::export` | perm:administer users | CSV/XLSX |
| `lms_analytics.quiz_report_list` | `/admin/lms/reports/quizzes` | GET | `QuizReportController::buildList` | perm:administer lms_quiz | HTML |
| `lms_analytics.quiz_report_detail` | `/admin/lms/reports/quizzes/{quiz_id}` | GET | `QuizReportController::buildDetail` | perm:administer lms_quiz | HTML |
| `lms_analytics.quiz_report_export` | `/admin/lms/reports/quizzes/{quiz_id}/export` | GET | `QuizReportController::exportItemAnalysis` | perm:administer lms_quiz | CSV/XLSX |

---

## Route Chi Tiết: Zoom

### POST `/api/lms/websdk/signature`

Endpoint tạo JWT signature để khởi chạy Zoom Web SDK trong browser.

**Auth:** `_user_is_logged_in: TRUE`
**Method:** POST
**Content-Type:** `application/json`

**Request Body:**
```json
{
  "meeting_number": "123456789",
  "role": 0
}
```

| Field | Type | Required | Mô tả |
|-------|------|----------|-------|
| `meeting_number` | string | Có | Zoom meeting number |
| `role` | integer | Có | 0 = attendee, 1 = host |

**Response (JSON):**
```json
{
  "signature": "eyJhbGci...",
  "sdk_key": "abc123",
  "meeting_number": "123456789",
  "user_name": "Nguyen Van A",
  "user_email": "user@example.com"
}
```

**Business logic:**
- Server tạo HMAC-SHA256 JWT dùng Zoom SDK Key + Secret (từ config).
- Signature có TTL ngắn (thường 2h).
- Chỉ expose SDK Key, không expose Secret.

> **RISK-001:** Không có rate limiting trên endpoint này — có thể bị abuse để generate signatures.

---

### GET `/zoom/join/{meeting_id}`

**Auth:** `_user_is_logged_in: TRUE`
**Path param:** `meeting_id` — raw string (không entity upcast)

**Business logic:**
- Load meeting config từ lesson entity.
- Redirect hoặc render Zoom Web SDK embed page.
- Kiểm tra user enrolled trong course chứa lesson.

---

## Route Chi Tiết: AI

### GET `/admin/everlms-ai-engine/preview-users-json`

**Auth:** `perm:administer site configuration`
**Controller:** `AiPreviewController::previewUsersJson`
**Response:** JSON — preview dữ liệu users sẽ được gửi lên AI engine.

> **RISK-003:** AI generation không có rollback. Nếu AI tạo course structure thất bại giữa chừng, partial data có thể tồn tại. Cần state machine + compensation.

### GET/POST `/manage/courses/ai-builder`

**Auth:** `perm:administer lms_course`
**Form:** `CourseBuilderForm`

**Flow:**
1. Instructor nhập topic/description.
2. Form submit → gọi AI provider plugin (OpenAI / Google).
3. AI trả course outline → form hiển thị preview.
4. Confirm → tạo `lms_course`, `lms_chapter`, `lms_lesson` entities.

**AI Provider Plugin Architecture:**
- Config tại `/admin/config/lms/ai`.
- Provider-agnostic interface — có thể switch OpenAI ↔ Google Gemini qua admin.
- **DEC-002:** Chưa quyết định abstraction layer (Drupal Plugin vs Service+Config).

---

## Route Chi Tiết: Analytics

### GET `/admin/lms/reports/courses/export`

**Auth:** `perm:administer lms_course`
**Response:** CSV hoặc XLSX download (Content-Disposition: attachment)

**Dữ liệu export:** course title, enrolled count, completion rate, avg score, revenue.

### GET `/admin/lms/reports/quizzes/{quiz_id}/export`

**Auth:** `perm:administer lms_quiz`
**Path param:** `quiz_id` — raw integer (không entity upcast — RISK-001)

**Dữ liệu export:** Per-question item analysis (correct rate, avg time, discrimination index).

---

## Gap Summary

| Thiếu | Mô tả |
|-------|-------|
| GA4 integration route | `lms_analytics` tích hợp Google Analytics 4 — không thấy route hay controller cho GA4 event push |
| Zoom webhook receiver | Zoom gửi webhook (meeting.ended, recording.completed) — không có route nhận |
| AI generation status API | Nếu AI generation async, cần `GET /api/ai/job/{id}/status` — chưa có |
| Revenue report route | `/admin/lms/reports/revenue` trong sitemap nhưng không có trong lms_analytics.routing.yml |

---

## Known Issues

| Ref | Mô tả |
|-----|-------|
| RISK-003 | AI course generation thiếu rollback/compensation khi fail giữa chừng |
| DEC-002 | AI provider abstraction chưa quyết định — ảnh hưởng extensibility |
| RISK-001 | `/api/lms/websdk/signature` không có rate limit |
| RISK-001 | `quiz_id` trong analytics routes là raw integer — không upcast entity, không có access check |
| — | Revenue report trong sitemap (C07) nhưng thiếu route trong lms_analytics.routing.yml |
| — | Zoom webhook receiver không có route |

---

*Module paths: `web/modules/custom/lms_ai/`, `lms_zoom/`, `lms_analytics/`*
