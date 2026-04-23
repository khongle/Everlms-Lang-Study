# Impact Analysis (Abbreviated) — {change-id}

> Use ONLY for HOTFIX during the active 4-hour SLA window.
> Fill in < 30 minutes. Full analysis (full.md) must follow within 5 business days.
> Adapted from `00-Guidelines/A09-operational-templates/04-impact.md` §Quick Impact Assessment
> and `00-Guidelines/A04-maintenance-workflow.md` §Abbreviated E09.

---

**Change ID:** HOTFIX-{NNN}
**Incident:** INC-{NNN}
**Date / Time:** {YYYY-MM-DD HH:MM}
**Analyst:** {Agent_Coder / Agent_Architect}
**Severity:** SEV1 / SEV2

> HOTFIX uses this abbreviated form to preserve speed.
> Full analysis must land in `E-Quality/E09-impact-analysis/HOTFIX-{NNN}.md` within 5 business days.
> Missed deadline → escalate via `G-Governance/G03-blockers.md`.

---

## 1. Scope

- **File(s):** `{file:line-range}` — {one-line description of change}
- **Unit:** U-{NNN} {unit name}
- **Schema change:** Yes / No
- **Dependency change:** Yes / No
- **API contract change:** Yes / No

---

## 2. Blast Radius

| Dimension | Impact |
|-----------|--------|
| User segments affected | {all logged-in / enrolled in X / admins only / …} |
| Traffic percentage | {~N% of requests} |
| Features affected | {list features or "authentication only"} |
| If fix is wrong | {what breaks for users} |
| Adjacent services | {none / list} |

---

## 3. Rollback Plan

| Method | Steps | Time |
|--------|-------|------|
| Feature flag | Toggle `FF_HOTFIX_{ID}` to OFF | < 1 min |
| Redeploy | `docker pull previous-tag && deploy` via H02 rollback runbook | < 5 min |

Preferred method: {flag / redeploy — reason}

---

## 4. Risk Assessment (1-row)

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| {primary risk} | Low/Med/High | Low/Med/High | {mitigation} |

---

## 5. G4 Abbreviated Focus

**Top 2 categories only** (full G4 deferred to full analysis within 5 business days):

| Category | Finding |
|----------|---------|
| {Primary: Security or Error Handling} | {specific concern and how fix addresses it} |
| {Secondary: Data Integrity or Edge Cases} | {specific concern and how fix addresses it} |

Remaining 4 categories (Architecture, Performance, and others): deferred — tracked in full E09.

---

## Deferred to Full Analysis (within 5 business days)

- [ ] Cost projection
- [ ] Alternatives analysis
- [ ] Long-term tech debt implications
- [ ] Full G4 6-category review
- [ ] Upstream/downstream ripple mapping
- [ ] Complete test impact analysis

Full analysis owner: Agent_Architect. SA sign-off required before merge.
Escalation path if missed: `G-Governance/G03-blockers.md`.

---

*Full template: M03/full.md. Protocol: A04 §Abbreviated E09 for Hotfix.*
