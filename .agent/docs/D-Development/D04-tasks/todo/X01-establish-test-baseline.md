---
id: X01
title: Establish PHPUnit kernel/functional baseline
status: todo
story: cross-cutting
owning_modules: [all]
modules: [all]
difficulty: 4
risk: R-006
adequacy: NOT_RUN
---

# X01 - Establish PHPUnit kernel/functional baseline

## Goal
Tạo automated-test baseline để G3 không còn pass một cách vacuous (rỗng, không kiểm chứng thật) (R-006 / BLK-004, P1).

## Steps
1. Thêm kernel test cho các entity cốt lõi (CRUD + access của course/lesson/quiz/order).
2. Functional test cho happy path enroll→learn→quiz→certificate.
3. Unit test cho 8 grading strategy + certificate idempotency.
4. Wire vào CI; đặt coverage threshold cho code mới.

## Acceptance / gates
- Các test suite chạy green trong CI; G3 enforce chúng; coverage threshold đã được đặt.

## Links
[E01 test strategy](../../../E-Quality/E01-test-strategy/_index.md) · [G02 R-006](../../../G-Governance/G02-risk-register/_index.md)
