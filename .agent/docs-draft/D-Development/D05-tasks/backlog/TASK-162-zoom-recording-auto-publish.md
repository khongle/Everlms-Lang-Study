# Task: Zoom Recording Auto-Publish to Lesson

**ID:** TASK-162
**Unit:** U-049 (lms_zoom)
**Group:** G-LIVE-CLASS
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Tự động publish video recording của Zoom meeting vào bài học (lesson) tương ứng ngay sau khi buổi học kết thúc. Zoom gửi webhook event `recording.completed` chứa danh sách recording files (video, audio, transcript). Hệ thống cần: verify webhook signature Zoom, tìm `lms_lesson` gắn với `zoom_meeting_id`, lưu URL recording vào `field_video`, publish lesson nếu đang ở draft.

Đây là tính năng quan trọng cho mô hình "blended learning": học viên vắng mặt hoặc muốn xem lại có thể xem recording được tự động gắn vào lesson. Access control: chỉ học viên đã enroll course mới xem được (không phải public URL của Zoom).

## Acceptance Criteria

- [ ] Zoom webhook handler mở rộng trong `lms_zoom` xử lý event `recording.completed`
- [ ] Verify Zoom webhook signature (HMAC-SHA256 với `webhookSecretToken`)
- [ ] Parse payload: tìm file type `MP4` trong `recording_files[]`, lấy `download_url` + `file_size`
- [ ] Lookup `class_schedule` theo `payload.object.id` (Zoom meeting ID) → tìm `lms_lesson` liên kết
- [ ] Download recording về `private://zoom-recordings/{meeting_id}/` — KHÔNG dùng Zoom URL trực tiếp để kiểm soát access
- [ ] Update `field_video` trên `lms_lesson` (managed file reference)
- [ ] Nếu lesson status = draft → set published = TRUE, ghi log
- [ ] Gửi notification email cho enrolled students: "Buổi học {date} đã có recording — xem ngay"
- [ ] Admin UI: danh sách recordings với status (pending/downloaded/published/failed)
- [ ] Retry: nếu download fail → queue retry 3 lần với backoff
- [ ] Unit test: webhook payload parse; Kernel test: recording.completed → lesson video updated

## Technical Notes

- Module: `web/modules/custom/lms_zoom/`
- Webhook route: `/zoom/webhook` (đã có từ TASK-023) — thêm case `recording.completed`
- Zoom signature verify: `hash_hmac('sha256', $timestamp . $body, $webhookSecret)`
- Download via `GuzzleHttp\Client` với Zoom download token (expires 24h) — cần process immediately
- File storage: `private://` để dùng Drupal access control, không expose raw Zoom URL
- Link tìm lesson: `class_schedule` entity → `field_lesson` (thêm field trong TASK-161)
- Coordinate: TASK-161 (class_schedule entity), TASK-163 (attendance)

## Dependencies

- TASK-161 (class_schedule entity)
- TASK-023 (Zoom API base)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
