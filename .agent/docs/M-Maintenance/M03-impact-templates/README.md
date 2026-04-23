# M03 — Impact Templates: Index

> Two template variants for E09 impact analyses.
> Use the variant that matches the maintenance type and risk level.

---

## Templates

| File | Variant | When to Use |
|------|---------|------------|
| [full.md](full.md) | Full impact analysis | BUG_FIX, ENHANCEMENT, REFACTOR, DEP, and HOTFIX follow-up (within 5 business days) |
| [abbreviated.md](abbreviated.md) | Abbreviated / quick variant | HOTFIX only (during the 4-hour SLA window) |

---

## Decision Rule

```
Is this a HOTFIX with active SLA running (< 4h window)?
  ├── YES → use abbreviated.md
  │         Then: full.md must be completed within 5 business days
  │         Ticket stays BLOCKED until full analysis is merged.
  └── NO  → use full.md
```

---

## Output Location

All E09 impact analysis instances go in `E-Quality/E09-impact-analysis/`.
Naming: `{TYPE}-{NNN}.md` matching the M01 instance file.

Example: M01 instance `HOTFIX-003.md` → E09 instance `HOTFIX-003.md`.

---

## Escalation

If the full E09 follow-up for a HOTFIX is not merged within 5 business days:
- Escalate via `G-Governance/G03-blockers.md`
- Owner: Agent_Architect drafts; SA sign-off required on merge

---

*Template source: `00-Guidelines/A08-layer-templates/12-m-maintenance.md` §M03 and `00-Guidelines/A09-operational-templates/04-impact.md`.*
