# Task: Drip Content Release Schedule

**ID:** TASK-228
**Unit:** U-041 (lesson)
**Group:** G-CONTENT (extension)
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Allow instructors to configure a drip release schedule for lesson or chapter visibility within a course. Supported modes: (1) offset-based — unlock N days after enrollment, (2) fixed date — unlock on a specific calendar date, (3) interval — unlock one lesson/chapter per day or week. Locked lesson cards show the unlock date in the UI. Per-cohort overrides are supported (extends TASK-168).

## Context
- G-CONTENT entities done; lesson and course entities exist.
- TASK-168 (cohort courses) defined cohort model; drip schedule must integrate with cohort enrollment offset.
- TASK-115–122 cover edge cases in existing content flow.

## Acceptance Criteria
- [ ] Each lesson node has a new `field_drip_mode` (select: none | offset | fixed_date | interval) and `field_drip_value` (integer days or timestamp)
- [ ] Course edit form has a "Drip Schedule" tab with per-lesson or per-chapter drip configuration
- [ ] Access check on lesson view: if drip date not yet reached for current user enrollment, return 403 + redirect to course outline with locked state
- [ ] Locked lesson card in course outline shows padlock icon + "Unlocks on {date}" formatted string
- [ ] Cohort enrollment offset: when a cohort has a `start_date`, offset-based drip is calculated from that date, not individual enrollment date
- [ ] Daily cron sends a "Your lesson is unlocked" email to users whose next lesson unlocks today (uses `lms_notification`)
- [ ] Admin UI at `node/{nid}/drip` for per-lesson schedule editing
- [ ] Schedule is importable/exportable via the Drupal config export system (config entity, not content entity)

## Technical Notes
- Modules: `lms_lesson` (field definitions, access check), `lms_progress` (unlock date resolution), `lms_base` (cron for unlock notification)
- Access check: extend `LessonAccessControlHandler::checkAccess()` — inject `DripScheduleService::isUnlocked(uid, lid)`
- `DripScheduleService`: resolves effective unlock timestamp per user per lesson considering drip mode, enrollment date, and cohort start date
- Locked state rendering: `lms_lesson/templates/components/_lesson-card-locked.html.twig` passed via `LessonListBuilder` preprocess
- DB: no new table; drip fields stored as standard Drupal field storage on `lms_lesson` entity type
- Cron: `lms_base_cron()` → `DripNotificationService::sendTodayUnlocks()` — query lessons unlocking today, batch notify enrolled users

## Dependencies
- TASK-168 (cohort courses — cohort start date integration)
- TASK-115 (lesson edge cases — access check must not conflict)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: DripScheduleService unlock date math; functional: locked/unlocked lesson access)
- [ ] No new debt introduced
