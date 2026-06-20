# C05 Module - lms_bundle

**Name:** Course bundles · **Phase:** 6 · **Epic:** [E06](../../D-Development/D02-epics/E06-commerce.md) · **Status:** done

## Responsibility
Owns the course-bundle entity: package several courses at one price; purchase enrolls in all included courses.

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
- Entity [C04](../C04-entities/lms_course_bundle.md); courses [lms_course](lms_course.md); orders [lms_order](lms_order.md).

## Dependencies
- lms_base, lms_course.

## Implementation notes
- `field_courses` multi-ref; purchase fan-out to per-course enrollment at order completion. `field_discount_percent` for bundle pricing.
