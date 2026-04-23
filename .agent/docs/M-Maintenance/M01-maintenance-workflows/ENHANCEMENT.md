# ENHANCEMENT Playbook 🔵

> Small planned enhancement. SLA: **planned sprint**.
> Protocol source: `00-Guidelines/A04-maintenance-workflow.md` §Flow 3.

---

## Trigger Conditions

| Trigger | Example |
|---------|---------|
| Feature request from SA or business | Add bulk enrollment import via CSV |
| Acceptance criteria gap discovered | Quiz timer was missing countdown UI |
| Integration requirement added | Zoom live lesson URL auto-generation |
| Minor UX improvement approved | Add lesson bookmarking |

**Scope guidance:**
- Small: < 1 unit → this playbook applies directly
- Medium (1–3 units) or Large (new group): this playbook + G04 change-request required

---

## Feature Flag Requirement

| Condition | Flag Required? |
|-----------|---------------|
| High-risk: auth, payment, externally visible API | Required — `FF_ENHANCEMENT_{ID}` |
| Low-risk internal UX change | Optional (SA call) |
| Multi-step rollout planned | Required |

---

## Step-by-Step Flow

### Step 1 — TRIAGE (sprint planning)

- [ ] Receive enhancement request from SA
- [ ] Classify scope: small / medium / large
- [ ] Check: does scope > 1 unit? If yes → write G04 change-request first
- [ ] Create M01 instance file: `ENHANCEMENT-{NNN}.md`

### Step 2 — IMPACT ANALYSIS (full)

- [ ] Use D07 code-map: dependency analysis (who calls affected code?)
- [ ] Identify all affected units, modules, APIs
- [ ] Check B05: new acceptance criteria needed?
- [ ] Check B06: new integration requirements?
- [ ] Check E10: NFR impact (performance, scalability)?
- [ ] Write M03 **full** impact template → `E-Quality/E09-impact-analysis/ENHANCEMENT-{NNN}.md`
- [ ] Update G02 risk register if new risks identified

### Step 3 — PLANNING

- [ ] SA approves G04 change-request (if scope > 1 unit)
- [ ] Create/update D02 group spec (if new group)
- [ ] Create D03 unit spec(s) for enhancement
- [ ] Update B05 acceptance criteria
- [ ] Update D01 dev overview with new units
- [ ] If feature flag: define rollout plan (default: 0% → 10% → 50% → 100%)

### Step 4 — IMPLEMENTATION

- [ ] Implement enhancement
- [ ] Write tests matching B05 acceptance criteria
- [ ] If feature flag:
  - Wrap new code: `FeatureFlagManager::isEnabled('FF_ENHANCEMENT_{ID}')`
  - Register in H05, default OFF
  - Implement both new + fallback paths
  - Test both paths

**D03 Lite requirement:** Even small enhancements need a unit spec. It may be abbreviated (title, acceptance criteria, test cases) but must exist before implementation starts.

### Step 5 — GATES (all mandatory)

| Gate | Requirement |
|------|------------|
| G1 Build | Must pass |
| G2 Lint | Must pass |
| G3 Tests | Must pass; new tests for enhancement included |
| G4 Adversarial | Full 6-category. Extra focus: ARCHITECTURE (fits existing patterns), PERFORMANCE (no degradation) |

### Step 6 — DEPLOY

- [ ] Deploy with feature flags OFF
- [ ] SA controls rollout: 0% → 10% → 50% → 100%
- [ ] Monitor at each rollout stage (error rate, latency)
- [ ] Update F03 changelog

### Step 7 — VERIFY

- [ ] Verify enhancement works at each rollout stage
- [ ] Validate B05 acceptance criteria in production
- [ ] Update E10 NFR tracking if NFRs affected
- [ ] Update H05 feature flag status
- [ ] Plan flag cleanup: 2 sprints after 100% rollout
- [ ] Close M01 instance file

---

## Required Documentation

| Artifact | Required? |
|----------|-----------|
| D03 unit spec (lite OK for small) | Required |
| G04 change-request | If scope > 1 unit |
| M03 full impact analysis (E09) | Required |
| B05 acceptance criteria update | Required |
| E03 test definitions | Required |
| G4 adversarial review (full) | Required |
| D07 code-map update | Required |
| H05 feature flag | If high-risk or multi-stage rollout |
| E10 NFR tracking update | If NFRs affected |
| K04 lesson learned | Optional |

---

*Protocol reference: A04 §Flow 3. Gate reference: A03 §Gates.*
