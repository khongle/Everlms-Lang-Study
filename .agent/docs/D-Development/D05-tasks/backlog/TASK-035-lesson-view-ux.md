# Task: Lesson View UX Improvements

**ID:** TASK-035
**Unit:** U-073
**Group:** G-UI-POLISH
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Cải thiện UX cho lesson view: video player controls, sidebar navigation, progress indicator, notes panel, next/prev navigation.

## Acceptance Criteria
- [ ] Lesson sidebar: curriculum tree với current lesson highlighted
- [ ] Progress indicator: lesson completion checkbox, course %
- [ ] Video player: progress tracking, resume position, speed control
- [ ] Next/Previous lesson navigation
- [ ] Notes panel (slide-in, timestamp-linked cho video)
- [ ] Mark as complete button
- [ ] Responsive layout (collapse sidebar on mobile)
- [ ] Keyboard shortcuts (next, prev, toggle sidebar)

## Technical Notes
- Module: `lms_lesson` (LessonController exists)
- Template: `lms-lesson-view.html.twig`
- JS: lesson progress tracking, video events
- SCSS: `_lesson.scss`
- Integration: `lms_note`, `lms_progress`

## Dependencies
- None (existing scaffold)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Video progress tracking functional
- [ ] Mobile responsive verified
