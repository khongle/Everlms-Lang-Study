# ISSUE-0003 - Badge award logic absent

**Severity:** P1 · **Status:** open · **Risk:** R-003 · **Task:** [E05S02T01](../../../D-Development/D04-tasks/todo/E05S02T01-implement-badge-award-subscriber.md)

## Description
`lms_badge`/`lms_user_badge` entities and `field_criteria` exist, but no code evaluates criteria to grant badges — the system never awards a badge.

## Impact
F-008 non-functional; gamification absent; `lms_user_badge` stays empty.

## Evidence
No event subscriber/award service in `lms_badge/src`.

## Fix
Add an event subscriber (course completion / QuizPassedEvent) to evaluate `field_criteria` and idempotently create `lms_user_badge`; surface on profile.
