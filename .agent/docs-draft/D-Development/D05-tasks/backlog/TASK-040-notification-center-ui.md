# Task: Notification Center UI

**ID:** TASK-040
**Unit:** U-081
**Group:** G-UI-POLISH
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
UI cho notification center: bell icon dropdown, notification list page, mark read/unread, notification preferences.

## Acceptance Criteria
- [ ] Bell icon với unread count badge (header)
- [ ] Dropdown: recent notifications preview (5-10 items)
- [ ] Notification list page: full list, pagination, filters (all/unread)
- [ ] Mark as read/unread (individual + mark all)
- [ ] Notification item: icon by type, title, time ago, action link
- [ ] Delete notification
- [ ] Real-time update (AJAX polling hoặc SSE)
- [ ] Responsive layout

## Technical Notes
- Module: `lms_notification` (NotificationController exists, templates + js + css exist)
- Templates: `lms-notification-*.html.twig`
- JS: notification polling, mark read
- Bell block already configured: `block.block.lms_classic_notificationbell.yml`

## Dependencies
- TASK-009 (Notification dispatch)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Real-time badge update functional
- [ ] Mobile responsive verified
