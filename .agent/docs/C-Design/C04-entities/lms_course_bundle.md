# C04 Entity - lms_course_bundle

**Display name:** Course Bundle
**Owning module(s):** [lms_bundle](../C05-modules/lms_bundle.md)
**Mode:** as-built (Content Entity)

## Purpose
A sellable package of multiple courses at a single bundle price/discount.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| name | bundle title |
| field_description | formatted description |
| field_price | bundle price |
| field_discount_percent | discount vs. buying individually |
| field_thumbnail | catalog image |
| field_courses | **multi-ref → lms_course** (included courses) |
| status | publish |

## Relationships
- N→N `lms_course` via `field_courses`.
- Purchasable via `lms_order_item` (type=bundle); purchase fans out to per-course enrollment.

## Editorial/data management
Editors create bundles and pick courses via the bundle entity form. Price/discount are fields. Cache invalidated on save. See [F-009](../../B-Requirements/B03-features/F-009-course-bundles.md), [C16 sheet 01](../C16-item-solutions/01-entities.md).
