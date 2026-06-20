# Task: Vietnamese Holiday Calendar Awareness for Cron & Email Blast

**ID:** TASK-159
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P3
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Tích hợp nhận thức về ngày lễ Việt Nam vào hệ thống cron và email blast, tránh gửi email marketing/thông báo vào các ngày nghỉ lễ lớn (Tết Nguyên Đán, 30/4, 1/5, 2/9...). Danh sách ngày lễ cố định hàng năm, nhưng Tết Nguyên Đán và nghỉ bù thay đổi theo quyết định của Bộ Lao động — Thương binh và Xã hội (thường ban hành tháng 10-11 năm trước).

`HolidayService` trong `lms_base` cung cấp phương thức check ngày có phải ngày lễ/cuối tuần không. Email blast campaign và scheduled notifications sẽ query service này trước khi queue message. Admin có thể cập nhật danh sách ngày nghỉ hàng năm qua UI đơn giản.

## Acceptance Criteria

- [ ] `HolidayService` trong `lms_base`: `isHoliday(DateTimeInterface $date): bool` và `isWorkingDay(DateTimeInterface $date): bool`
- [ ] Dataset mặc định hardcode: Tết Dương lịch (1/1), Giỗ Tổ Hùng Vương (10/3 ÂL), 30/4, 1/5, Quốc khánh (2/9)
- [ ] Config entity `VnHolidayYear`: admin nhập danh sách ngày nghỉ cho từng năm (JSON format) — override dataset mặc định
- [ ] Admin UI: `/admin/lms/holidays` — form nhập ngày nghỉ theo năm, import từ file JSON
- [ ] `HolidayService::getNextWorkingDay(DateTimeInterface $date): DateTimeInterface` — skip holidays + weekend
- [ ] Email blast scheduler: nếu ngày gửi là ngày lễ → dời sang `getNextWorkingDay()`
- [ ] Scheduled notification (TASK-166 class reminders): không bị ảnh hưởng — class reminder phải gửi đúng giờ dù ngày lễ
- [ ] Unit test: 30/4/2026 là ngày lễ; 2/5/2026 là ngày làm việc tiếp theo (1/5 cũng nghỉ)
- [ ] Lunar calendar helper (optional): tính ngày Âm lịch để detect Tết tự động

## Technical Notes

- Module: `web/modules/custom/lms_base/src/Service/HolidayService.php`
- Config entity: `web/modules/custom/lms_base/src/Entity/VnHolidayYear.php`
- Fixed holidays storage: `lms_base/config/install/lms_base.vn_holidays_fixed.yml`
- Lunar calendar lib: `overtrue/chinese-calendar` (PHP) nếu cần tự động Tết
- Email blast integration: hook vào `lms_email` / `lms_notification` queue worker pre-dispatch
- Năm 2026: Tết Bính Ngọ 17-23/1/2026 (tạm tính), 30/4 Thứ Năm nghỉ 5 ngày

## Dependencies

- TASK-139 (email digest — có scheduled send)
- TASK-146 (email campaign builder)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
