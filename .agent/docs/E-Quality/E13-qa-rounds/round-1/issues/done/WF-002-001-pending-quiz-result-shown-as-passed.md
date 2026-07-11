# WF-002-001: Pending quiz result is shown as Passed

## Status
Done

## Workflow
WF-002 - Quiz attempt and grading

## Evidence
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/002-quiz-attempt-and-grading/004-student-quiz-result-pending-review.png`
- Screenshot: `.agent/docs/E-Quality/E00-workflows/round-1/_screens/002-quiz-attempt-and-grading/005-student-my-quiz-results.png`
- DB check: latest Maria quiz 43 result is `Pending`, score `82.35`, with `15` question responses.

## Problem
The quiz result contains an essay response that requires manual review, so the backend stores the quiz result taxonomy as `Pending`.

The learner-facing result page and quiz history page still display the attempt as `Passed` because they infer pass/fail from score thresholds instead of respecting the stored result state.

## Impact
Learners can believe they have passed an assessment before instructor review is complete. This breaks WF-002, misrepresents certification readiness, and can trigger inconsistent downstream expectations.

## Expected
- `Pending` result term displays as `Pending Review` on the result page.
- Pending responses are styled as review items, not incorrect failures.
- `/my-quiz-results` counts and lists pending results separately from passed attempts.
- Pass/fail display should only use the stored result term after grading state is finalized.

## Resolution Plan
- Update quiz result template to render `Pending`, `Passed`, and `Failed` from `result_label`.
- Update quiz history controller to derive state from `field_result` taxonomy first.
- Retest WF-002 screenshots and DB state after cache rebuild.

## Resolution
- `lms-quiz-result.html.twig` now renders `Pending Review`, `Passed`, or `Failed` from the stored result label instead of inferring from score.
- Pending question responses are styled as review items instead of incorrect failures.
- `QuizController::myResults()` now derives row state and summary counts from `field_result` first.
- `/my-quiz-results` now shows separate `Passed`, `In review`, and `Retry` counts.

## Retest
- Rebuilt Drupal cache.
- Recaptured result page for quiz result `96`.
- Recaptured `/my-quiz-results`.
- Visual check confirms the latest quiz result remains `Pending Review` in both pages.
