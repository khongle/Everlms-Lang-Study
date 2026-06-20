# Task: Notification Preferences UI

**ID:** TASK-140
**Unit:** — (extend `lms_notification`)
**Group:** G-COMMUNICATION (extension)
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Build a notification preferences page in the user account area. Users control which event types trigger notifications and through which channels (in-app, email, push). Sensible defaults are applied for new users. Preferences are stored per user and respected by all notification dispatch calls.

## Context
- `lms_notification` dispatch service (TASK-009) currently has no per-user channel preference layer.
- TASK-064 (admin send notification) and TASK-139 (email digest) both need to honour these preferences.
- Push notifications are future-scope; include the toggle but mark it "coming soon" and skip actual push delivery in this task.

## Acceptance Criteria
- [ ] Preferences page at `/user/{uid}/notification-preferences` accessible by the account owner
- [ ] Events grouped by category: Learning (enrollment, lesson complete, course complete, certificate), Community (forum reply, Q&A answer, lesson comment, mention), Marketing (email digest, promotions)
- [ ] Per-event toggles: In-App (checkbox), Email (checkbox), Push (checkbox — disabled/labelled "coming soon")
- [ ] "Save preferences" button; success message on save
- [ ] Defaults on new user: In-App ON for all events; Email ON for learning events only; Email OFF for community/marketing by default
- [ ] Default preferences seeded via `hook_user_insert`
- [ ] Dispatch service checks preferences before sending; if channel disabled for event type, skip silently
- [ ] Admin can view (read-only) any user's preferences at `/admin/lms/users/{uid}/notification-preferences`

## Technical Notes
- Module: `web/modules/custom/lms_notification/`
- Storage: `lms_notification_preferences` DB table — columns: uid, event_type (varchar), channel (enum: in_app/email/push), enabled (tinyint)
- OR use a single serialized field on user entity (`field_notif_prefs`) — prefer separate table for queryability
- Form: `NotificationPreferencesForm.php` (extends `AccountForm` or standalone `FormBase`)
- Route: add to `lms_notification.routing.yml`
- Service: `NotificationPreferencesService` — `getPreference(uid, event_type, channel): bool`, `setPreference(...)`, `getDefaults(): array`
- Dispatch service (TASK-009) updated to call `NotificationPreferencesService::getPreference()` before dispatch
- SCSS: `_notification-preferences.scss`

## Dependencies
- TASK-009 (U-042 Notification dispatch service — update to check prefs)
- TASK-040 (Notification center UI — link to preferences from notification bell)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests: `NotificationPreferencesServiceTest` (get/set, defaults on new user, dispatch skip when disabled)
- [ ] Preferences respected in at least one dispatch integration test
- [ ] No new debt introduced
