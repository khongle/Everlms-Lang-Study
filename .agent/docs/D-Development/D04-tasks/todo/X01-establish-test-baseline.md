---
id: X01
title: Establish PHPUnit kernel/functional baseline
status: todo
story: cross-cutting
owning_modules: [all]
risk: R-006
adequacy: NOT_RUN
---

# X01 - Establish PHPUnit kernel/functional baseline

## Goal
Create an automated-test baseline so G3 stops passing vacuously (R-006 / BLK-004, P1).

## Steps
1. Add kernel tests for core entities (course/lesson/quiz/order CRUD + access).
2. Functional tests for enroll→learn→quiz→certificate happy path.
3. Unit tests for the 8 grading strategies + certificate idempotency.
4. Wire into CI; set a coverage threshold for new code.

## Acceptance / gates
- Suites run green in CI; G3 enforces them; coverage threshold set.

## Links
[E01 test strategy](../../../E-Quality/E01-test-strategy/_index.md) · [G02 R-006](../../../G-Governance/G02-risk-register/_index.md)
