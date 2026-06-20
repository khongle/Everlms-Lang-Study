# Task: Basic Webcam Proctoring (Opt-In per Quiz)

**ID:** TASK-173
**Unit:** new (raise)
**Group:** G-ASSESSMENT
**Priority:** P3
**Estimate:** 24h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Triển khai module mới `lms_proctor` cung cấp tính năng giám sát cơ bản (basic proctoring) opt-in cho quiz. Proctoring gồm: yêu cầu quyền truy cập webcam, chụp ảnh snapshot mỗi 30 giây, bắt sự kiện mất focus tab/cửa sổ trình duyệt. Snapshot được lưu trữ private và chỉ được review khi có flag vi phạm. Tính năng này opt-in per quiz — instructor enable trong quiz settings.

Privacy notice bắt buộc: student phải xem và đồng ý với privacy policy trước khi bắt đầu quiz có proctoring. Snapshot KHÔNG được xử lý AI/facial recognition trong phạm vi task này — chỉ lưu trữ để instructor review thủ công khi cần.

## Acceptance Criteria

- [ ] Module `lms_proctor` tại `web/modules/custom/lms_proctor/` với `lms_proctor.info.yml`
- [ ] Field `field_proctoring_enabled` (boolean) trên `lms_quiz` entity
- [ ] Khi `field_proctoring_enabled = TRUE` → student thấy privacy consent dialog trước khi start quiz
- [ ] Consent phải explicit (checkbox + nút "Tôi đồng ý và bắt đầu") — không thể skip
- [ ] JavaScript: `ProctorSession.js` — request webcam access, capture snapshot mỗi 30s
- [ ] Snapshot upload: `POST /proctor/snapshot/{attempt_id}` — multipart form, JPEG 320x240, max 50KB
- [ ] Backend: lưu snapshot file `private://proctor/{attempt_id}/{timestamp}.jpg`
- [ ] Tab focus event: JavaScript `visibilitychange` + `blur` event → `POST /proctor/event/{attempt_id}` với payload `{type: 'tab_switch', at: timestamp}`
- [ ] Flag logic: ≥ 3 tab switch events trong 1 lần thi → `ProctorSession.flag = true`, thông báo admin
- [ ] Admin review UI: `/admin/lms/proctor-review` — list flagged attempts, slideshow snapshots, event timeline
- [ ] Snapshots tự xóa sau 30 ngày (cron cleanup) nếu attempt không bị flag
- [ ] Unit test: flag threshold logic; Kernel test: snapshot upload → file stored private

## Technical Notes

- Module path: `web/modules/custom/lms_proctor/`
- JS: `lms_proctor/js/proctor-session.js` — attach trong Drupal behaviors khi quiz route với proctoring enabled
- Webcam API: `navigator.mediaDevices.getUserMedia({video: true})` → `HTMLCanvasElement.toBlob()`
- Backend route: `lms_proctor.snapshot` → `ProctorSnapshotController::upload()`
- File: `private://proctor/` — `hook_file_download()` restrict to admin + instructor only
- Event entity `ProctorEvent`: attempt_id, type, timestamp, flagged — lightweight DB table
- Snapshot cleanup: `lms_proctor_cron()` — delete unflagged attempt snapshots older than 30 days
- Coordinate: TASK-176 (cert quiz — proctoring enabled by default on cert quizzes)

## Dependencies

- G-ASSESSMENT (quiz attempt infrastructure)
- TASK-171 (attempt context)
- TASK-176 (cert quiz — primary use case)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
