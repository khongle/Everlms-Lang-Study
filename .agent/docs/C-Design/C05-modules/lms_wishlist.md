# C05 Module - lms_wishlist

**Name:** Wishlist · **Phase:** 8 · **Epic:** [E08](../../D-Development/D02-epics/E08-community.md) · **Status:** done

## Responsibility
Owns the per-user course wishlist (save-for-later / purchase intent). ~3 routes.

## Owned / related entities
- [lms_wishlist](../C04-entities/lms_wishlist.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-015](../../B-Requirements/B03-features/F-015-learner-notes-and-wishlist.md) | Learner notes & wishlist |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-001](../../B-Requirements/B04-business-workflows/WF-001-enrollment-and-learning.md) | Enrollment & learning |

## Integrations / APIs (B06 / C06)
- Served via mobile API.

## Backing pages
| Label | URL |
| ----- | --- |
| My wishlist | `/wishlist` |

## Cross references
- Entity [C04](../C04-entities/lms_wishlist.md); convertible to [lms_order](lms_order.md).

## Dependencies
- lms_base, lms_course.

## Implementation notes
- Add/remove toggle on catalog/course; one row per user-course.
