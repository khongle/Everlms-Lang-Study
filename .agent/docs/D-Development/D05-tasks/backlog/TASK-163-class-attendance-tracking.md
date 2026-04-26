# Task: Class Attendance Tracking via Zoom Webhooks

**ID:** TASK-163
**Unit:** U-049 (lms_zoom)
**Group:** G-LIVE-CLASS
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Theo dõi điểm danh (attendance) buổi học trực tuyến tự động qua Zoom webhook events `participant.joined` và `participant.left`. Mỗi lần tham gia/rời khỏi được ghi vào entity `class_attendance`, cho phép tính tổng thời gian tham dự của từng học viên trong một buổi học. Dữ liệu này hiển thị trong dashboard instructor và transcript học viên.

Điểm danh tự động có giá trị đặc biệt khi kết hợp với TASK-168 (cohort mode) và khi course yêu cầu tỷ lệ tham dự tối thiểu để cấp chứng chỉ (ví dụ: "phải tham dự ≥ 80% số buổi học"). Entity lưu raw data; service layer tính toán aggregates.

## Acceptance Criteria

- [ ] Content entity `class_attendance` với fields: `field_user` (entity_ref → user), `field_class_schedule` (entity_ref → class_schedule), `field_joined_at` (datetime), `field_left_at` (datetime, nullable), `field_duration_seconds` (integer, computed on close)
- [ ] Zoom webhook handler xử lý `participant.joined`: tạo `class_attendance` record mới (left_at = NULL)
- [ ] Zoom webhook handler xử lý `participant.left`: tìm open attendance record, set `left_at` + compute `duration_seconds`
- [ ] `AttendanceService::getSummary(int $userId, int $scheduleId): AttendanceSummary` — tổng duration, số lần join
- [ ] `AttendanceService::getCourseAttendanceRate(int $userId, int $courseId): float` — phần trăm sessions tham dự
- [ ] Instructor dashboard view: per-session attendance list — tên học viên, thời gian join/leave, duration
- [ ] Student transcript: danh sách buổi đã tham dự + tổng giờ học live
- [ ] Webhook dedup: nếu participant.joined đến 2 lần (reconnect) → create new record thay vì cập nhật
- [ ] Unit test: `AttendanceServiceTest` — tính rate 3/4 sessions = 75%
- [ ] Kernel test: webhook joined → created; webhook left → duration computed

## Technical Notes

- Module: `web/modules/custom/lms_zoom/`
- Entity: `src/Entity/ClassAttendance.php` — lightweight entity, không cần revisions
- Webhook route: `/zoom/webhook` — add cases `participant.joined`, `participant.left`
- Zoom participant identifier: `user_id` (Zoom) → map sang Drupal uid qua `field_zoom_user_id` trên User entity (hoặc match bằng email)
- Email matching: Zoom participant email = Drupal account email (primary match key)
- Instructor view: Drupal Views với relationship to `class_schedule` → `class_attendance`
- Aggregate query: `\Drupal::database()->select('class_attendance')` GROUP BY uid, schedule_id

## Dependencies

- TASK-161 (class_schedule entity)
- TASK-023 (Zoom webhook infrastructure)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
