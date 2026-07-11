# C05 Module - lms_analytics

**Name:** Analytics & reporting · **Phase:** 12 · **Epic:** [E12](../../D-Development/D02-epics/E12-analytics.md) · **Status:** in-progress

## Responsibility
Báo cáo chỉ đọc (read-only) trên các entity hiện có: enrollment, completion, tỷ lệ đậu quiz, doanh thu, tiến độ theo course/learner. ~7 route.

## Owned / related entities
- Không có (tổng hợp từ progress/orders/quiz results).

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
- Đọc dữ liệu từ [lms_progress](lms_progress.md), [lms_order](lms_order.md), [lms_quiz](lms_quiz.md).

## Dependencies
- lms_base.

## Implementation notes
- ⚠️ Coverage chưa đầy đủ — một số KPI còn cơ bản/placeholder (xác nhận độ sâu ở E06). Phải tôn trọng role scoping (manager = org; instructor = own courses).
