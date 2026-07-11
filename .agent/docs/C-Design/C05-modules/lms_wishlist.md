# C05 Module - lms_wishlist

**Name:** Wishlist · **Phase:** 8 · **Epic:** [E08](../../D-Development/D02-epics/E08-community.md) · **Status:** done

## Responsibility
Sở hữu wishlist course theo từng user (save-for-later / ý định mua). ~3 route.

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
- Phục vụ qua mobile API.

## Backing pages
| Label | URL |
| ----- | --- |
| My wishlist | `/wishlist` |

## Cross references
- Entity [C04](../C04-entities/lms_wishlist.md); có thể chuyển thành [lms_order](lms_order.md).

## Dependencies
- lms_base, lms_course.

## Implementation notes
- Toggle add/remove trên catalog/course; một row cho mỗi cặp user-course.
