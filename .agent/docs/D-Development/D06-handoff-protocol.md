---
documentation-mode: as-built
last-updated: 2026-06-20
session: 1
---

# D06 — Handoff Protocol

> Instructions for the next session. Updated at the end of every session.
> The incoming session MUST read this file first after Tier 1 context.
> **Rewrite-in-place** — this file always reflects only the next session's instructions.

**Last Updated:** 2026-06-20 (Session 1 — Documentation Rebuild)

---

## Context Load Order

Load these documents in order before starting work:

```
1. A-Project-Information/A01-CLAUDE.md
2. A-Project-Information/A03-project-context.md
3. D-Development/D05-session-status.md  (latest entry — Session 1)
4. THIS FILE (D06-handoff-protocol.md)
5. D-Development/D01-dev-overview.md
6. Phase-specific docs listed below
```

### Phase-Specific Context (next phase: E-10 Hardening — P0 issues first)

```
- D-Development/D02-epics/E-10-hardening.md
- D-Development/D03-stories/lms_payment.md    (S-PAYMENT — IN_PROGRESS)
- D-Development/D03-stories/lms_stripe.md     (S-STRIPE — IN_PROGRESS)
- D-Development/D03-stories/lms_forum.md      (S-FORUM — IN_PROGRESS)
- D-Development/D00-conventions/README.md
- D-Development/D00-conventions/CONV-001-php-strict-types-di.md
- D-Development/D00-conventions/CONV-005-service-plugin-patterns.md
- D-Development/D00-conventions/CONV-007-config-secrets-flags.md
- C-Design/C05-modules/lms_payment.md
- C-Design/C05-modules/lms_stripe.md
- C-Design/C05-modules/lms_forum.md
```

---

## Module Spec Locations

All C05 module specs live at:

```
C-Design/C05-modules/<module-slug>.md   (one file per module, 28 total)
C-Design/C05-modules/_index.md          (roll-up table)
```

Direct links for currently active stories:

| Story | Module Spec | Status |
|-------|------------|--------|
| S-PAYMENT | [C05-modules/lms_payment.md](../C-Design/C05-modules/lms_payment.md) | IN_PROGRESS |
| S-STRIPE | [C05-modules/lms_stripe.md](../C-Design/C05-modules/lms_stripe.md) | IN_PROGRESS |
| S-FORUM | [C05-modules/lms_forum.md](../C-Design/C05-modules/lms_forum.md) | IN_PROGRESS |
| S-RATING | [C05-modules/lms_rating.md](../C-Design/C05-modules/lms_rating.md) | IN_PROGRESS |
| S-NOTIFICATION | [C05-modules/lms_notification.md](../C-Design/C05-modules/lms_notification.md) | IN_PROGRESS |
| S-ZOOM | [C05-modules/lms_zoom.md](../C-Design/C05-modules/lms_zoom.md) | IN_PROGRESS |
| S-AI | [C05-modules/lms_ai.md](../C-Design/C05-modules/lms_ai.md) | IN_PROGRESS |
| S-MOBILE | [C05-modules/lms_mobile_api.md](../C-Design/C05-modules/lms_mobile_api.md) | IN_PROGRESS |
| S-BADGE | [C05-modules/lms_badge.md](../C-Design/C05-modules/lms_badge.md) | STUB |

---

## Current Work State

### Active Story: None (Baseline)

No implementation story was active in Session 1 (documentation rebuild). The next session should target the highest-severity P0 issues from the `IN_PROGRESS` and `STUB` stories.

**Story type:** N/A
**Owning module(s):** N/A
**Status:** BASELINE — documentation layer A–E complete; no implementation Task in progress
**Epic:** Next: E-10 (Hardening) — P0 items first
**Feature(s) contributed to:** N/A
**Active Task(s):** None

### What Was Completed (Session 1)

