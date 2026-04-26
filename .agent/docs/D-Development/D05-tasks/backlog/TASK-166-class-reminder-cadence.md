# Task: Class Reminder Cadence (24h / 1h / 5min)

**ID:** TASK-166
**Unit:** U-049 (lms_zoom)
**Group:** G-LIVE-CLASS
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Triển khai hệ thống nhắc nhở buổi học trực tuyến theo lịch 3 mức: 24 giờ trước (email), 1 giờ trước (email + push notification), và 5 phút trước (push notification only). Mục tiêu: giảm tỷ lệ no-show trong các buổi live class — vấn đề phổ biến với học online VN. Học viên có thể tắt từng kênh trong settings cá nhân (TASK-140 notification preferences UI).

Reminder được queue qua `lms_notification` queue worker, không chạy trực tiếp trong cron để tránh timeout. Mỗi `class_schedule` khi được publish sẽ tự động tạo reminder jobs. Khi schedule bị hủy hoặc reschedule, các reminder job chưa chạy phải được cancel/recreate.

## Acceptance Criteria

- [ ] `ClassReminderScheduler::scheduleReminders(ClassSchedule $schedule)` — tạo 3 queue items (24h, 1h, 5min)
- [ ] Queue item data: `{ uid, schedule_id, reminder_type: '24h'|'1h'|'5min', send_at: timestamp }`
- [ ] Queue worker `ClassReminderWorker`: pop item, check `send_at <= now`, dispatch notification
- [ ] 24h và 1h: gọi `lms_notification` email channel với template `class_reminder_{type}`
- [ ] 1h và 5min: gọi push notification channel (FCM/web push) nếu user đã register device token
- [ ] Email template: kèm Zoom join URL, tên buổi học, giờ bắt đầu (Asia/Ho_Chi_Minh), link "Add to Calendar" (TASK-164)
- [ ] Nếu schedule bị cancel: `ClassReminderScheduler::cancelReminders(int $scheduleId)` — flag queue items CANCELLED (soft delete)
- [ ] Nếu schedule reschedule: cancel cũ + schedule lại mới
- [ ] User preferences check: `NotificationPreferenceService::isEnabled(uid, 'class_reminder', channel)` — theo TASK-140
- [ ] Unit test: `ClassReminderSchedulerTest` — 3 items created với đúng `send_at`
- [ ] Unit test: cancel flow — items flagged CANCELLED không được dispatch

## Technical Notes

- Module: `web/modules/custom/lms_zoom/` + integration với `lms_notification`
- Queue: Drupal `QueueInterface` — `lms_class_reminder` queue name
- Queue worker: `src/Plugin/QueueWorker/ClassReminderWorker.php`
- Cron frequency: cron mỗi 5 phút để bắt kịp window 5 min reminder
- Scheduler service: `src/Service/ClassReminderScheduler.php`
- Hook: `lms_zoom_class_schedule_insert/update()` → trigger scheduler
- Reminder không bị hoãn bởi TASK-159 (holiday calendar) — live class không skip reminder dù ngày lễ
- Coordinate: TASK-140 (notification prefs), TASK-161 (class_schedule), TASK-164 (calendar link)

## Dependencies

- TASK-161 (class_schedule entity)
- TASK-140 (notification preferences UI)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
