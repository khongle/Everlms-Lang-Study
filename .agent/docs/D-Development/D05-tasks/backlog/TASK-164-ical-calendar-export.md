# Task: iCal / .ics Calendar Export

**ID:** TASK-164
**Unit:** U-049 (lms_zoom)
**Group:** G-LIVE-CLASS
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Cho phép học viên subscribe lịch học của họ vào ứng dụng calendar (Apple Calendar, Google Calendar, Outlook) qua định dạng iCalendar (.ics). Có hai loại export: (1) feed subscription URL cá nhân — tự động cập nhật khi có lịch mới; (2) single-event download (.ics file) từ trang chi tiết buổi học.

Feed URL cá nhân cần được bảo mật bằng token (không yêu cầu đăng nhập để subscribe, nhưng token là private). Token rotate khi user yêu cầu. Mỗi VEVENT trong feed bao gồm Zoom join URL trong description và conference data fields theo RFC 7986.

## Acceptance Criteria

- [ ] Personal iCal feed: `/user/{uid}/calendar.ics?token={secret}` — trả `text/calendar` response
- [ ] Feed bao gồm tất cả upcoming `class_schedule` của user (enrolled courses), nhìn về phía trước 90 ngày
- [ ] Mỗi VEVENT: `SUMMARY`, `DTSTART`/`DTEND` (UTC), `DESCRIPTION` (bao gồm Zoom join URL), `LOCATION` (zoom URL), `UID` (`{schedule_id}@everlms`)
- [ ] `CONFERENCE` property (RFC 7986): `VALUE=URI:zoom://...`
- [ ] Single-event download: link "Add to Calendar" trên trang class_schedule — download `.ics` file
- [ ] Token management: `IcalTokenService::generateToken(int $uid): string`, stored hashed trong user data
- [ ] Admin: button "Regenerate Calendar Token" trong user profile settings
- [ ] Feed cache: 1 giờ, Drupal cache tags `class_schedule_{uid}` để invalidate khi schedule thay đổi
- [ ] Unit test: VEVENT output format đúng iCal spec (RFC 5545)
- [ ] Integration test: subscribe feed URL trong PHP iCal parser → parse thành công

## Technical Notes

- Module: `web/modules/custom/lms_zoom/`
- Controller: `src/Controller/IcalFeedController.php`
- Route: `lms_zoom.ical_feed` → `/user/{user}/calendar.ics`
- iCal library: `eluceo/ical` (composer require) — hỗ trợ VTIMEZONE tự động
- Timezone: VTIMEZONE component cho `Asia/Ho_Chi_Minh`
- Token: `user.data` service → `$userData->set('lms_zoom', 'ical_token', $token)`
- Cache: `CacheableResponse` với tag `class_schedule_list:{uid}`
- Feed URL trong user profile: "📅 Subscribe to my schedule" (plain link, no emoji in code)
- Coordinate: TASK-161 (class_schedule data source), TASK-165 (Google Calendar sync)

## Dependencies

- TASK-161 (class_schedule entity)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
