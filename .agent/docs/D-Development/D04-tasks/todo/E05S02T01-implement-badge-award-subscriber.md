---
id: E05S02T01
task_id: E05S02T01
title: Implement badge award subscriber
status: todo
story: E05S02
owning_modules: [lms_badge]
modules: [lms_badge]
difficulty: 3
risk: R-003
adequacy: NOT_RUN
---

# E05S02T01 - Implement badge award subscriber

## Goal
Tự động grant `lms_user_badge` khi learner đáp ứng `field_criteria` của badge (R-003 / BLK-003, P1).

## Steps
1. Định nghĩa cách evaluation criteria (hoàn thành course, quiz mastery, streak).
2. Thêm event subscriber (khi completion/QuizPassedEvent) để evaluate + grant một cách idempotent.
3. Hiển thị (surface) badge đã đạt được trên profile/dashboard.

## Acceptance / gates
- Đạt một milestone sẽ grant badge đúng một lần (không trùng lặp); hiển thị trên profile.
- G3: test award-trigger + idempotency.

## Links
[story](../../D03-stories/E05S02-lms_badge.md) · [F-008](../../../B-Requirements/B03-features/F-008-badges-and-gamification.md) · [G02 R-003](../../../G-Governance/G02-risk-register/_index.md)
