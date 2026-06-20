# Task: Cohort-Based Courses

**ID:** TASK-230
**Unit:** U-040 (course)
**Group:** G-CONTENT (extension)
**Priority:** P2
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Extend the cohort model introduced in TASK-168 to support full cohort-based learning: a fixed start date when all enrolled students begin together, a shared cohort discussion forum thread, and scheduled weekly live class sessions (via TASK-161 Zoom integration). The cohort lifecycle progresses through three states: `open` (accepting enrollments) → `active` (in-progress) → `completed`.

## Context
- TASK-168 defined the cohort entity and enrollment relationship; this task adds the lifecycle, forum, and live class integration.
- TASK-161 handles Zoom meeting creation; cohort courses use it to schedule recurring weekly meetings.
- TASK-228 (drip content) integrates with cohort start date for offset-based drip.

## Acceptance Criteria
- [ ] Cohort entity gains a `status` field (select: open | active | completed) with admin UI to transition
- [ ] Cron auto-transitions: `open` → `active` on `start_date`; `active` → `completed` when all lessons marked complete for all enrolled users (or after `end_date`)
- [ ] Course landing page shows cohort status badge and seats-remaining count when `cohort_capacity` is set
- [ ] Enrollment is blocked when cohort status is `completed` or seats are full
- [ ] On cohort activation, a group forum thread is auto-created in `lms_forum` for that cohort with title "Cohort {name} Discussion"
- [ ] On cohort creation (status set to `open`), a recurring Zoom meeting is scheduled via TASK-161 integration: weekly on the configured day/time, for the cohort duration
- [ ] Cohort dashboard page at `/cohort/{cid}` shows: participants list, forum thread link, Zoom meeting schedule, drip schedule progress
- [ ] Email notification sent to all enrolled users 24h before cohort `start_date` (via `lms_notification`)
- [ ] Instructors can export cohort completion report as CSV

## Technical Notes
- Modules: `lms_course` (cohort entity extension), `lms_zoom` (meeting creation), `lms_forum` (thread auto-creation), `lms_notification` (start reminder email)
- Cohort entity update: add `status` (list_string field), `capacity` (integer field), `end_date` (datetime field)
- `CohortLifecycleService`: handles status transition logic; called from cron and admin form submit
- Zoom integration: `CohortZoomService::scheduleWeeklyMeetings(Cohort $cohort)` — calls TASK-161's `ZoomMeetingService::createRecurring()`
- Forum thread: call `lms_forum` `ForumThreadService::createCohortThread(Cohort $cohort)` on cohort save with status = open
- Access: cohort dashboard accessible to enrolled users and instructors of that course

## Dependencies
- TASK-168 (cohort entity — base model)
- TASK-161 (Zoom integration — live class scheduling)
- TASK-228 (drip content — cohort start date integration)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: lifecycle transitions; functional: enrollment blocking when full/completed)
- [ ] No new debt introduced
