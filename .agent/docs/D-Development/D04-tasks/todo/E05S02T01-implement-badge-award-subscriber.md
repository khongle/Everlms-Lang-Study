---
id: E05S02T01
title: Implement badge award subscriber
status: todo
story: E05S02
owning_modules: [lms_badge]
risk: R-003
adequacy: NOT_RUN
---

# E05S02T01 - Implement badge award subscriber

## Goal
Grant `lms_user_badge` automatically when a learner meets a badge's `field_criteria` (R-003 / BLK-003, P1).

## Steps
1. Define criteria evaluation (course completion, quiz mastery, streaks).
2. Add event subscriber (on completion/QuizPassedEvent) to evaluate + grant idempotently.
3. Surface earned badges on profile/dashboard.

## Acceptance / gates
- Meeting a milestone grants the badge once (no duplicates); shown on profile.
- G3: award-trigger + idempotency tests.

## Links
[story](../../D03-stories/E05S02-lms_badge.md) · [F-008](../../../B-Requirements/B03-features/F-008-badges-and-gamification.md) · [G02 R-003](../../../G-Governance/G02-risk-register/_index.md)
