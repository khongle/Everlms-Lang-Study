# E03 — Test Definitions Index

> Test case specifications cho EverLMS. Mỗi file là một test definition độc lập.
> Đây là **định nghĩa** (specs), không phải kết quả — xem E04 cho run logs.

## Registry

| ID | File | Type | Feature | Unit | Priority | Status |
|----|------|------|---------|------|----------|--------|
| TEST-001 | TEST-001-course-enrollment-flow.md | Integration / E2E | F-ENROLL | U-021, U-022 | P0 | DEFINED |
| TEST-002 | TEST-002-quiz-grading-auto.md | Unit / Integration | F-QUIZ | U-016, U-020 | P0 | DEFINED |
| TEST-003 | TEST-003-progress-cascade.md | Integration | F-PROGRESS | U-022, U-059 | P0 | DEFINED |
| TEST-004 | TEST-004-payment-checkout.md | Integration | F-PAYMENT | U-027, U-030 | P0 | DEFINED |
| TEST-005 | TEST-005-certificate-issuance.md | Integration | F-CERT | U-036, U-037 | P0 | DEFINED |

## Coverage Summary

| Test Type | Count | Linked Issues |
|-----------|-------|---------------|
| Unit | 2 | ISSUE-002 |
| Integration | 4 | ISSUE-001, ISSUE-002, ISSUE-004 |
| E2E | 2 | — |

## Naming Convention

```
{module}.{feature}.{scenario}
Ví dụ: progress.cascade.should_update_course_percent_on_lesson_complete
```

## Traceability

- E01-test-strategy.md — loại test + tool
- E05-traceability.md — REQ → FEAT → UNIT → TEST chain
- E04-test-results/ — run logs sau khi tests được thực thi
