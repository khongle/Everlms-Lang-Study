# WF-003-001: Passed quiz does not issue a certificate without a quiz-course link

## Status
Done

## Workflow
WF-003 - Certificate issuance

## Evidence
- Maria (`uid=61`) has a passed quiz result for quiz `81` with score `98.00`.
- Maria has no `lms_user_certificate` rows.
- `CertificateEventListener` resolves course only through `lesson.field_quiz -> chapter -> course`.
- Existing final quizzes are not referenced by any lesson `field_quiz`.
- Admin quiz list already expects `lms_quiz.field_course`, but the quiz entity does not define that field.

## Problem
Certificate issuance depends on knowing which course a passed quiz belongs to. Live final quizzes currently have no durable course relationship, so `QuizPassedEvent` cannot issue `lms_user_certificate`.

## Impact
Learners can pass a final quiz and still see an empty `/my-certificates` page. WF-003 fails end to end, and WF-001 course completion expectations cannot reliably trigger credentials.

## Expected
- Each final quiz has a course relationship.
- `QuizPassedEvent` resolves course from the quiz-course field first, then falls back to lesson hierarchy for older/embedded quizzes.
- Certificate issuance remains idempotent: one certificate per learner/course.

## Resolution Plan
- Add `lms_quiz.field_course` as a base entity reference to `lms_course`.
- Backfill the existing generated final quizzes to their corresponding generated courses.
- Update the certificate event subscriber to use `field_course` before lesson hierarchy.
- Run database updates, rebuild cache, then retest `/my-certificates`, certificate download, and public verification.

## Resolution
- Added `lms_quiz.field_course` as a course entity reference.
- Added update hooks `lms_quiz_update_10002` and `lms_quiz_update_10003` to install the field and backfill generated final assessments to generated courses.
- Updated `CertificateEventListener` to use dependency injection and resolve course from `quiz.field_course` before falling back to lesson hierarchy.
- Ran DB updates and cache rebuild.

## Retest
- Retook quiz `81` as Maria and received a new `Passed` result (`lms_quiz_result` id `97`).
- Certificate id `52` was issued for Maria/course `126`.
- Idempotency confirmed: Maria has exactly one certificate for course `126` despite passed quiz results `49` and `97`.
