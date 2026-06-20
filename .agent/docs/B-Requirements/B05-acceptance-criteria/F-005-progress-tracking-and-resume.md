# F-005 Acceptance Criteria - Progress tracking & resume

## Core criteria
1. **Given** a learner completing lessons, **when** each lesson is finished, **then** `lms_user_course` percent-complete and last-accessed update accordingly.
2. **Given** a partially-complete course, **when** the learner returns, **then** "Resume" opens the last-accessed lesson.
3. **Given** all required lessons complete and the final quiz passed, **when** the rollup recomputes, **then** the course is marked complete (enabling F-007).
4. **Given** progress data, **when** queried by the dashboard or mobile API, **then** the same percent is returned on both surfaces.

## Evidence expected
- Module: [lms_progress](../../C-Design/C05-modules/lms_progress.md).
- Test: progress-rollup kernel test; resume-link functional test; web/mobile parity check (E03).
