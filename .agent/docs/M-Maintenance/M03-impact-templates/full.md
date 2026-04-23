# Impact Analysis (Full) — {change-id}

> Use for: BUG_FIX, ENHANCEMENT, REFACTOR, DEPENDENCY_UPDATE, and HOTFIX follow-up.
> Adapted from `00-Guidelines/A09-operational-templates/04-impact.md`.

---

**ID:** IA-{NNN}
**Change ID:** {TYPE}-{NNN} (matches M01 instance file)
**Date:** {YYYY-MM-DD}
**Type:** HOTFIX 🔴 | BUG_FIX 🟡 | ENHANCEMENT 🔵 | REFACTOR 🟣 | DEPENDENCY_UPDATE 🟠
**Change Request:** G04-CR-{NNN} (if applicable, else —)
**Severity:** Critical | High | Medium | Low
**Analyst:** Agent_Coder | Agent_Architect

---

## 1. Purpose

### Root Cause / Reason
{Why is this change needed?
- Bug: root cause analysis
- Enhancement: business requirement
- Refactor: tech debt item (H04-TD-{NNN})
- Dependency: CVE ID or EOL notice}

### Proposed Change
{What will change:
- Code: files, functions, logic
- Configuration: which configs
- Database: schema changes, migrations
- Infrastructure: any infra changes}

---

## 2. Changed Surface (from D07 Code-Map)

### Directly Affected Files

| File Path | Unit ID | Group | Feature | Change Type | Est. Lines |
|-----------|---------|-------|---------|-------------|-----------|
| `web/modules/custom/{module}/{file}.php` | U-{NNN} | G-{name} | F-{NNN} | Modified | ~{N} |
| `web/modules/custom/{module}/{file}.php` | U-{NNN} | G-{name} | F-{NNN} | Added | ~{N} |

### Indirectly Affected (Dependents / Callers)

| File Path | Unit ID | Why Affected | Impact Level |
|-----------|---------|-------------|-------------|
| `{file}` | U-{NNN} | Imports modified service | Test may need update |

### Upstream / Downstream Ripple

- Upstream callers affected: {list or "none"}
- Downstream data affected: {list or "none"}
- Shared schemas / contracts changed: {list or "none"}

---

## 3. Test Impact

### Tests to Add

| Test ID | Type | Description | Covers |
|---------|------|-------------|--------|
| T-{NNN} | Unit | {regression test} | {scenario} |
| T-{NNN} | Integration | {cross-unit test} | {interaction} |

### Tests to Modify

| Test ID | File | Reason |
|---------|------|--------|
| T-{NNN} | {file} | {why} |

### Validation Suite

| Suite | Command | Scope |
|-------|---------|-------|
| Unit (affected module) | `php vendor/bin/phpunit web/modules/custom/{module}/tests/` | Direct |
| Full suite | `php vendor/bin/phpunit` | Regression |

---

## 4. Runtime Impact

### Performance

| Metric | Before | Expected After | Risk |
|--------|--------|---------------|------|
| API latency p95 | {N}ms | {N}ms | Low/Med/High |
| DB queries per request | {N} | {N} | Low/Med/High |
| Memory | {N}MB | {N}MB | Low/Med/High |

### Availability

| Aspect | Impact |
|--------|--------|
| Downtime required | None / Brief / Maintenance window |
| Rolling deploy safe | Yes / No — {reason} |
| Backward compatible | Yes / No — {breaking details} |

### Data

| Aspect | Impact |
|--------|--------|
| DB migration | None / Additive / Destructive |
| Data transformation | None / Required — {describe} |
| Data loss risk | None / Possible — {safeguards} |
| DB backup required before deploy | Yes / No |

### User

| Aspect | Impact |
|--------|--------|
| User-facing behavior change | None / Yes — {describe} |
| API contract changes | None / Breaking / Non-breaking |
| UI changes | None / Yes — {describe} |

---

## 5. H-Layer Impact

| Layer | Question | Answer |
|-------|----------|--------|
| H01 Incidents | New incident type introduced? | No / Yes — {describe} |
| H01 Incidents | Existing incident type resolved? | No / Yes — INC-{NNN} |
| H02 Runbooks | New runbook needed? | No / Yes — {scenario} |
| H02 Runbooks | Existing runbook update needed? | No / Yes — {which} |
| H03 Dependencies | Dependency version changed? | No / Yes — {from → to} |
| H03 Dependencies | New CVE introduced? | No / Yes — {CVE ID} |
| H04 Tech Debt | New debt introduced? | No / Yes — {TD-NNN} |
| H04 Tech Debt | Existing debt resolved? | No / Yes — {TD-NNN} |
| H05 Feature Flags | New flag needed? | No / Yes — FF_{TYPE}_{ID} |
| H05 Feature Flags | Existing flag affected? | No / Yes — {which} |

---

## 6. Risk Assessment

| # | Risk | Likelihood | Impact | Severity | Mitigation |
|---|------|-----------|--------|----------|-----------|
| 1 | {risk} | Low/Med/High | Low/Med/High | L×I | {mitigation} |
| 2 | {risk} | Low/Med/High | Low/Med/High | L×I | {mitigation} |

---

## 7. Feature Flag Plan

**Flag Required:** YES / NO

**If YES:**

| Property | Value |
|----------|-------|
| Flag ID | FF_{TYPE}_{ID} |
| Description | {what this flag controls} |
| Default | OFF |
| Rollout | 0% → 10% ({duration}) → 50% ({duration}) → 100% ({duration}) |
| Monitor | {error rate, latency, business metric} |
| Rollback trigger | {metric threshold} |
| Cleanup target | Sprint {N} (2 sprints after 100%) |

**If NO:**
Reason: {why not needed}

---

## 8. Rollback Plan

### Automatic Rollback Triggers

- Error rate > {threshold}% for > {duration}
- Latency p95 > {threshold}ms for > {duration}
- {Other metric trigger}

### Rollback Steps

1. {Feature flag OFF / redeploy previous tag}
2. {Rollback DB migration (if any)}
3. Verify health endpoint
4. Confirm metrics returning to baseline

### Rollback Verification

- [ ] Health endpoint responding
- [ ] Error rate at baseline
- [ ] No data corruption
- [ ] Previous behavior restored

---

## 9. G4 Focus (6 Categories)

| Category | Concern |
|----------|---------|
| Security | {concern or "none"} |
| Error handling | {concern or "none"} |
| Performance | {concern or "none"} |
| Edge cases | {concern or "none"} |
| Architecture | {concern or "none"} |
| Data integrity | {concern or "none"} |

---

## 10. Approval

| Role | Name | Decision | Date |
|------|------|----------|------|
| SA | {name} | APPROVED / REJECTED | {YYYY-MM-DD} |

**Conditions:** {any conditions or —}
**Proceed to:** {TYPE} implementation per M01 playbook

---

*Template source: 00-Guidelines/A09-operational-templates/04-impact.md adapted for M03. Full protocol: A04.*
