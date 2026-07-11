---
id: WF-005-001
workflow: WF-005
title: Membership entitlement is tier-agnostic — a free "Basic Access" member unlocks all paid courses
severity: medium
category: product-gap
status: open
roles: student
source: round-2 real-session QA
found: 2026-07-11
needs: product decision
---

# WF-005-001 - Any active membership unlocks every course, regardless of plan tier

## Problem
`MembershipManager::hasActiveMembership()` is binary: any `active`/`trial`
membership whose `field_end_date` is in the future unlocks **every** course
(`CoursePublicController::detail()` sets `is_enrolled = TRUE` when it returns
true). There is no plan-tier / access-level field consulted in gating.

But the `/pricing` page markets differentiated tiers:
- **Basic Access (Free)** → "Access to all free onboarding courses" only.
- **Professional ($29)** → "Full access to all specialist courses".
- **Enterprise ($199)** → everything + facility features.

`maria.santos` holds an active **Basic Access** membership yet can open paid
specialist courses (e.g. course 108 and any paid course showed "Unlocked"),
which undermines the paid tiers.

## Expected (needs product decision)
Entitlement should respect the plan: define which plan(s) grant full-catalog
access (e.g. a `field_grants_full_access` boolean or an access-level/tier on
`lms_membership_plan`), and have `hasActiveMembership()` / F-004 gating check it.
A free tier should not unlock paid specialist courses.

## Evidence
- `01-student-pricing-plans.png` (tier differentiation) vs
  `02-student-my-membership.png` (maria = Basic Access) + WF-001
  `03-student-course-detail-not-enrolled.png` (paid/other course shows Unlocked).

## Recommendation
Not auto-fixed — requires a product decision on the tier→access mapping and a
schema addition on `lms_membership_plan`. Track as a WF-005 product gap
(analogous to the WF-009 coupon-admin gap noted in C12).

## Notes
The other WF-005 contracts are verified healthy in round-2: `hasActiveMembership`
excludes NULL/expired end dates; `myMembership` dedupes by plan and hides stale
rows (maria's 10 duplicate plan-7 rows render as one card); order completion
extends/reactivates instead of creating duplicates (`lms_order.module`).
