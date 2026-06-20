# G04 — Change Requests

> Scoped change requests for modifications that affect scope, architecture, or multiple units.
> A Change Request (CR) is required whenever a proposed change exceeds a single unit boundary or touches architecture.

---

## Purpose

Change Requests provide a formal tracking mechanism for:
- Changes affecting more than one development unit
- Architecture modifications (C01) or module boundary changes (C03)
- New integration requirements (B06)
- Scope additions (new features or requirements)
- Breaking API changes (C04, C06)
- Database schema changes affecting multiple entities
- Security model changes
- NFR target changes (B02, E10)
- Any change the SA explicitly requests to track formally

Without a CR, changes that span multiple units risk inconsistent documentation, untested edge cases, and untracked regressions.

---

## Template

Use the standard CR template defined in:
**`00-Guidelines/A08-layer-templates/07-g-governance.md` — §G04 Change Requests**

Each CR file must include:
- CR ID and title
- Status (PROPOSED / APPROVED / REJECTED / IMPLEMENTED / CANCELLED)
- Reason for change and trigger
- Scope: affected units, groups, modules, documents
- Impact analysis link to `E09-impact-analysis/{CR-NNN}.md`
- Proposed solution and alternatives
- Feature flag requirement
- Rollback plan
- SA decision with date and rationale

---

## Active Change Requests

*No active change requests at this time.*

---

## Change Request Summary

| CR ID | Title | Status | Priority | Units Affected | Sprint Impact |
|-------|-------|--------|----------|---------------|---------------|
| — | — | — | — | — | — |

---

## When to Raise a CR

```
Does the change affect more than 1 unit?          YES → CR required
Does it modify architecture (C01) or C03?         YES → CR required
Does it add new integration requirements (B06)?   YES → CR required
Does it add new features or scope?                YES → CR required
Is it a breaking API change?                      YES → CR required
Does it change the database schema cross-entity?  YES → CR required
Does it change security model or RBAC?            YES → CR required
Does the SA explicitly request formal tracking?   YES → CR required
Otherwise:                                        Proceed without CR
```

---

## CR Lifecycle

```
SA or Agent identifies cross-unit change
  → Create CR-{NNN}.md from template
  → Set Status: PROPOSED
  → Link E09 impact analysis
  → SA reviews → APPROVED or REJECTED
  → If APPROVED: Agent implements per CR scope
  → On completion: set Status: IMPLEMENTED
  → If scope dropped: set Status: CANCELLED
```

---

*Last updated: 2026-04-16*
