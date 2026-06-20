# Task: Welcome Email Drip Series

**ID:** TASK-222
**Unit:** U-048 (notification)
**Group:** G-ENGAGEMENT
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement a 3-email drip series automatically sent to every new user: Day 0 (email verification + welcome), Day 3 (explore catalog), Day 7 (first-course recommendation based on signup intent). Emails are personalised using the user's signup intent field and queued via Drupal's Queue API so delivery is decoupled from the registration request.

## Context
- Existing onboarding: WF-007 (user registration flow), TASK-059 (auth pages UI).
- `lms_notification` module exists with email service and twig-templated mail.
- Intent field (`field_signup_intent`) must exist on the User entity (add if absent).

## Acceptance Criteria
- [ ] On `UserRegistrationEvent` (or `hook_user_insert`), three queue items are created with delays: 0h, 72h, 168h
- [ ] Cron processes queued items only when their scheduled send timestamp has passed
- [ ] Email 1 — Day 0: subject "Welcome to EverLMS!", body includes email-verify CTA + platform intro; sent immediately
- [ ] Email 2 — Day 3: subject "Explore our course catalog", body includes 3 featured courses pulled from `lms_course` catalog; skipped if user has already enrolled in a course
- [ ] Email 3 — Day 7: subject "Your first recommended course", body includes 1 course matched to `field_signup_intent` category; skipped if user has completed lesson 1 of any course
- [ ] Each email has a one-click unsubscribe link that sets `lms_notification_pref.welcome_series = false`
- [ ] Admin UI at `/admin/lms/notifications/welcome-series` to preview/disable the series
- [ ] All emails pass HTML/CSS inline check (tables, max-width 600px, alt text on images)

## Technical Notes
- Modules: `lms_notification` (extend), `lms_base` (Queue integration)
- New queue: `lms_welcome_series_queue`; worker: `WelcomeSeriesQueueWorker` (plugin, `@QueueWorker` annotation)
- Queue item payload: `{ uid, step (1|2|3), send_after (timestamp) }`
- Email templates: `lms_notification/templates/email/welcome-series-{1,2,3}.html.twig`
- Course selection for Email 2: `lms_course` entity query, sort by enrollment count DESC, limit 3
- Course selection for Email 3: match `field_signup_intent` taxonomy term to `field_course_category`; fallback to highest-rated course
- Skip logic: check progress records in `lms_progress` before sending Email 2/3

## Dependencies
- TASK-059 (auth pages — registration form where intent field lives)
- None blocking (lms_notification already exists)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: queue item creation; functional: skip logic)
- [ ] No new debt introduced
