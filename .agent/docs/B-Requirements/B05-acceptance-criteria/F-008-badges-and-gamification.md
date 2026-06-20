# F-008 Acceptance Criteria - Badges & gamification

> ⚠️ **As-built gap (R-003 / BLK-003):** award logic is not implemented. Criteria 1–3 describe the `target` behavior and currently **FAIL**; criterion 4 (definition/admin) passes.

## Core criteria
1. **(target)** **Given** an award rule (e.g. course completed), **when** a learner meets it, **then** a `lms_user_badge` is granted automatically. — *FAILS today (no trigger).*
2. **(target)** **Given** an earned badge, **when** the learner views their profile, **then** the badge is displayed.
3. **(target)** **Given** a re-trigger of the same milestone, **when** evaluated, **then** the badge is not duplicated.
4. **Given** an admin, **when** they define a badge (`lms_badge`, `badge_type`), **then** it persists and is manageable. — *passes.*

## Evidence expected
- Module: [lms_badge](../../C-Design/C05-modules/lms_badge.md).
- Test: badge-definition CRUD (passes); award-trigger test (currently red) tracked in [E06](../../E-Quality/E06-implementation-status/_index.md) / [G02 R-003](../../G-Governance/G02-risk-register/_index.md).
