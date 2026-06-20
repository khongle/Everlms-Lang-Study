# F-003 Acceptance Criteria - Lesson delivery & media

## Core criteria
1. **Given** an enrolled learner, **when** they open a lesson, **then** body text, video, and attachments (`field_attachments`) render in the player.
2. **Given** a lesson, **when** the learner marks it complete, **then** a `lms_user_lesson` record is created/updated and the next lesson is reachable.
3. **Given** a non-enrolled user, **when** they try to open lesson content, **then** access is denied / a CTA to enroll is shown (gating via F-004).
4. **Given** a lesson with an attachment, **when** the learner downloads it, **then** the file is served (referenced file, not inlined).

## Evidence expected
- Module: [lms_lesson](../../C-Design/C05-modules/lms_lesson.md); player page in C11.
- Test: lesson-access test (enrolled vs not); complete-lesson progress test (E03).
