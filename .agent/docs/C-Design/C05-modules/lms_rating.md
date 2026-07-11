# C05 Module - lms_rating

**Name:** Ratings & reviews · **Phase:** 8 · **Epic:** [E08](../../D-Development/D02-epics/E08-community.md) · **Status:** done

## Responsibility
Sở hữu rating/review của course và hiển thị aggregate; có moderation (approve/feature). ~2 route.

## Owned / related entities
- [lms_rating](../C04-entities/lms_rating.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-014](../../B-Requirements/B03-features/F-014-ratings-and-reviews.md) | Ratings & reviews |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-001](../../B-Requirements/B04-business-workflows/WF-001-enrollment-and-learning.md) | Enrollment & learning |

## Integrations / APIs (B06 / C06)
- —

## Backing pages
| Label | URL |
| ----- | --- |
| Submit/list reviews (on course detail) | `/course/{course}` (reviews tab) |

## Cross references
- Entity [C04](../C04-entities/lms_rating.md); aggregate được cache trên [lms_course](lms_course.md) tại `field_rating`.

## Dependencies
- lms_base, lms_course.

## Implementation notes
- Điều kiện: learner đã enroll. Aggregate được tính lại khi rating được save/approve. ~79 rating đang live.
