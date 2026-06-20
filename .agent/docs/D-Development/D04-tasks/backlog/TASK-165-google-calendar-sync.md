# Task: Google Calendar 2-Way Sync (OAuth)

**ID:** TASK-165
**Unit:** new (raise)
**Group:** G-LIVE-CLASS
**Priority:** P3
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Tích hợp 2 chiều với Google Calendar: học viên connect tài khoản Google, buổi học tự động được thêm vào Google Calendar. Khi buổi học bị hủy hoặc thay đổi giờ, event trên Google Calendar tự động cập nhật. RSVP status từ Google Calendar (accepted/declined) được sync ngược về hệ thống.

Đây là tính năng P3 (nice-to-have), chỉ implement sau khi TASK-164 (iCal export) đã hoàn thành. Việc dùng subscribe URL từ TASK-164 giải quyết 80% use case mà không cần OAuth phức tạp; tính năng này chỉ cần thiết khi cần sync 2 chiều (RSVP) hoặc auto-push (không cần user manually subscribe).

## Acceptance Criteria

- [ ] OAuth2 flow: "Connect Google Calendar" button trong user settings → redirect Google consent → callback lưu `access_token` + `refresh_token` encrypted
- [ ] `GoogleCalendarService::syncSchedule(ClassSchedule $schedule, int $uid)` — tạo/update Google Calendar event
- [ ] Event fields: title, start/end time, description (Zoom join URL), location, conferenceData (Zoom)
- [ ] Khi class_schedule update (reschedule/cancel) → `lms_zoom_class_schedule_update` hook → sync Google Calendar
- [ ] Khi class_schedule bị delete → delete Google Calendar event
- [ ] RSVP sync: background cron mỗi 4h pull event RSVP status từ Google → update `field_attendance_rsvp` trên class_schedule enrollment
- [ ] Disconnect: user có thể revoke Google Calendar access trong settings → revoke token + xóa calendar events đã tạo
- [ ] Scope: chỉ request `https://www.googleapis.com/auth/calendar.events` (không phải full calendar read)
- [ ] Unit test: `GoogleCalendarEventBuilderTest` — event payload format
- [ ] Error handling: nếu Google API trả 401 → refresh token; nếu refresh fail → notify user re-connect

## Technical Notes

- Module: `web/modules/custom/lms_zoom/` (extend — không tạo module mới)
- Service: `src/Service/GoogleCalendarService.php`
- Google API PHP Client: `google/apiclient` (composer require)
- OAuth config: `lms_zoom.google_oauth_settings` — client_id, client_secret, redirect_uri
- Token storage: `user.data` → `lms_zoom.google_calendar.{access_token,refresh_token,expires_at}` — encrypted
- Encryption: dùng `EncryptService` từ `lms_base`
- RSVP cron: `lms_zoom_cron()` — max 50 users per run để tránh rate limit
- Coordinate: TASK-164 (iCal — simpler alternative), TASK-161 (class_schedule data)

## Dependencies

- TASK-164 (iCal export — prerequisite)
- TASK-161 (class_schedule entity)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
