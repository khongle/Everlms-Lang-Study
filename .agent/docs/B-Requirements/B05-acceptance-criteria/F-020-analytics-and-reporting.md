# F-020 Acceptance Criteria - Analytics & reporting

> ⚠️ As-built coverage is partial; depth confirmed in E06.

## Core criteria
1. **Given** a manager role, **when** they open the analytics dashboard, **then** enrollment/completion/revenue summaries render from live data.
2. **Given** an instructor role, **when** they open analytics, **then** they see data scoped to their own courses.
3. **Given** quiz results exist, **when** the pass-rate report runs, **then** it reflects `lms_quiz_result` data.
4. **Given** a non-privileged user, **when** they hit an analytics route, **then** access is denied.

## Evidence expected
- Module: [lms_analytics](../../C-Design/C05-modules/lms_analytics.md).
- Test: role-scoped access test; report-accuracy spot check vs DB; gaps logged in [E06](../../E-Quality/E06-implementation-status/_index.md).
