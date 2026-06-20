# Task: Instructor Office Hours — 1-on-1 Booking System

**ID:** TASK-167
**Unit:** new (raise)
**Group:** G-LIVE-CLASS
**Priority:** P3
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Xây dựng hệ thống đặt lịch hẹn 1-on-1 giữa học viên và giảng viên (Calendly-lite). Instructor set các khung giờ rảnh (availability slots), học viên enrolled trong course của instructor có thể book một slot. Sau khi book, Zoom meeting được tự động tạo và link gửi qua email cho cả hai bên.

Đây là P3 — implement sau khi các infrastructure live class cơ bản (TASK-161 → 166) đã ổn định. Tính năng này tăng giá trị khóa học premium ("1-on-1 mentoring") và phổ biến trong mô hình học tiếng Anh/luyện thi tại VN.

## Acceptance Criteria

- [ ] Instructor UI: `/instructor/office-hours` — weekly availability grid (Mon–Sun, hourly slots), toggle on/off
- [ ] Config entity `OfficeHoursAvailability`: instructor uid, day_of_week, start_time, end_time, slot_duration_minutes (default 30)
- [ ] Content entity `OfficeHoursBooking`: instructor uid, student uid, course_id, start_datetime, end_datetime, status (pending/confirmed/cancelled), zoom_meeting_id, notes
- [ ] Student UI: `/course/{id}/book-office-hours` — calendar view hiển thị slots còn trống (đã trừ booked)
- [ ] Book flow: student chọn slot → confirm dialog → create booking → auto-create Zoom meeting → email cả hai
- [ ] Instructor: nhận email "Học viên X đã đặt lịch {date}" với link confirm/decline (nếu không auto-confirm)
- [ ] Cancel: student hoặc instructor có thể cancel ≥ 1h trước → Zoom meeting deleted → email notify
- [ ] Buffer time: 15 phút giữa các booking (configurable)
- [ ] Student chỉ book được 1 slot / week / instructor (rate limit chống lạm dụng)
- [ ] Unit test: slot availability calculation — exclude booked + buffer
- [ ] Kernel test: booking flow → Zoom meeting created → email queued

## Technical Notes

- Module: `web/modules/custom/lms_zoom/`
- Availability service: `src/Service/OfficeHoursService.php`
- Slot generation: expand weekly availability into datetime slots, exclude booked + buffer
- Zoom: `ZoomApiClient::createMeeting()` với duration = slot_duration_minutes
- Email: `lms_notification` template `office_hours_booking_confirm`
- Student rate limit: check `OfficeHoursBooking` count per student+instructor+week in service layer
- Calendar UI: consider Drupal fullcalendar library hoặc simple grid HTML table
- Coordinate: TASK-161 (Zoom integration), TASK-164 (iCal link in email)

## Dependencies

- TASK-161 (class_schedule + Zoom integration)
- TASK-023 (Zoom API)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
