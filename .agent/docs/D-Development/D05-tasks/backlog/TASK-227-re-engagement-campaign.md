# Task: Re-Engagement Email Campaign

**ID:** TASK-227
**Unit:** U-048 (notification)
**Group:** G-ENGAGEMENT
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement a 3-email re-engagement series targeting users who have been inactive (no login) for 30, 60, and 90 days. Email 1 (30d): "We miss you — here's what's new". Email 2 (60d): "Courses you haven't finished". Email 3 (90d): "Last chance — special offer + unsubscribe option". Users can opt out from any email; opted-out users are excluded from all future re-engagement sends.

## Context
- `lms_notification` module exists.
- Extends the Queue-based approach introduced in TASK-222 (welcome series).
- Drip logic is driven by cron, not message queues, because the trigger is absence-based (no activity) rather than event-based.

## Acceptance Criteria
- [ ] Daily cron job (`lms_reengagement_cron`) runs a DB query to find users with `last_login < NOW() - INTERVAL 30 DAY AND last_login >= NOW() - INTERVAL 31 DAY` and queues Email 1 for each
- [ ] Same cron handles 60d (Email 2) and 90d (Email 3) windows
- [ ] Email 1: subject "We miss you!", body includes 3 recently-added courses from `lms_course`
- [ ] Email 2: subject "Pick up where you left off", body includes user's in-progress courses from `lms_progress`; if no in-progress courses, send top 3 by category match
- [ ] Email 3: subject "Final nudge — a gift for you", body includes discount coupon code (from `lms_coupon`) + prominent unsubscribe link
- [ ] Each email footer contains one-click unsubscribe link → sets `lms_notification_pref.reengagement = false`; opted-out users excluded from all three tiers
- [ ] Users who log in before Email 2/3 send date are automatically excluded (check last_login before send)
- [ ] Admin report at `/admin/lms/notifications/reengagement` shows: queue depth, sent count per tier, opt-out count

## Technical Notes
- Modules: `lms_notification` (extend), `lms_base` (cron hook)
- Cron hook: `lms_base_cron()` → calls `ReEngagementCronService::run()`
- Query: `SELECT uid FROM {users_field_data} WHERE login < :threshold AND login > :window_start AND status = 1`
- Queue worker: `ReEngagementQueueWorker` (same pattern as TASK-222's `WelcomeSeriesQueueWorker`)
- Templates: `lms_notification/templates/email/reengagement-{30,60,90}.html.twig`
- Opt-out storage: `lms_notification_pref` table, column `reengagement` boolean (extend schema)
- Coupon for Email 3: auto-generate a single-use 10% coupon via `lms_coupon` service if `lms_coupon` module enabled

## Dependencies
- TASK-222 (welcome email series — establishes Queue pattern and pref storage)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: window boundary query; functional: opt-out skips sends)
- [ ] No new debt introduced
