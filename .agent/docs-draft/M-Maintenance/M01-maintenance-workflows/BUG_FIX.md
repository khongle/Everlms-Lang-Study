# BUG_FIX Playbook 🟡

> Standard bug fix. SLA: resolved within **current sprint**.
> Protocol source: `00-Guidelines/A04-maintenance-workflow.md` §Flow 2.

---

## Trigger Conditions

| Trigger | Example |
|---------|---------|
| User-reported bug with reproduction steps | Quiz score not saving on retry |
| Test regression detected in CI | Progress cascade test fails after merge |
| Monitoring alert (non-critical, not SEV1/SEV2) | Error rate spike on `/api/lessons` |
| QA finding in staging | Certificate PDF garbled for Vietnamese names |

---

## Feature Flag Requirement

| Condition | Flag Required? |
|-----------|---------------|
| Fix changes user-facing behavior | Optional — `FF_BUG_FIX_{ID}` (SA decision) |
| Fix is internal / invisible to users | Not typically required |
| Fix touches payment or auth flows | Required |

---

## Step-by-Step Flow

### Step 1 — TRIAGE (< 1 day)

- [ ] Log in `E-Quality/E02-issues/ISS-{NNN}.md` with reproduction steps
- [ ] Classify severity (Critical / High / Medium / Low) and priority
- [ ] Check if related to existing H04 tech debt
- [ ] Check: is production actively broken? If yes → reclassify as HOTFIX
- [ ] Create M01 instance file: `BUG_FIX-{NNN}.md`
- [ ] Log classification in D04 session log

### Step 2 — IMPACT ANALYSIS (standard)

- [ ] Use D07 code-map: trace file → unit → group → feature
- [ ] Identify affected units, groups, features
- [ ] Check E05 traceability: which requirements are affected?
- [ ] Check H05: any feature flags relevant to affected code?
- [ ] Write M03 **full** impact template → `E-Quality/E09-impact-analysis/BUG_FIX-{NNN}.md`

### Step 3 — PLANNING

- [ ] Create D03 unit spec for bug fix (or update existing)
- [ ] Define test cases for fix verification (both happy path and regression)
- [ ] Update D05 tasks: move to `in-progress/`
- [ ] If feature flag: define rollout plan

### Step 4 — IMPLEMENTATION

- [ ] Fix the bug
- [ ] **Write regression test** (mandatory — must prove the bug is fixed and cannot regress)
- [ ] Update existing tests if behavior changes
- [ ] Feature flag (if decided): register in H05 default OFF

### Step 5 — GATES (all mandatory)

| Gate | Requirement |
|------|------------|
| G1 Build | Must pass |
| G2 Lint | Must pass; coverage maintained |
| G3 Tests | Must pass; regression test included; coverage not reduced |
| G4 Adversarial | Full 6-category review. Focus: ERROR_HANDLING, EDGE_CASES |

### Step 6 — DEPLOY

- [ ] Deploy with next release cycle (unless severity demands earlier)
- [ ] If feature flag: deploy OFF, rollout per SA schedule
- [ ] Update F03 changelog

### Step 7 — VERIFY

- [ ] Confirm bug resolved in production (manual check or automated monitor)
- [ ] Monitor for regressions (D+1, D+3)
- [ ] Update E02 issue status to RESOLVED
- [ ] Close related H04 tech debt items if applicable
- [ ] Update M01 instance file status to CLOSED

---

## Regression Test Requirement

Every BUG_FIX **must** include a regression test. No exceptions.

| Type | When to use |
|------|------------|
| Unit test | Bug is in a single function/method |
| Integration test | Bug involves cross-module interaction |
| E2E / browser test | Bug is in a user-facing flow |

The regression test must:
- Fail **before** the fix is applied (proves the bug existed)
- Pass **after** the fix (proves the fix works)
- Be linked in D03 unit spec under "Regression Test"

---

## Required Documentation

| Artifact | Required? |
|----------|-----------|
| E02 issue entry | Required |
| D03 unit spec | Required |
| E03 regression test definition | Required |
| G4 adversarial review (full) | Required |
| M03 full impact analysis (E09) | Required |
| D07 code-map update | Required |
| H04 tech debt update | If debt resolved |
| H05 feature flag | If flag used |
| K04 lesson learned | Optional (add if bug reveals systemic pattern) |

---

*Protocol reference: A04 §Flow 2. Gate reference: A03 §Gates.*
