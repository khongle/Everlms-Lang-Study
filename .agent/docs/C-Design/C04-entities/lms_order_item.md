# C04 Entity - lms_order_item

**Display name:** Order Item (line)
**Owning module(s):** [lms_order](../C05-modules/lms_order.md)
**Mode:** as-built (Content Entity)

## Purpose
A single purchasable line on an order — a course, a bundle, or a membership plan.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| field_course | ref → lms_course (when a course line) |
| field_membership_plan | ref → lms_membership_plan (when a plan line) |
| field_price | line price |
| field_type | line type (course / bundle / membership) |

## Relationships
- Referenced by `lms_order.field_items`. Points to the purchased product (course/bundle/plan).

## Editorial/data management
System-created at checkout. The line `field_type` determines which entitlement is granted on payment. See [lms_order](lms_order.md).
