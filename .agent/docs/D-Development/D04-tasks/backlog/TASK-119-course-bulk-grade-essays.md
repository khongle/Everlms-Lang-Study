# Task: Bulk Essay Grading for Instructors

**ID:** TASK-119
**Unit:** U-018 (lms_quiz)
**Group:** G-CONTENT (extension)
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Allow instructors to grade multiple essay responses for the same question in a single session using a "save and next" workflow. Each response is shown with rubric criteria; the instructor enters a score and optional feedback, saves, and is immediately taken to the next ungraded response. Extends the single-response grading UI from TASK-065.

## Context

- Module: `lms_quiz`
- Essay questions (`question_type = essay`) require manual grading; currently done one at a time
- Bulk workflow: instructor opens a question → sees a queue of ungraded submissions → grades in sequence
- Rubric scoring: configurable point ranges per criterion

## Acceptance Criteria

- [ ] Instructor page at `/admin/lms/quiz/{quiz_id}/grade-essays` listing ungraded essay questions
- [ ] Queue view per question: list of learner submissions sorted by submission date
- [ ] Grading panel: learner response text, rubric criteria with score inputs, feedback textarea
- [ ] "Save & Next" button commits score + feedback and loads next ungraded submission
- [ ] "Skip" button moves to next without saving
- [ ] Progress indicator: "Grading 3 of 17 ungraded"
- [ ] On grading last item in queue: confirmation message "All essays graded for this question"
- [ ] Graded score immediately reflected in learner's quiz result and progress
- [ ] Keyboard shortcut: `Tab` navigates rubric fields; `Enter` on Save & Next

## Technical Notes

- Extend existing essay grading controller introduced in TASK-065
- New route: `lms_quiz.bulk_grade_essays` in `lms_quiz.routing.yml`
- Queue built via entity query on `lms_quiz_response` entities where `graded = 0` and `question_id = X`
- Rubric schema: reuse `field_rubric_criteria` field if it exists, otherwise add to `lms_quiz_question`
- AJAX form submission for Save & Next to avoid full page reload (Drupal AJAX API)
- Update `lms_progress` cascade after grading (same flow as TASK-065)

## Dependencies

- TASK-065 (single essay grading UI, baseline)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
