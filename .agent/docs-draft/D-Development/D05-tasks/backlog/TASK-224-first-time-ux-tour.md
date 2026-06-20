# Task: First-Time UX Tour for Student Dashboard

**ID:** TASK-224
**Unit:** U-055 (theme)
**Group:** G-ONBOARDING
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement a first-time overlay tour (Joyride/Intro.js style) for new students after their first login. The tour highlights five key areas: My Learning dashboard, Browse Courses, Enroll flow, Take a Lesson, and the Help menu. The tour is dismissable at any point, and can be replayed from the Help menu. Tour completion is persisted so it does not re-trigger.

## Context
- Existing onboarding: WF-007 (user registration), TASK-059 (auth pages), TASK-222 (welcome emails), TASK-223 (instructor wizard).
- No student-facing interactive tour exists today.
- Tour library: prefer lightweight vanilla JS (Intro.js BSD-licensed) rather than a heavy framework.

## Acceptance Criteria
- [ ] Tour automatically starts for any user with `student` role who has never completed it (flag checked via `lms_base` user pref)
- [ ] Tour has exactly 5 steps: (1) My Learning widget, (2) Course catalog link, (3) Enroll button on a sample course card, (4) Lesson player area, (5) Help menu
- [ ] Each step shows: tooltip with title + body text, step counter ("2 of 5"), Next / Back / Skip buttons
- [ ] "Skip" dismisses tour permanently; "Done" on step 5 marks tour complete
- [ ] Help menu item "Replay tour" re-triggers the tour from step 1 regardless of completion flag
- [ ] Tour does not trigger on mobile widths < 768px (too small for overlay pointers); show a one-time banner instead
- [ ] Tooltip pointer positions are CSS-configurable per step via data attributes (no hardcoded pixel offsets)
- [ ] Tour overlay passes keyboard navigation: Tab cycles through tooltip controls; Esc = Skip

## Technical Notes
- Module: `lms_theme` (JS asset + SCSS) plus a small hook in `lms_base` for the pref flag
- JS library: Intro.js (or equivalent) added to `lms_theme.libraries.yml` as a local vendor asset (not CDN)
- Tour definition: `lms_theme/js/student-tour.js` — array of step configs referencing CSS selectors; conditionally loaded only when `drupalSettings.lmsTour.showStudentTour === true`
- Pref flag: `lms_user_pref.student_tour_done` (boolean) set via AJAX endpoint `POST /api/lms/user-pref/student-tour-done`
- SCSS: `lms_theme/scss/components/_tour.scss` for tooltip styling matching site design system

## Dependencies
- TASK-059 (auth pages — first login redirect to dashboard triggers tour)
- TASK-222 (welcome email — Day 0 email references the tour)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: pref flag endpoint; FE: tour triggers on first login, not on second)
- [ ] No new debt introduced
