# REFACTOR Playbook 🟣

> Internal code improvement. SLA: **planned sprint**. Zero behavior change.
> Protocol source: `00-Guidelines/A04-maintenance-workflow.md` §Flow 4.

---

## Trigger Conditions

| Trigger | Example |
|---------|---------|
| H04 tech debt register item | Progress service God object — split into focused services |
| G4 Medium/Low architectural finding | Plugin factory coupling too high |
| Code complexity exceeds threshold | Cyclomatic complexity > 10 in quiz grader |
| Post-implementation pattern consolidation | Three modules implementing ad-hoc caching; unify |

**Hard rule:** Refactor changes ONLY internal structure. If any external behavior changes (API response shape, DB schema, user-visible output), classify as ENHANCEMENT or BUG_FIX instead.

---

## Feature Flag Requirement

Not typically required (no behavior change). Exception: if refactor touches a critical path where silent regression is high-risk, use a flag as a circuit breaker — SA decision.

---

## Characterization Test Requirement

Before ANY refactor begins, characterization tests must exist covering the code being changed. These tests define the current (actual) behavior as the contract.

- **If characterization tests do not exist:** write them first, commit separately, then refactor.
- Characterization tests must pass before and after the refactor.
- If an existing test must be updated during refactor: document why in D03 ("test was testing implementation detail, not behavior").

---

## Step-by-Step Flow

### Step 1 — TRIAGE (sprint planning)

- [ ] Source: H04 tech debt register, G4 findings, code review
- [ ] Classify scope and risk (low / medium / high blast radius)
- [ ] Verify: is this truly behavior-neutral? If no → reclassify
- [ ] Check: does scope > 1 unit? If yes → G04 change-request
- [ ] Create M01 instance file: `REFACTOR-{NNN}.md`

### Step 2 — IMPACT ANALYSIS (full)

- [ ] Use D07 code-map: complete dependency mapping (all callers, all imports)
- [ ] List all files, units, modules affected
- [ ] Confirm: no external behavior change, no API contract change
- [ ] Write M03 **full** impact template with before/after architecture → `E-Quality/E09-impact-analysis/REFACTOR-{NNN}.md`

### Step 3 — PLANNING

- [ ] G04 change-request (if scope > 1 unit) → SA approves
- [ ] Document refactoring plan:
  - Current state: files, patterns, issues
  - Target state: new patterns, structure
  - Migration steps: incremental, each step must build
- [ ] Create D03 unit spec(s)
- [ ] **Write characterization tests** (if not already present) — commit before touching code

### Step 4 — IMPLEMENTATION

- [ ] Refactor incrementally — each step: `G1 Build` passes
- [ ] **RULE: All existing tests pass unchanged** — if a test fails, the refactor introduced a behavior change (stop, investigate)
- [ ] If a test must be updated: document in D03 with reason (implementation detail test, not behavior test)
- [ ] Update D07 code-map (file mappings change during refactor)
- [ ] Mark H04 tech debt items as RESOLVED as each item is addressed

### Step 5 — GATES (all mandatory)

| Gate | Requirement |
|------|------------|
| G1 Build | Must pass after every incremental step |
| G2 Lint | Must pass (may see improvements) |
| G3 Tests | Must pass; coverage maintained or improved |
| G4 Adversarial | Full 6-category. Focus: ARCHITECTURE (was the refactoring actually an improvement?), PERFORMANCE (no regressions introduced) |

### Step 6 — DEPLOY

- [ ] Deploy with normal release cycle
- [ ] Feature flags: not typically required
- [ ] If refactor touches critical paths: SA decides on flag

### Step 7 — VERIFY

- [ ] Confirm no regressions in production (error rate, latency unchanged)
- [ ] Update H04 tech debt register (mark items RESOLVED)
- [ ] Update K03 architecture patterns if new patterns established
- [ ] Optional: K04 lesson learned if refactor surfaces a systemic finding
- [ ] Close M01 instance file

---

## Required Documentation

| Artifact | Required? |
|----------|-----------|
| D03 unit spec | Required |
| Characterization tests (pre-refactor) | Required — commit before implementation |
| M03 full impact analysis (E09) | Required |
| G04 change-request | If scope > 1 unit |
| G4 adversarial review (full) | Required |
| D07 code-map update | Required |
| H04 tech debt update | Required (mark resolved items) |
| K03 architecture patterns update | If new patterns established |
| K04 lesson learned | Optional |

---

## Scope Expansion Guard

If during implementation you discover that the refactor requires a behavior change:

1. STOP immediately — do not implement the behavior change
2. Commit the structural refactor up to this point
3. Open a new BUG_FIX or ENHANCEMENT ticket for the behavior change
4. Cross-reference both tickets
5. Resume refactor in current ticket (structural changes only)

---

*Protocol reference: A04 §Flow 4. Gate reference: A03 §Gates.*
