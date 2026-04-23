# M01 — Maintenance Workflows: Playbook Index

> Per-type playbooks for the 5 maintenance categories defined in `00-Guidelines/A04-maintenance-workflow.md`.
> Each playbook is a standing operating procedure (SOP) — not a per-change instance record.
> Per-change instance records live in individual files named `{TYPE}-{NNN}.md` within this folder.

---

## Playbooks

| File | Type | Indicator | SLA |
|------|------|-----------|-----|
| [HOTFIX.md](HOTFIX.md) | Emergency production fix | 🔴 | < 4 hours |
| [BUG_FIX.md](BUG_FIX.md) | Standard bug fix | 🟡 | < 1 sprint |
| [ENHANCEMENT.md](ENHANCEMENT.md) | Small planned enhancement | 🔵 | Planned sprint |
| [REFACTOR.md](REFACTOR.md) | Internal code improvement | 🟣 | Planned sprint |
| [DEP.md](DEP.md) | Dependency upgrade | 🟠 | Per CVE severity |

---

## Classification Quick Reference

Use `M02-decision-trees.md` for formal classification. Short version:

1. Production broken **right now** → HOTFIX
2. CVE or version bump → DEP
3. Bug, not urgent → BUG_FIX
4. New user-visible capability → ENHANCEMENT
5. Internal improvement, no behavior change → REFACTOR

**Rule:** Classification is locked at TRIAGE. Re-classification requires a new M01 entry cross-referencing the original.

---

## Per-Change Instance Records

When a maintenance event occurs, create a new file in this folder:

```
M01-maintenance-workflows/
├── README.md              (this file)
├── HOTFIX.md              (SOP playbook)
├── BUG_FIX.md             (SOP playbook)
├── ENHANCEMENT.md         (SOP playbook)
├── REFACTOR.md            (SOP playbook)
├── DEP.md                 (SOP playbook)
├── HOTFIX-001.md          (instance — created when event occurs)
├── BUG_FIX-001.md         (instance — created when event occurs)
└── ...
```

Instance file naming: `{TYPE}-{NNN}.md` where NNN is zero-padded (001, 002, …).
Use the M12-layer template in `00-Guidelines/A08-layer-templates/12-m-maintenance.md` §M01 for instance structure.

---

*See A04 for full protocol. See M02 for decision trees. See M03 for impact templates.*
