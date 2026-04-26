# Task: Weekly Email Digest

**ID:** TASK-139
**Unit:** — (extend `lms_notification`)
**Group:** G-COMMUNICATION (extension)
**Priority:** P2
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Send each user a personalized weekly email digest every Monday morning. The digest summarises the past week: learning progress, new courses matching their interests, forum activity highlights, certificates earned, and course recommendations. Users can unsubscribe via a one-click link (GDPR-aligned with TASK-062).

## Context
- `lms_notification` provides the email channel and dispatch service (TASK-009, TASK-011).
- Progress data from `lms_progress`; certificate data from `lms_certificate`.
- Personalization is lightweight: filter new courses by enrolled categories; forum highlights = top 3 posts by reply count in the past 7 days.
- TASK-062 handles GDPR consent storage; digest respects the `email_digest` opt-in flag.

## Acceptance Criteria
- [ ] Cron job runs every Monday 07:00 site timezone; Drupal `hook_cron` + Queue API for per-user batch
- [ ] Digest email contains: greeting, weekly progress bar (lessons completed this week), new courses (up to 3), forum highlights (up to 3 threads), certificates earned this week, recommended courses (up to 3 by category match)
- [ ] "Recommended" section omitted if no match found
- [ ] Each section has a CTA link back to the relevant page on the LMS
- [ ] One-click unsubscribe link in footer sets `email_digest = FALSE` on the user's preference record without requiring login
- [ ] Unsubscribe token is unique per user, signed with Drupal's `CsrfTokenGenerator` equivalent (HMAC)
- [ ] Users with zero activity in the past 30 days receive a shorter "we miss you" variant
- [ ] Admin can trigger a test digest for any user from `/admin/lms/notifications/digest-test`
- [ ] Respects TASK-140 `email_digest` channel toggle

## Technical Notes
- Module: `web/modules/custom/lms_notification/` (extend)
- New queue worker: `WeeklyDigestQueueWorker` (`@QueueWorker` plugin)
- `hook_cron`: enqueue one item per active user with `email_digest = TRUE`
- Service: `DigestBuilderService` — assembles digest data per user (progress, new courses, forum, certs, recs)
- Twig: `email-digest-weekly.html.twig` — responsive email HTML (inline CSS, tested for Gmail/Outlook)
- Unsubscribe route: `GET /unsubscribe/digest/{uid}/{token}` — validates HMAC, sets preference, shows confirmation page
- Config: `lms_notification.settings` — `digest_send_day`, `digest_send_hour`
- SCSS: not needed (email inline CSS only)

## Dependencies
- TASK-009 (U-042 Notification dispatch service)
- TASK-011 (U-044 Email notification integration)
- TASK-040 (Notification center UI — link back from digest)
- TASK-062 (GDPR — respect email_digest consent flag)
- TASK-140 (Notification preferences UI — email_digest toggle)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests: `DigestBuilderServiceTest` (data assembly), queue worker test (batch dispatch), unsubscribe token validation
- [ ] Test digest admin page functional
- [ ] Unsubscribe verified end-to-end (sets flag, shows confirmation)
- [ ] No new debt introduced
