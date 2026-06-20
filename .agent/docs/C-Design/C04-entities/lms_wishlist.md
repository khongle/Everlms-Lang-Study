# C04 Entity - lms_wishlist

**Display name:** Wishlist item
**Owning module(s):** [lms_wishlist](../C05-modules/lms_wishlist.md)
**Mode:** as-built (Content Entity)

## Purpose
A saved-for-later course in a user's wishlist; captures purchase intent.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | owner |
| field_course | ref → lms_course |
| created | timestamp |

## Relationships
- N→1 `user`, N→1 `lms_course`. Convertible to an order (F-010).

## Editorial/data management
User-managed (add/remove). Surfaced on dashboard + mobile. See [F-015](../../B-Requirements/B03-features/F-015-learner-notes-and-wishlist.md).
