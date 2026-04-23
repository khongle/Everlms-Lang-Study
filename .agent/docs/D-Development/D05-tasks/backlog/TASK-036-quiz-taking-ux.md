# Task: Quiz Taking UX

**ID:** TASK-036
**Unit:** U-074
**Group:** G-UI-POLISH
**Priority:** P1
**Estimate:** 10h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Cải thiện UX quiz taking: timer, question navigation, progress bar, review before submit, result display.

## Acceptance Criteria
- [ ] Timer countdown (nếu quiz có time limit)
- [ ] Question navigation sidebar (numbered, status icons)
- [ ] Progress bar (answered/total)
- [ ] Mark for review functionality
- [ ] Review page trước khi submit (summary of answers)
- [ ] Auto-submit khi hết thời gian
- [ ] Result page: score, correct/wrong breakdown
- [ ] Responsive layout

## Technical Notes
- Module: `lms_quiz` (QuizTakeController exists)
- Templates: `lms-quiz-take.html.twig`, `lms-quiz-result.html.twig`
- JS: timer, navigation, auto-submit
- SCSS: `_quiz.scss`

## Dependencies
- None (existing scaffold)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Timer + auto-submit functional
- [ ] Mobile responsive verified
