# C05 Module - lms_badge

**Name:** Badges · **Phase:** 5 · **Epic:** [E05](../../D-Development/D02-epics/E05-certification.md) · **Status:** blocked (award logic absent — BLK-003)

## Responsibility
Sở hữu định nghĩa badge (`lms_badge`) và các instance đã trao (`lms_user_badge`). Dự kiến trao badge khi đạt milestone.

## Owned / related entities
- [lms_badge](../C04-entities/lms_badge.md) · [lms_user_badge](../C04-entities/lms_user_badge.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-008](../../B-Requirements/B03-features/F-008-badges-and-gamification.md) | Badges & gamification |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-002](../../B-Requirements/B04-business-workflows/WF-002-quiz-attempt-and-grading.md) | (intended trigger) |

## Integrations / APIs (B06 / C06)
- —

## Backing pages
| Label | URL |
| ----- | --- |
| Badge admin / my badges | `/manage/badges`, `/my-badges` |

## Cross references
- [E06 implementation status](../../E-Quality/E06-implementation-status/_index.md); [G02 R-003](../../G-Governance/G02-risk-register/_index.md).

## Dependencies
- lms_base.

## Implementation notes
- ⚠️ **Gap:** entity + `field_criteria` đã có nhưng **chưa implement award trigger/evaluator nào** — badge không bao giờ được trao tự động. Cần một event subscriber (ví dụ khi course completion / quiz pass) để evaluate criteria và ghi `lms_user_badge`.
