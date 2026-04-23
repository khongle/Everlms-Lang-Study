# A03 — Project Context (Living State)

> **Tier 1** — load at every session start. Mutable counterpart to A02.
> Update cadence: **at every session end**.

---

## Current State

| Field | Value |
|-------|-------|
| Last updated | 2026-04-16 |
| Updated by | Agent_PM |
| Phase | 4-Development |
| Checkpoint | CP3 done → CP4 in progress (G-HARDENING blocking) |
| Active persona | Agent_PM (docs); next = Agent_Coder on G-HARDENING |
| Sprint | sprint-01 |
| Docs files | 373 .md across B–S layers |

---

## Recent Decisions

| Date | Decision | Rationale | ADR |
|------|----------|-----------|-----|
| 2026-04-16 | AI provider = Drupal Plugin system | Extensible, standard discovery | ADR-006 |
| 2026-04-16 | Payment gateway = Interface + Manager pattern | Swap providers without business code changes | ADR-007 |
| 2026-04-16 | Question grading = Strategy pattern | 8 question types, clean separation | ADR-005 |

## Open Decisions (SA action required)

| ID | Question | Options | Recommendation | Blocks |
|----|----------|---------|----------------|--------|
| DEC-001 | API input validation strategy | A: per-route DTO / B: global EventListener | A | U-057, ADR-009 |
| DEC-003 | Progress race condition handling | A: SELECT FOR UPDATE / B: queue worker | A for v1 | U-059, ADR-010 |

## Active Blockers

| ID | Description | Owner | Since | Blocks |
|----|-------------|-------|-------|--------|
| BLK-001 | ADR-009 awaiting SA sign-off (input validation) | SA | 2026-04-16 | U-057 |
| BLK-002 | ADR-010 awaiting SA sign-off (race condition) | SA | 2026-04-16 | U-059 |

## Feature Flag Status

| Flag ID | Feature | Rollout % | Notes |
|---------|---------|-----------|-------|
| FF_FEAT_AI_PROVIDER | AI provider plugin | 0% (dev only) | U-047 |
| FF_FEAT_AI_BUILDER | AI course builder | 0% (dev only) | U-048 |
| FF_FEAT_ZOOM | Zoom meeting integration | 0% (dev only) | U-049 |

## Progress Summary

| Group | Total | Done | Spec | Status |
|-------|-------|------|------|--------|
| G-FOUNDATION | 4 | 4 | 0 | PASSED |
| G-CONTENT | 8 | 8 | 0 | PASSED |
| G-ASSESSMENT | 8 | 8 | 0 | PASSED |
| G-PROGRESS | 4 | 4 | 0 | PASSED |
| G-COMMERCE | 10 | 5 | 5 | PARTIAL |
| G-CERTIFICATE | 4 | 1 | 3 | PENDING |
| G-COMMUNICATION | 6 | 3 | 3 | PENDING |
| G-INTEGRATION | 6 | 2 | 4 | PENDING |
| G-ADDITIONAL | 6 | 6 | 0 | PASSED |
| G-HARDENING | 14 | 0 | 14 | NOT_STARTED |
| G-UI-POLISH | 14 | 0 | 14 | NOT_STARTED |
| **Total** | **84** | **41** | **43** | **49% done** |

## DONE-Unit Debt (PARTIAL flags)

| Unit | Why partial |
|------|------------|
| U-022 | No ProgressCascadeService class; logic inlined in controller |
| U-028 | Stripe handleWebhook() is stub; processPayment() scaffold only |
| U-029 | PayPal all methods TODO stubs; duplicate class FIND-009 |
| U-052 | CourseBundle entity only — no routing/forms/commerce |
| U-054 | Badge entities only — no award logic/triggers |

## Critical Findings (source-code review 2026-04-16)

| ID | Finding | Severity |
|----|---------|----------|
| FIND-001 | Stripe handleWebhook() returns 200 stub — no signature verify | Critical |
| FIND-002 | PayPal gateway entirely TODO; no webhook route | Critical |
| FIND-003 | OpenAI calls have no max_tokens cap — runaway cost | High |
| FIND-004 | /checkout routes use _access: TRUE (anon) | High |
| FIND-005 | No atomic txn between order + enrollment creation | High |
| FIND-008 | Gemini API key in URL query param — leaks in logs | High |
| FIND-009 | PayPalGateway duplicate class in lms_payment + lms_paypal | High |
| FIND-013 | /api/lms/notifications/unread-count allows anonymous | High |

Full list (FIND-001 to FIND-015): see G-Governance/G02-risk-register.md.

## Next Steps

1. SA sign-off ADR-009 (input validation) and ADR-010 (race condition)
2. Pick up U-060 (Stripe webhook signature) — FIND-001 + RISK-004
3. Pick up U-059 (progress race fix) once ADR-010 accepted — RISK-002
4. Run G2 (lint) + G4 (test) baseline against current scaffolded code

## Health Check

| Dimension | Status | Last Checked |
|-----------|--------|-------------|
| Completeness | WARNING — 43/84 units still SPEC | 2026-04-16 |
| Consistency | WARNING — D01 has 3 spec-vs-source discrepancies | 2026-04-16 |
| Freshness | OK — docs fully built this session | 2026-04-16 |
| Drift | WARNING — 5 DONE units flagged PARTIAL | 2026-04-16 |

---

*End of A03-project-context.md*
