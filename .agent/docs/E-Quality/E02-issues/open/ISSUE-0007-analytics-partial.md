# ISSUE-0007 - Analytics KPIs partial / scoping unverified

**Severity:** P2 · **Status:** open · **Task:** [E12S01T01](../../../D-Development/D04-tasks/todo/E12S01T01-complete-analytics-kpis.md)

## Description
`lms_analytics` provides course/quiz/user reports + CSV, but KPI depth is partial and role-scoping (manager=org vs instructor=own) needs verification.

## Impact
Incomplete reporting for B2B; possible over-broad data exposure if scoping is loose.

## Fix
Audit reports vs required KPIs; implement gaps; enforce role-scoped queries; verify exports.
