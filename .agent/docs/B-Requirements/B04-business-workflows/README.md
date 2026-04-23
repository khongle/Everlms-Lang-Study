# B04 — Business Workflows

> Index of all workflow definitions. Mỗi file mô tả một workflow: trigger, actors, preconditions, happy path, error paths, business rules, postconditions.

---

## Workflow Index

| ID | File | Trigger | Key Actors | Related Features |
|----|------|---------|-----------|-----------------|
| WF-001 | [WF-001-enrollment-learning.md](./WF-001-enrollment-learning.md) | User chọn enroll/mua khóa học | Student, System, Gateway | F-001, F-003, F-004 |
| WF-002 | [WF-002-quiz-grading.md](./WF-002-quiz-grading.md) | Student submit quiz | Student, Instructor, System | F-002, F-003 |
| WF-003 | [WF-003-certificate-issuance.md](./WF-003-certificate-issuance.md) | Quiz passed / course completed | System, Student | F-005 |
| WF-004 | [WF-004-order-management.md](./WF-004-order-management.md) | Student mua course/bundle/plan | Student, System, Gateway | F-004 |
| WF-005 | [WF-005-membership-subscription.md](./WF-005-membership-subscription.md) | Student mua membership | Student, System, Scheduler | F-004 |
| WF-006 | [WF-006-course-creation.md](./WF-006-course-creation.md) | Instructor tạo khóa học | Instructor, Admin, AI | F-001, F-014 |
| WF-007 | [WF-007-user-registration.md](./WF-007-user-registration.md) | Visitor đăng ký tài khoản | Visitor, System, Email | F-007 |
| WF-008 | [WF-008-forum-flow.md](./WF-008-forum-flow.md) | User tạo/reply forum topic | Student, Instructor, Moderator | F-008 |
| WF-009 | [WF-009-coupon-flow.md](./WF-009-coupon-flow.md) | Admin tạo / Student dùng coupon | Admin, Student, System | F-004 |

---

## Cross-Workflow Risk Controls

| Risk | Control | Workflows Affected |
|------|---------|-------------------|
| Duplicate payment webhook | Idempotency key check | WF-001, WF-004, WF-005 |
| Race condition on progress | DB lock/transaction | WF-001, WF-002 |
| AI generation partial failure | Rollback partial content | WF-006 |
| Over-redemption of coupons | Atomic increment + lock | WF-009 |
| Certificate duplicate issuance | Unique constraint check | WF-003 |

---

## Related Docs

- [B03 — Features](../B03-features/) — workflows map back to feature IDs
- [B05 — Acceptance Criteria](../B05-acceptance-criteria/) — testable criteria per workflow
- [B06 — Integration Requirements](../B06-integration-requirements/) — external systems used
