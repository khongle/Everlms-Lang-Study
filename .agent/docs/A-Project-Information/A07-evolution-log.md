# A07 — Evolution Log

> Append-only. Records framework changes and significant documentation rebuilds applied to this project.
> Each entry cites a source (retro / lesson / incident / SA directive) and lists affected files.
> Detailed diffs live in `M-Maintenance/M05-evolution-log/` (one file per change set) — layer M is planned, not yet materialized.
>
> **Edit rule:** prepend new entries at the top (newest first). Never delete or modify existing entries.

---

### 2026-06-20 — v0.4.0 — .agent/docs rebuilt from code/config/db ground truth; canonical A–E layers deep

**Source:** SA directive (ground-truth documentation rebuild)
**Version bump:** MINOR (full layer rebuild, new canonical structure)
**Affected files:**
- `.agent/docs/A-Project-Information/` rebuilt: A01-CLAUDE.md, A02-project-overview.md, A03-project-context.md, A04-prompt-catalog.md, A05-framework-primer.md, A06-workflow-primer.md, A07-evolution-log.md — all seeded from code/config/db facts (`the 2026-06-20 code ground-truth scan` facts base: 13 files covering all 27 lms_* modules + open_switcher, 29 entities, 202 DB tables, 137 routes, config-sync, themes, mobile app)
- `.agent/docs/B-Requirements/` rebuilt deep: B01, B02, B03-features/ (16 feature files), B04-business-workflows/, B05-acceptance-criteria/, B06-integration-requirements/ — statuses corrected from draft's stale DONE/SPEC labels to as-built / planned / stub based on actual code
- `.agent/docs/C-Design/` rebuilt deep: C01-architecture-decision.md, C02-user-roles.md, C03-entity-module-map.md, C04-entities/, C05-modules/ (27 per-module files), C06-apis/, C07-sitemap.md, C09-skins.md, C11-pages-components/, C12-workflows/
- `.agent/docs/D-Development/` rebuilt: D00-conventions/, D01-dev-overview.md, D02-epics/, D03-stories/, D04-tasks/, D05-session-status.md, D06-handoff-protocol.md, D07-code-map/
- `.agent/docs/E-Quality/` rebuilt: E01-test-strategy.md, E02-issues/ (known bugs logged: ForumController HARD DEBUG, lms_rating missing routes, Zoom wrong service id, field_expiry_date varchar/int mismatch, Stripe PaymentIntent on build), E06-implementation-status.md

**Key corrections vs. draft:**
- Vocabulary: eliminated all "Group"/"Unit" references; canonical hierarchy is now Epic ⊇ Story ⊇ Task throughout
- Module count: 27 lms_* + 1 open_switcher (28 dirs total); draft had 23
- Entity count: 29 custom (28 ContentEntity + 1 ConfigEntity = lms_question_type); draft had 40+
- `lms_mobile_api` added (was missing from draft entirely); Flutter app v1.0.10+33, 33 screens, /api/v1/*
- Payment status corrected: BankTransfer = real, Stripe = real (PaymentIntent), PayPal = dead code (wrong interface, never discovered), ZaloPay = stub; no webhook endpoints exist anywhere
- `lms_badge` flagged as near-stub (entities only, zero award logic)
- `lms_paypal` module flagged as dead code
- `lms_notification` email + push flagged as stubs
- Auth mechanism: custom bearer token via keyvalue.expirable + OTP (NOT OAuth2/JWT)
- Certificate issuance: event subscriber on lms_quiz.passed + mPDF (not described in draft)
- Quiz grading: static PHP factory GradingStrategyFactory (NOT a Drupal plugin manager)
- Security risk documented: all third-party secrets in plaintext committed config YAML; no Key module; .env with test creds committed
- Layers F→U deferred (not materialized in this rebuild run); forward references written as "(planned, layer X — not yet materialized)"

**Rationale:** Prior draft tree (`.agent/docs-draft/`) was stale — module/entity counts drifted, implementation statuses were optimistic, `lms_mobile_api` was absent, and "Group/Unit" vocabulary violated canonical Epic/Story/Task convention. This rebuild anchors every claim to code/config/db ground truth so the owner can re-run or rebuild the project from docs alone.

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
- ADR-001, ADR-002: "40+ entities" retained as historical record (no rewrite of architectural reasoning)

**Rationale:** Gap audit identified U-Userguide as the only entirely-missing canonical layer; several Tier 1 / Tier 2 artifacts were stubs; count drift had accumulated (23 scaffolded → 26 lms_* current). Post-change: all 14 canonical layers have content; no canonical slot is empty.

---

### 2026-04-16 — v0.3.0 — A-Project-Information layer created; IP separation enforced

**Source:** SA directive (IP separation requirement)
**Version bump:** MINOR
**Affected files:**
- `A-Project-Information/A01-A07`: created as new shareable layer
- Old `A-Guidelines/` removed from `docs/` (was verbatim framework copy)

**Rationale:** Framework files (`00-Guidelines/`) are company IP. Shareable docs must contain only project-specific distilled content. A05/A06 serve as primers replacing need for framework access by external collaborators.

---

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

---

### 2026-04-15 — v0.1.0 — Initial docs materialization

**Source:** SA directive
**Version bump:** MINOR
**Affected files:**
- All layers B–M scaffolded from docs-draft sources
- 23 modules scaffolded, 47 DB tables, 13 routes, 13 templates

**Rationale:** Bootstrap canonical doc structure from existing draft specs + source code analysis.

---

*End of A07-evolution-log.md*
