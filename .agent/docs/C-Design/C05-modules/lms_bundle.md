# C05 Module - lms_bundle

**Name:** Course bundles · **Phase:** 6 · **Epic:** [E06](../../D-Development/D02-epics/E06-commerce.md) · **Status:** done

## Responsibility
Sở hữu entity course-bundle: đóng gói nhiều course vào một mức giá; mua bundle sẽ enroll vào tất cả course bên trong.

## Owned / related entities
- [lms_course_bundle](../C04-entities/lms_course_bundle.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-009](../../B-Requirements/B03-features/F-009-course-bundles.md) | Course bundles |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-004](../../B-Requirements/B04-business-workflows/WF-004-order-checkout-and-payment.md) | Order checkout & payment |

## Integrations / APIs (B06 / C06)
- —

## Backing pages
| Label | URL |
| ----- | --- |
| Bundle detail | `/bundle/{bundle}` |
| Bundle manage | `/manage/bundles` |

## Cross references
- Entity [C04](../C04-entities/lms_course_bundle.md); course [lms_course](lms_course.md); order [lms_order](lms_order.md).

## Dependencies
- lms_base, lms_course.

## Implementation notes
- `field_courses` multi-ref; khi order hoàn tất, purchase sẽ fan-out thành enrollment cho từng course. `field_discount_percent` dùng để định giá bundle.
