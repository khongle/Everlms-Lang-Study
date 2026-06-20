# HOTFIX Playbook 🔴

> Emergency production fix. SLA: deployed within **4 hours** of incident declaration.
> Protocol source: `00-Guidelines/A04-maintenance-workflow.md` §Flow 1.

---

## Trigger Conditions

| Trigger | Example |
|---------|---------|
| Service outage / all users affected | Login broken site-wide |
| Data corruption in progress | Duplicate enrollment writes |
| Security breach / active exploit | Auth bypass discovered |
| SEV1 / SEV2 declared in A05 | Incident command active |

---

## Feature Flag Requirement

| Condition | Flag Required? |
|-----------|---------------|
| Change touches auth, payment, or data writes | YES — `FF_HOTFIX_{ID}`, default OFF |
| Pure read-path fix, easy redeploy rollback | Optional (SA call) |
| Schema migration bundled (avoid — see Schema Gate below) | REQUIRED or reclassify |

Flag naming: `FF_HOTFIX_{UPPERCASE_DESCRIPTION}` — e.g., `FF_HOTFIX_NULL_TOKEN_AUTH`.
Register in `H-Operations/H05-feature-flags.md` before deploy.

---

## Step-by-Step Flow

### Step 1 — TRIAGE (< 15 min)

- [ ] Log incident in `H-Operations/H01-incident-log/INC-{NNN}.md`
- [ ] Classify severity: SEV1 (outage/data/security) | SEV2 (major degradation) | SEV3/SEV4 (minor)
- [ ] Notify SA immediately
- [ ] **Schema migration gate:**
  - Code-only fix → proceed HOTFIX path
  - Fix requires DB migration → **reclassify as BUG_FIX** (or get SA written approval with backward-compatible + reversible migration)
- [ ] Create M01 instance file: `HOTFIX-{NNN}.md`
- [ ] Log classification in E02-issues and D04 session log

### Step 2 — IMPACT ANALYSIS (< 30 min, abbreviated)

- [ ] Use D07 code-map: trace file → unit → group → feature
- [ ] Identify minimum change set (do not expand scope)
- [ ] Check H05: existing feature flags on affected code?
- [ ] Fill M03 **abbreviated** impact template → save to `E-Quality/E09-impact-analysis/HOTFIX-{NNN}.md`
- [ ] Confirm no schema change (if discovered: stop, reclassify)

### Step 3 — PLANNING (< 15 min)

- [ ] Define fix scope — MINIMAL changes only (scope expansion → STOP, split ticket)
- [ ] Decide: feature flag needed?
- [ ] Create/update D03 unit spec for hotfix unit

### Step 4 — IMPLEMENTATION

- [ ] Implement fix (minimal scope)
- [ ] Write regression test proving the fix
- [ ] If feature flag:
  - Wrap fix: `FeatureFlagManager::isEnabled('FF_HOTFIX_{ID}')`
  - Register in H05, default OFF
  - Test both paths (flag ON and OFF)

### Step 5 — GATES (all mandatory, expedited)

| Gate | Requirement |
|------|------------|
| G1 Build | Must pass |
| G2 Lint | Must pass |
| G3 Tests | Must pass; regression test included |
| G4 Adversarial | Security + Data Integrity: FULL review. Other categories: one-line abbreviated proof acceptable. "Looks good" still FORBIDDEN. |

- SEV1: 2 G4 retries allowed; escalate via G03-blockers if still failing.

### Step 6 — DEPLOY

- [ ] Deploy to staging → quick smoke test
- [ ] SA approves production deploy
- [ ] Deploy to production
- [ ] If feature flag: enable to 100% (direct flip for hotfix, unless SA specifies staged rollout)
- [ ] Record deploy time in H01 incident log

### Step 7 — VERIFY (24-72 hours)

- [ ] Monitor error rates, latency, throughput per M04
- [ ] Confirm incident resolved; update H01 with resolution
- [ ] Update H02 runbook if new procedure discovered
- [ ] Create H04 tech-debt entry if hotfix is tactical (proper fix deferred)
- [ ] Log K04 lesson learned (required for HOTFIX)

---

## Rollback Protocol

| Trigger | Action |
|---------|--------|
| SEV1 attributable to fix | Immediate flag flip to OFF or redeploy previous tag |
| SEV2 not resolved in 1 hour | Rollback; do not wait |
| Error budget burn > 10× for 1 hour | Flag off or redeploy |

Rollback steps:
1. Toggle `FF_HOTFIX_{ID}` to OFF (if flag deployed) — instant, no redeploy
2. OR: redeploy previous artifact tag via H02 rollback runbook (target: < 5 min)
3. Verify health endpoint, error rate normalizing
4. Log rollback event in H01

---

## Required Documentation

| Artifact | Timing |
|----------|--------|
| H01 incident entry (severity, timeline, root cause, resolution) | During/after |
| E02 issue entry | During triage |
| D03 unit spec | Before implementation |
| E03 regression test definition | Before deploy |
| G4 adversarial review (abbreviated) | Before deploy |
| M03 abbreviated impact analysis (E09) | Before deploy |
| K04 lesson learned | Required (HOTFIX always) |
| H02 runbook update | If new procedure discovered |
| H04 tech-debt entry | If tactical fix |
| M03 **full** impact analysis (E09) | Within 5 business days — ticket BLOCKED until merged |
| D07 code-map update | Within 5 business days |

---

## Postmortem Trigger

| Severity | Postmortem Required? | Deadline |
|----------|---------------------|----------|
| SEV1 | Yes — mandatory | Within 5 business days |
| SEV2 | Yes — mandatory | Within 1 sprint |
| SEV3/SEV4 | Optional (SA call) | Next sprint planning |

Postmortem file: `E-Quality/E07-retrospectives/INC-{NNN}.md`

---

*Protocol reference: A04 §Flow 1. Gate reference: A03 §Gates. Incident reference: A05.*
