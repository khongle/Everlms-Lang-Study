# Task: Microlearning Bites

**ID:** TASK-231
**Unit:** U-041 (lesson)
**Group:** G-CONTENT (extension)
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Introduce a new lesson sub-type "bite" for microlearning content: short, self-contained lessons ≤5 minutes. Bites use a mobile-optimized format with chunked content (text/image/video), a mandatory check quiz after each chunk, and an optional daily reminder push notification to prompt users to complete their daily bite.

## Context
- `lms_lesson` already supports multiple lesson types (video, text, quiz, assignment); "bite" is a new type.
- G-CONTENT entities done; TASK-115–122 handle existing lesson edge cases.
- TASK-232 (flashcards) and TASK-233 (adaptive difficulty) are complementary microlearning features.

## Acceptance Criteria
- [ ] `lms_lesson` gains a new bundle/type `lesson_bite` selectable from the lesson type picker on course edit
- [ ] Bite lesson edit form: add content in sequential chunks (text block, image, or short video ≤5 min); each chunk has an optional inline quiz question
- [ ] `field_bite_chunks` — paragraph field supporting chunk paragraph type with sub-fields: `chunk_body` (text), `chunk_media` (image/video), `chunk_quiz` (quiz question, optional)
- [ ] Bite lesson player: renders chunks one at a time with a "Next" button; quiz must be answered correctly before advancing
- [ ] Bite player is mobile-first: single-column, large tap targets, swipe-to-advance gesture support
- [ ] Course outline shows bite lessons with a clock icon and "≤5 min" duration indicator
- [ ] Daily reminder push notification (web push) sent at user's preferred time if they have an incomplete bite for today; push uses `lms_notification` push channel
- [ ] Progress tracking: bite marked `complete` only when all chunks + quizzes in sequence are done
- [ ] Bite lessons count toward course progress and certificate eligibility same as other lesson types

## Technical Notes
- Module: `lms_lesson` (new bundle, paragraph type, player template, progress integration)
- New paragraph type: `lesson_bite_chunk` with fields `field_chunk_body`, `field_chunk_media`, `field_chunk_quiz_nid` (reference to `lms_quiz` question, optional)
- Player: `lms_lesson/templates/lesson-bite-player.html.twig` + `lms_lesson/js/bite-player.js`
- Mobile gestures: Hammer.js (already in project) or CSS `scroll-snap-type`
- Push notification: `lms_notification` push channel daily cron — query users with in-progress bite lessons, send push at `user_pref.push_time` (default 09:00 local)
- Duration enforcement: `BiteValidator::validate()` checks sum of video durations per bite ≤ 300 seconds on save

## Dependencies
- TASK-115 (lesson edge cases — progress integration compatibility)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: duration validator; functional: chunk sequence, quiz gate, progress completion)
- [ ] No new debt introduced
