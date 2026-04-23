# D03 — Unit Spec: Notification Center UI

**Unit ID:** U-081
**Group:** G-UI-POLISH
**Title:** Notification center UI
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Build the notification center: bell icon in header with unread badge count, dropdown with recent notifications, full notification center page, mark-as-read (individual + all), and notification type icons.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/notification-bell.html.twig` | Create | lms_notification |
| `templates/notification-center.html.twig` | Create | lms_notification |
| `templates/notification-item.html.twig` | Create | lms_notification |
| `scss/components/_notifications.scss` | Create | lms_theme |
| `js/notifications.js` | Create | lms_theme |
| `src/Controller/NotificationController.php` | Modify | lms_notification |

---

## Interfaces / Services

- Bell dropdown: last 10 unread notifications (AJAX endpoint)
- Unread count: badge on bell icon, polled every 60s via JS
- Mark-read: `POST /notifications/{id}/read` + `POST /notifications/read-all`
- Notification types with icons: enrollment (book), quiz (pencil), certificate (award), forum (chat), membership (card), system (info)

---

## Acceptance Criteria

- [ ] Bell icon in header shows unread count badge (0 hides badge)
- [ ] Click bell: dropdown with 10 most recent notifications, type icon, relative time
- [ ] Click notification: mark as read + navigate to relevant content
- [ ] "Mark all read" button in dropdown and on full center page
- [ ] Full center page: paginated list of all notifications, filter by type
- [ ] Unread count refreshes every 60 seconds via AJAX (no full page reload)

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | User can only mark own notifications as read |

---

## Dependencies

- U-039 (notification entity)
- U-042 (notification dispatch — notifications must exist to display)
- G-HARDENING complete

**Feature Flag:** None

*End of U-081-notification-center-ui.md*