- Full A–E documentation layer rebuilt from code ground truth (`the 2026-06-20 code ground-truth scan`).
- 28 C05 module specs written (27 `lms_*` + `open_switcher`), all as-built status verified.
- 11 Epics registered in `D02-epics/`; 37 Stories registered in `D03-stories/`.
- 8 coding conventions written in `D00-conventions/` (CONV-001 through CONV-008).
- D01 dev overview rebuilt with canonical Epic/Story/Task vocabulary.
- All stale Group/Unit vocabulary removed.
- 12 known issues (FIND-001 through FIND-012) logged in D01; feeds E-Quality/E02-issues/ once materialized.
- D05 seeded (this session entry), D06 seeded (this file), D08 sessions folder + README + session file created.

### What Remains

1. **P0 — FIND-001:** Implement Stripe webhook endpoint + signature verification (`lms_stripe`, `lms_payment`). Story: S-PAYMENT-HARDENING (E-10).
2. **P0 — FIND-002:** Fix or tombstone `lms_paypal` — wrong interface, missing `@PaymentGateway` annotation, never registered. Story: S-AI-HARDENING (E-10) / S-PAYPAL.
3. **P0 — FIND-003:** Remove or fix `ForumController::viewTopic()` dead code (`throw new \Exception("HARD DEBUG!")`). Story: S-FORUM (E-07).
4. **P0 — FIND-007:** Extract all plaintext secrets from committed config YAML into `drupal/key` module. Story: S-SEC-HARDENING (E-10).
5. **P1 — FIND-005:** Add 3 missing routes to `lms_rating.routing.yml`. Story: S-RATING (E-07).
6. **P1 — FIND-010:** Move Stripe PaymentIntent creation out of `buildForm()` to the submit handler. Story: S-STRIPE.
7. **Cross-cutting:** Materialize E-Quality layer (`E02-issues/`, `E06-implementation-status.md`) — not yet created.
8. **Cross-cutting:** Create `D04-tasks/` bucket files (backlog/todo/in-progress/blocked/in-review/done/closed) and migrate P0 issues to `todo/`.

### Work In Progress

- No code files are partially modified. Documentation rebuild is complete; code changes have not started.

---

## Blockers

| ID | Description | Impact | Workaround |
|----|-------------|--------|------------|
| BLK-001 | `.agent/.env` with Stripe/PayPal test credentials committed — risk of exposure if repo goes public | Blocks S-SEC-HARDENING verification | Extract to `drupal/key` before any public push |
| BLK-002 | `lms_paypal` dead code never discovered by plugin manager — safe to ignore at runtime, but misleading | Blocks S-PAYMENT-HARDENING audit completion | Tombstone or fix interface + annotation |
| BLK-003 | E-Quality layer (E02-issues/) not yet materialized — no formal issue tracker inside docs | FIND-001..012 tracked only in D01 footnotes | Use D01 Known Issues table as interim tracker |

---

## Gate Sequence for Next Implementation Session

```
G1 Build    — ddev drush php:eval "print 'ok';"  (Drupal bootstrap)
G2 Lint     — phpcs --standard=Drupal,DrupalPractice web/modules/custom/<module>/
G3 Tests    — ddev drush test:run <module>  (no test suite exists yet — mark NOT_RUN)
G4 Review   — adversarial review per A02 §4.2 (7 categories: Security, Error Handling,
              Performance, Edge Cases, Architecture, Data Integrity, Maintainability)
```

Note: No PHPUnit test suite exists (`phpunit.xml` absent). G3 will be NOT_RUN until S-SEC-HARDENING or a dedicated test story creates one. Document this honestly in each session's D08 entry.

---

## Decisions Pending SA Input

