# Task: Learning Streaks

**ID:** TASK-142
**Unit:** — (new module `lms_streak`)
**Group:** G-GAMIFICATION
**Priority:** P3
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Track consecutive days of learning activity per user. A day counts if the user completes at least one lesson (or a configurable minimum duration). Display the current streak count and a flame icon on the dashboard and user profile. Allow one "streak freeze" per week. Missing a day resets the streak. Streak milestones feed into the badge award rules (TASK-063).

## Context
- `lms_progress` fires events on lesson completion; `lms_streak` listens to these.
- Badge milestones (7-day, 30-day, 100-day streaks) are configured in `lms_badge` admin (TASK-063).
- Streak freeze is a forgiving mechanic: one free pass per calendar week, auto-consumed on a missed day.

## Acceptance Criteria
- [ ] Daily activity recorded per user when at least 1 lesson is completed that calendar day (site timezone)
- [ ] Streak count = number of consecutive calendar days with activity up to and including today
- [ ] Streak freeze: each user accumulates 1 freeze per calendar week (max 1 held at a time); auto-consumed on first missed day; not consumed for subsequent consecutive misses
- [ ] Streak resets to 0 if no freeze available and a day is missed
- [ ] Current streak + best streak stored per user; displayed on dashboard card and `/user/{uid}` profile
- [ ] Flame icon (Bootstrap icon or SVG) shown next to streak count; animated on new streak increment
- [ ] Streak milestones (7, 30, 100 days) dispatch an event that `lms_badge` can listen to for awarding badges
- [ ] Admin config: minimum lessons per day (default 1), freeze accrual rule (default 1/week)
- [ ] Cron job nightly checks for streak breaks and updates streak records

## Technical Notes
- New module: `web/modules/custom/lms_streak/`
- DB table: `lms_streak` — (uid, current_streak, best_streak, last_activity_date, freeze_available, updated)
- Event subscriber: listens to `lms_progress.lesson_completed` event; calls `StreakService::recordActivity(uid)`
- `StreakService::recordActivity()`: checks last_activity_date, increments or resets streak, awards freeze on week boundary
- Cron worker: `StreakCronWorker` — iterates users active in past 2 days, checks for missed day, applies freeze or reset
- Drupal event: dispatches `lms_streak.milestone` on 7/30/100 day hits
- Config: `lms_streak.settings` — `min_lessons_per_day`, `freeze_per_week`
- SCSS: `_streak.scss` — flame icon, counter badge

## Dependencies
- TASK-063 (Admin badges CRUD — listens to `lms_streak.milestone` for badge awards)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests: `StreakServiceTest` (increment, freeze consumption, reset, milestone dispatch)
- [ ] Cron worker tested with simulated date rollover (mocked `time()`)
- [ ] No new debt introduced
