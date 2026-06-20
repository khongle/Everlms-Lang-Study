# ISSUE-013 — lms_notification: Email Channel Only Logs; Push Channel is a Stub (FCM Foundation Only)

| Field | Value |
|---|---|
| ID | `ISSUE-013` |
| Title | lms_notification email channel only logs (TODO comment); push channel is a stub — neither delivers notifications |
| Source | G4-Architecture / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `MEDIUM` |
| Affected page / route | N/A (affects backend dispatch only); user-visible at `/notifications` when `field_channel` includes `email` or `push` |
| Owning Epic | E-04 (Communication) — |
| Owning Story | [`lms_notification`](../../../D-Development/D03-stories/lms_notification.md) |
| Owning module(s) | `lms_notification` |

## Summary

`NotificationManager::dispatch()` routes outgoing notifications by channel (`in_app`, `email`, `push`). The **in_app** channel is fully implemented: it creates and persists an `lms_notification` entity which is then displayed via `NotificationController::listNotifications()` and the `NotificationBellBlock`. However, the `email` and `push` channels are non-functional stubs:

**Email channel (`in_app` ≠ `email`):**
```php
// In NotificationManager::dispatch() for 'email' channel:
$this->logger->info('TODO: Implement Drupal queue creation for mail');
// No drupal mail plugin call, no queue item, no Drupal mail() invocation
```
Email notifications are silently dropped — an info log entry is written, but no email is ever sent. The `NotificationPreferencesForm` exposes "Email" checkboxes for 9 event types; users who opt in to email receive nothing.

**Push channel:**
```php
// In NotificationManager::dispatch() for 'push' channel:
$this->logger->info('FCM foundation comment');
// No HTTP call to FCM, no device token lookup, no push delivery
```
Push notification delivery is entirely absent. The `field_channel` field supports `push` as an allowed value, but the channel handler is a one-liner log entry only.

The practical impact: quiz-pass notifications (`NotificationEventSubscriber::onQuizPassed()`), forum-reply notifications (from `ForumReplyForm::save()`), and broadcast announcements (`BroadcastForm::processBatch()`) all call `dispatch()` — but only in_app delivery works. Any user who has enabled email or push preferences receives no notification via those channels.

## Reproduction

1. Enable email and push preferences for a test user at `/manage/settings/notifications`.
2. Complete a quiz (triggers `QuizPassedEvent` → `NotificationEventSubscriber::onQuizPassed()` → `dispatch('quiz_result', ..., channels=['in_app','email'])`).
3. Check the user's email inbox — no email received.
4. Check Drupal watchdog (`drush watchdog:show`) — an info entry "TODO: Implement Drupal queue creation for mail" appears.
5. Check push notification on any device — nothing received.

Confirm in source:
```bash
grep -n "TODO\|logger\->info\|push\|email" web/modules/custom/lms_notification/src/NotificationManager.php
```

## Root Cause

`NotificationManager` was built with in_app delivery as the MVP channel; email and push were intentionally deferred with TODO comments. No Drupal queue worker for mail delivery was ever implemented, and no FCM (Firebase Cloud Messaging) integration was built. The `field_channel` entity field and the user preferences form were scaffolded ahead of the actual delivery implementations.

Source file:
- `web/modules/custom/lms_notification/src/NotificationManager.php` — `dispatch()` method, email and push branches

## Resolution (filled at RESOLVED)

**Email delivery:**
1. Implement Drupal mail queue worker in `lms_notification`:
   - In `dispatch()` for `email` channel: call `\Drupal::queue('lms_notification_mail')->createItem(['uid'=>$uid, 'title'=>$title, ...])`.
   - Add `QueueWorker` plugin (`src/Plugin/QueueWorker/NotificationMailWorker.php`) that calls `\Drupal::service('plugin.manager.mail')->mail(...)` using `hook_mail` key `lms_notification_email`.
   - Register `hook_mail` in `lms_notification.module`.
2. Alternatively, use the Drupal `swiftmailer` or `symfony_mailer` contributed module.

**Push delivery:**
1. Integrate FCM (Firebase Cloud Messaging) HTTP v1 API:
   - Store FCM server key via Drupal Key module (reference ISSUE-001 for secrets management).
   - Add `lms_fcm_device_token` entity or key_value store for per-user device tokens.
   - In `dispatch()` for `push` channel: POST to FCM API with device token and payload.
2. Implement device token registration endpoint in `lms_mobile_api` (or as a new route in `lms_notification`).

Until implemented, the `email` and `push` channel options should be hidden from the preferences form or marked "(coming soon)" to avoid misleading users.

## Trace links

- **Story:** `D-Development/D03-stories/lms_notification.md`
- **C05 module spec:** `C-Design/C05-modules/lms_notification.md`
- **Facts base:** `modules-progress-comms.md` §lms_notification §NotificationManager — "KEY ISSUE: Email notifications are NOT functional — only an info log is written. Push notifications are NOT functional — only an info log is written."
- **Related:** ISSUE-001 (secrets management — FCM server key must use Key module, not config YAML)
- **K04:** Reusable rule — stub channel handlers that silently drop notifications create false confidence; mark stub channels as explicitly disabled (return early with logged warning) rather than accepting the call and doing nothing.
