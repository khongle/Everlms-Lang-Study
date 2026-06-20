# C04 Entity - lms_user_badge

**Display name:** User Badge (awarded)
**Owning module(s):** [lms_badge](../C05-modules/lms_badge.md)
**Mode:** as-built (Content Entity) — ⚠️ never written by the system (BLK-003)

## Purpose
A badge awarded to a user at a point in time.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | recipient |
| field_badge | ref → lms_badge |
| field_awarded_at | award timestamp |

## Relationships
- N→1 `user`, N→1 `lms_badge`.

## Editorial/data management
Intended to be system-created on milestone. ⚠️ **No award trigger exists** — table stays empty unless seeded. Tracked in [E06](../../E-Quality/E06-implementation-status/_index.md). See [F-008](../../B-Requirements/B03-features/F-008-badges-and-gamification.md).