| Question | Context | Default if No Response |
|----------|---------|------------------------|
| Prioritize S-SEC-HARDENING (FIND-007 secrets) or S-FORUM bug fix (FIND-003) first? | FIND-007 is systemic risk; FIND-003 is a crash bug on a real route | Fix FIND-003 first (crash); then FIND-007 in E-10 sprint |
| Tombstone `lms_paypal` or fix to a real gateway? | Dead code, wrong interface — no active PayPal business requirement visible | Tombstone (add `README.md` with "not implemented", disable module) |
| Badge award logic (S-BADGE) — implement or defer? | Near-stub, no business logic | Defer to E-11 per current plan |
| Materialize E-Quality layer before or after E-10 hardening work? | Issues would ideally live in E02-issues/ not just D01 | Materialize E02-issues/ at start of first E-10 task session |

---

## Gotchas & Notes

- **Vocabulary:** Use Epic / Story / Task everywhere. "Group" and "Unit" are forbidden (appear in old draft, not in canonical docs).
- **`lms_mobile_api` undeclared deps:** `lms_quiz` and `lms_wishlist` are used but not declared in `lms_mobile_api.info.yml`. Add them before any deployment to avoid module enable ordering issues.
- **Stripe PaymentIntent on form BUILD:** `CheckoutForm::buildForm()` calls `\Stripe\PaymentIntent::create()` every page load — will multiply dangling Stripe objects. Fix in S-STRIPE (move to `submitForm()`).
- **Certificate `field_expiry_date`:** Schema discrepancy noted (datetime vs varchar). Verify actual column type against `db-schema.md` facts before writing migrations.
- **`lms_zoom` wrong service id:** `generateSignature()` references a wrong service id — runtime fatal if Zoom signing attempted. Confirm in `C05-modules/lms_zoom.md`.
- **`lms_ai` no rollback:** If `CourseBuilder` queue worker fails mid-way, partial entities remain in DB with no cleanup. No compensation mechanism exists.
- **IP separation:** `.agent/00-Guidelines/` is company-confidential — never copy verbatim into A05/A06 or any public-facing doc. Distilled summaries only.
- **DDEV `.ddev/` is gitignored** — collaborators must run `ddev config` or be given the config separately. This is noted in `A02-project-overview.md`.
- **No test suite exists** — `phpunit.xml` absent. G3 tests are NOT_RUN until a test story is started. First test story should target `lms_quiz` grading strategies (pure PHP, easiest to unit test).

---

## Feature Flag State

| Flag ID | Status | Notes |
|---------|--------|-------|
| — | No active feature flags for next session | Feature flags infrastructure not yet in place (planned layer H, not yet materialized) |

---

## Estimated Remaining Work

| Story | Tasks Remaining | Est. Hours | Gates Remaining |
|-------|-----------------|-----------|-----------------|
| S-FORUM (FIND-003/004) | ~2 tasks | 1–2 h | G1, G2, G4 |
| S-RATING (FIND-005) | ~2 tasks | 2–3 h | G1, G2, G4 |
| S-STRIPE (FIND-010) | ~3 tasks | 3–5 h | G1, G2, G4 |
| S-SEC-HARDENING (FIND-007) | ~5 tasks | 8–12 h | G1, G2, G4 |
| S-PAYMENT (webhooks) | ~4 tasks | 6–10 h | G1, G2, G4 |
| S-MOBILE (dep fix + API hardening) | ~3 tasks | 3–5 h | G1, G2, G4 |
| E-Quality materialization | ~3 tasks | 2 h | — |

---

## Session Priority

1. **First:** Read Tier 1 context (A01, A03, D05 latest, this file, D01).
2. **Then:** Confirm which P0 issue to fix with SA — recommended order: FIND-003 (crash), then FIND-005 (missing routes), then FIND-010 (Stripe build-time PaymentIntent), then FIND-007 (secrets).
3. **Materialize E02-issues/** as part of the first E-10 task session so all FIND-* issues have canonical issue IDs.
4. **If time:** Begin S-FORUM fix — remove `throw new \Exception("HARD DEBUG!")` from `ForumController::viewTopic()` and replace with proper view rendering. Simple, high-impact, P0.
