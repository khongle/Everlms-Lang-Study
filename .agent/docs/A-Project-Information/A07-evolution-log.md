# A07 — Evolution Log

> Append-only. Records framework changes applied to this project.
> Detailed diffs live in `M-Maintenance/M05-evolution-log/` (one file per change set).

---

### 2026-04-23 — v0.3.1 — U-Userguide layer materialized + drift fixes

**Source:** SA directive (docs completeness audit)
**Version bump:** PATCH (no structural change; layer materialization + accuracy fixes)
**Affected files:**
- `U-Userguide/` created from empty: U01-user-guide.md, U02-getting-started.md, U03-feature-guides/ (13 guides: 7 learner + 3 instructor + 3 customer + 2 admin), U04-faq.md, U05-troubleshooting.md, U06-tutorials/ (3 tutorials), U07-release-notes/v0.1.0.md, U08-admin-guide.md, U09-html/README.md (build plan)
- `D-Development/D06-handoff-protocol.md`: Tier 1 artifact expanded from 39 → ~160 lines with explicit load order, priority queue, gotchas, handoff checklist
- `S-System/S06-slo-per-service/`: added 4 SLOs (cron, payment-webhook, certificate-verify, checkout-flow) — now 5 total (was 1)
- `I-External/I02-sdk-docs/`: added TypeScript SDK plan + webhook verify utility blueprint (was README stub only)
- Drift fixes (entity/module counts):
  - `A01-CLAUDE.md`: "40+ entities" → "29 entity classes (28 content + 1 config)"; added theme count
  - `C01-architecture-decision.md`: "40+ entities" → precise count
  - `C03-entity-module-map.md`: roll-up header precision
  - `B02-technical-requirements.md`: NFR-011 "23 modules" → "26 lms_* + 1 utility"
  - `E10-nfr-tracking.md`: NFR-011 same fix
  - `D05-tasks/backlog/TASK-016-access-control-hardening.md`: "23 modules" in audit scope updated
- ADR-001, ADR-002: "40+ entities" retained as historical record (no rewrite of architectural reasoning).
**Rationale:** Gap audit identified U-Userguide as the only entirely-missing canonical layer; several Tier 1 / Tier 2 artifacts were stubs; count drift had accumulated (23 scaffolded → 26 lms_* current). Post-change: all 14 canonical layers have content; no canonical slot is empty.

### 2026-04-16 — v0.3.0 — A-Project-Information layer created

**Source:** SA directive (IP separation requirement)
**Version bump:** MINOR
**Affected files:**
- `A-Project-Information/A01-A07`: created as new shareable layer
- Old `A-Guidelines/` removed from `docs/` (was verbatim framework copy)
**Rationale:** Framework files (`00-Guidelines/`) are company IP. Shareable docs must contain only project-specific distilled content. A05/A06 serve as primers replacing need for framework access.

### 2026-04-16 — v0.2.0 — Documentation bulk completion (round 1 + round 2)

**Source:** SA directive (docs gap-fill)
**Version bump:** MINOR
**Affected files:**
- B-Requirements: B04 split (9 WF), B05 split (16 AC), B06 split (8 INT)
- C-Design: C06-apis (8 files), C05-modules (27 per-module files), C10-prototypes register
- D-Development: D02 group-specs (11), D03 unit-specs (84 total: 41 DONE + 43 SPEC), D07 code-map (3 maps), D08 session log format
- E-Quality: E02 issues (5), E03 test-defs (5), E08 patterns (2)
- F-Release: F02 checklists (3 templates + 0.1.0 marker)
- G-Governance: G01 ADRs (10), G02/G03 rewritten to mirror A03 RISK/FIND
- H-Operations: H02 runbooks (7), H06 capacity, H07 observability (10)
- I-External: I01 (4 endpoints), I03 webhooks (2), I04-I06 stubs
- S-System: S01-S06 (17 files incl. 7 outbound contracts, 3 data flows)
- K-Knowledge: K01-K04 enriched
- M-Maintenance: M01 playbooks (5), M03 templates (2)
**Rationale:** Fill all canonical layer slots per A01-document-structure.md. 373 .md files total across B–S.

### 2026-04-15 — v0.1.0 — Initial docs materialization

**Source:** SA directive
**Version bump:** MINOR
**Affected files:**
- All layers B–M scaffolded from docs-draft sources
- 23 modules scaffolded, 47 DB tables, 13 routes, 13 templates
**Rationale:** Bootstrap canonical doc structure from existing draft specs + source code analysis.

---

*End of A07-evolution-log.md*
