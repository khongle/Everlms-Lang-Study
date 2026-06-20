# C05 Module - lms_analytics

**Name:** Analytics & reporting · **Phase:** 12 · **Epic:** [E12](../../D-Development/D02-epics/E12-analytics.md) · **Status:** in-progress

## Responsibility
Read-only reporting over existing entities: enrollments, completions, quiz pass rates, revenue, per-course/learner progress. ~7 routes.

## Owned / related entities
- None (aggregates progress/orders/quiz results).

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-020](../../B-Requirements/B03-features/F-020-analytics-and-reporting.md) | Analytics & reporting |

## Business workflows (B04)
- —

## Integrations / APIs (B06 / C06)
- —

## Backing pages
| Label | URL |
| ----- | --- |
| Analytics dashboard | `/manage/analytics` |
| Course report | `/manage/analytics/course/{course}` |
| Revenue report | `/manage/analytics/revenue` |

## Cross references
- Reads [lms_progress](lms_progress.md), [lms_order](lms_order.md), [lms_quiz](lms_quiz.md).

## Dependencies
- lms_base.

## Implementation notes
- ⚠️ Coverage partial — some KPIs basic/placeholder (confirm depth in E06). Must respect role scoping (manager = org; instructor = own courses).
