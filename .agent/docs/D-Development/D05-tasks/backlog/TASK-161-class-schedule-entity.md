# Task: Class Schedule Entity (Recurring Live Meetings)

**ID:** TASK-161
**Unit:** new (raise)
**Group:** G-LIVE-CLASS
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Tạo entity `class_schedule` để quản lý lịch học trực tuyến (live class sessions), mở rộng từ `lms_zoom` module. Hiện tại TASK-023 chỉ implement Zoom API stub cho single meeting; cần entity đầy đủ để hỗ trợ recurring meetings, nhiều học viên, nhiều cohort, và gắn với course/lesson cụ thể.

`class_schedule` là content entity (không phải config entity) vì có nhiều instance. Một course có thể có nhiều schedule (e.g., "Lớp thứ 2 - thứ 4 - thứ 6, 19:00"). Instructor được assign vào schedule, student enroll qua course enrollment. Khi schedule được publish, auto-create Zoom meeting qua API (TASK-023 integration point U-049).

## Acceptance Criteria

- [ ] Content entity `class_schedule` trong module `lms_zoom` với bundle machine name `class_schedule`
- [ ] Fields: `field_course` (entity_reference → lms_course), `field_cohort` (entity_reference, nullable), `field_instructor` (entity_reference → user), `field_start_datetime` (datetime), `field_duration_minutes` (integer), `field_timezone` (string), `field_max_attendees` (integer), `field_zoom_meeting_id` (string), `field_zoom_join_url` (uri), `field_zoom_password` (string, encrypted)
- [ ] Recurrence: `field_recurrence_rule` (string, iCal RRULE format) — e.g., `FREQ=WEEKLY;BYDAY=MO,WE,FR;COUNT=12`
- [ ] `ClassScheduleManager::createZoomMeeting(ClassSchedule $schedule)` — gọi Zoom API, lưu `meeting_id` + `join_url`
- [ ] Entity presave: tự động create Zoom meeting khi status → published và `zoom_meeting_id` còn trống
- [ ] Admin UI: `/admin/lms/class-schedules` — CRUD form với date picker, recurrence UI
- [ ] Student view: "Lịch học của tôi" — danh sách schedule theo course enrolled
- [ ] Upcoming schedule block: hiển thị next 3 sessions trong student dashboard
- [ ] Unit test: `ClassScheduleTest` — recurrence rule expansion (5 occurrences từ RRULE)
- [ ] Access control: instructor chỉ edit schedule của mình; admin full access

## Technical Notes

- Module: `web/modules/custom/lms_zoom/`
- Entity class: `src/Entity/ClassSchedule.php` — extends `ContentEntityBase`
- Entity type ID: `class_schedule`
- RRULE parser: `simshaun/recurr` library (composer require)
- Zoom API: `ZoomApiClient::createMeeting()` từ TASK-023 / U-049
- `field_zoom_password`: store encrypted, dùng `EncryptService` từ `lms_base`
- Timezone: store IANA timezone string, convert to UTC khi gửi Zoom API
- Coordinate: TASK-162 (recording), TASK-163 (attendance), TASK-164 (iCal export)

## Dependencies

- TASK-023 (Zoom API stub)
- U-049 (Zoom integration)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
