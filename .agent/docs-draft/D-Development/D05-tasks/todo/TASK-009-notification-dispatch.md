# Task: Notification Dispatch Service

**ID:** TASK-009
**Unit:** U-042
**Group:** G-COMMUNICATION
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** TODO (50% done)

## Audit Notes — What Already Exists
- Notification entity (10 types, multi-channel: in_app/email/push) ✅
- NotificationManager: `dispatch()` checks user prefs via UserData, creates in-app notification ✅
- NotificationEventSubscriber: subscribes to QuizPassedEvent ✅
- User preferences form: 9 event types × (in_app, email) checkboxes ✅
- Bell block: unread count + top 5 dropdown with JS ✅
- Notification listing: paged (15/page), type filter, auto-mark-read ✅
- Unread count API: `GET /api/lms/notifications/unread-count` ✅
- Broadcast form: batch announcement to all users ✅
- Cron cleanup: read notifications > 90 days ✅

## Remaining Work
- [ ] Add event subscribers for: CourseEnrolled, CertificateIssued, PaymentCompleted, ForumReply, MembershipExpiry, NewLesson (only QuizPassed exists)
- [ ] Wire email channel (currently logs only, TODO comment in code)
- [ ] Wire push channel (currently logs only)

## Description
Service dispatching notifications dựa trên system events: enrollment, quiz completion, certificate issued, payment, membership expiring.

## Acceptance Criteria
- [ ] Event subscribers cho: CourseEnrolled, QuizCompleted, CertificateIssued, PaymentReceived, MembershipExpiring
- [ ] Tạo in-app Notification entity cho mỗi event
- [ ] Respect user notification preferences
- [ ] Notification types: enrollment, quiz_result, certificate, payment, membership, system
- [ ] Mark as read functionality

## Technical Notes
- Module: `lms_notification`
- Service: `NotificationDispatcher::dispatch($event, $recipient, $type)`
- Event subscribers: 5+ subscribers
- Preferences: per-user config entity hoặc user field

## Dependencies
- U-039 (Notification entity) ✅ DONE

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Unit tests cho dispatch logic
- [ ] Preference respect tests
