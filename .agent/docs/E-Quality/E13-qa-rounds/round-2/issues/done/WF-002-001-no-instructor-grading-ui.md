---
id: WF-002-001
workflow: WF-002
title: No instructor grading UI — quizzes with essays stay Pending forever and block certificates
severity: high
category: functionality
status: done
roles: instructor, manager, student
source: round-2 real-session QA
found: 2026-07-11
---

# WF-002-001 - Pending quiz results could never be finalised (missing grading UI)

## Problem
`lms_quiz` shipped only take / submit / view-result / my-results routes. There
was **no route or form for an instructor to grade responses left as "Pending
Manual Review"** by `EssayStrategy` / `ShortAnswerStrategy`. The `QuizResult`
entity declared a default form handler but exposed no add/edit route.

Consequence: any quiz containing an essay lands as `field_result = Pending`
(term) and **can never be finalised**, so:
- the learner's provisional score is frozen forever,
- `QuizPassedEvent` (WF-002 → WF-003) never fires,
- certificates for essay-based final assessments are never issued.

This contradicts C12 WF-002 ("Responsible manual grade: instructor for pending
essays; Approver: instructor confirms/overrides scores").

## Expected
An instructor (course owner / listed `field_instructor`) or a course admin can
open a pending submission, score each pending response, and finalise it —
recomputing pass/fail against `field_threshold` and firing `QuizPassedEvent` for
the **learner** (result owner) on a pass.

## Evidence
- Result 98 (maria.santos, quiz 43, course 88) submitted with an essay → term
  `Pending`, score 82.35, no way to finalise. `04-student-quiz-result-pending-review.png`.

## Fix (implemented)
- New `QuizGradeForm` + route `lms_quiz.grade` (`/manage/quiz-results/{id}/grade`),
  custom access = `administer lms_course` OR course owner OR listed instructor.
- Form lists each pending response (question, learner answer, points ≤ max,
  feedback), clears `field_pending_review`, recomputes the percentage with
  `GradingStrategyFactory::getMaxScore`, sets the `Passed`/`Failed` term, and
  fires `QuizPassedEvent($result, $quiz, $resultOwner)` on a pass.
- "Grade this submission" CTA rendered on the result page for graders
  (`can_grade`/`grade_url` added to the theme + controller).

## Verification
- sabine.mueller (uid 56, instructor of course 88) opened `/manage/quiz-results/98/grade`,
  awarded full marks, submitted → result 98 finalised **Passed (100%)**.
- `QuizPassedEvent` issued certificate `FDB2B9BA40` for maria (uid 61) course 88.
- Screens `06/07/08-instructor-*` in the WF-002 folder.

## Resolution (2026-07-11)
Implemented `QuizGradeForm` + route `lms_quiz.grade` + result-page CTA. Verified end-to-end: result 98 Pending -> instructor graded -> Passed 100% -> certificate FDB2B9BA40 issued (idempotent).
