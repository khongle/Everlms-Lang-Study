# C04 Entity - lms_badge

**Display name:** Badge
**Owning module(s):** [lms_badge](../C05-modules/lms_badge.md)
**Mode:** as-built (Content Entity) — ⚠️ award logic absent (BLK-003)

## Purpose
An achievement badge definition (icon, type, XP reward, award criteria). Intended to be granted on milestones.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| name | badge name |
| field_description | formatted description |
| field_icon | badge image |
| field_type | badge type (`badge_type` taxonomy) |
| field_xp_reward | XP/points granted |
| field_criteria | award criteria (definition) |

## Relationships
- Granted instances: `lms_user_badge.field_badge`.

## Editorial/data management
Admins define badges via the badge form. ⚠️ **As-built gap:** `field_criteria` is defined but **no code evaluates it to grant badges** — see [F-008](../../B-Requirements/B03-features/F-008-badges-and-gamification.md), [G02 R-003](../../G-Governance/G02-risk-register/_index.md).
