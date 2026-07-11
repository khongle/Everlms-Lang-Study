# ISSUE-0003 - Badge award logic absent

**State:** open
**Severity:** P1 · **Module:** [lms_badge](../../../C-Design/C05-modules/lms_badge.md) · **Story:** [E05S02-lms_badge](../../../D-Development/D03-stories/E05S02-lms_badge.md) · **Risk:** R-003 · **Task:** [E05S02T01](../../../D-Development/D04-tasks/todo/E05S02T01-implement-badge-award-subscriber.md)

## Summary
`lms_badge`/`lms_user_badge` entities and `field_criteria` exist, but no code evaluates criteria to grant badges — the system never awards a badge, so F-008 (Badges) is present in the data model only.

## Steps to reproduce
1. As a student, complete a course/quiz that carries a badge criterion (e.g. "complete 5 courses").
2. Query `lms_user_badge` for that user (`drush sqlq "SELECT * FROM lms_user_badge WHERE uid=<uid>"` or `/admin/content/lms-user-badge`).
3. Observe zero rows — no badge was ever granted, even though the criterion is met.

## Root cause
No event subscriber/award service exists under `lms_badge/src` to listen for the completion events (course completion, `QuizPassedEvent`) that should trigger criteria evaluation; the badge and user-badge entity types were scaffolded but the award pipeline that connects them to learner activity was never built.

## Fix
Add an event subscriber (course completion / `QuizPassedEvent`) to evaluate `field_criteria` and idempotently create `lms_user_badge`; surface newly-earned badges on the learner profile.

## Regression test
No automated regression test exists yet (tracked under [ISSUE-0004](ISSUE-0004-zero-test-coverage.md) / [X01](../../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md)). Planned: an [E03](../../E03-test-definitions/_index.md) `award-trigger` Kernel test asserting that dispatching `QuizPassedEvent`/course-completion for a user meeting `field_criteria` creates exactly one `lms_user_badge` row (idempotent on repeat dispatch).
