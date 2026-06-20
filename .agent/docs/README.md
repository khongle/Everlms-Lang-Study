# EverLMS — Project Documentation (`.agent/docs/`)

> Shareable project documentation for **EverLMS**, a full-featured **Drupal 11** Learning Management System (courses, chapters, lessons, quizzes, progress, commerce, certificates, community, AI authoring, and a Flutter mobile app).
>
> **Documentation mode:** *as-built* — every spec is grounded in the live codebase / config export / database as of **2026-06-20**, not aspiration. Where something is a stub, dead code, or planned, it is labelled as such.
>
> **Scope of this rebuild:** layers **A–E** are fully materialized. Layers **F–U** (Release, Governance, Operations, External, System, Knowledge, Maintenance, Userguide) are part of the canonical framework but **not yet materialized** for this project.

## Start here

| If you want to… | Read |
|---|---|
| **Run / rebuild the project** (commands, DDEV, bootstrap sequence) | [A-Project-Information/A01-CLAUDE.md](A-Project-Information/A01-CLAUDE.md) |
| Understand what EverLMS is (vision, scope) | [A-Project-Information/A02-project-overview.md](A-Project-Information/A02-project-overview.md) |
| See current state, gaps & recent work | [A-Project-Information/A03-project-context.md](A-Project-Information/A03-project-context.md) |
| Learn the doc framework (layers, IDs, gates) | [A05-framework-primer.md](A-Project-Information/A05-framework-primer.md) · [A06-workflow-primer.md](A-Project-Information/A06-workflow-primer.md) |
| Browse the 28 modules | [C-Design/C05-modules/_index.md](C-Design/C05-modules/_index.md) |
| Browse the 29 entities & data model | [C-Design/C04-entities/README.md](C-Design/C04-entities/README.md) · [C-Design/C04-entities/data-model.md](C-Design/C04-entities/data-model.md) |
| See every page's implementation contract | [C-Design/C11-pages-components/_index.md](C-Design/C11-pages-components/_index.md) |
| See the build order & delivery status | [D-Development/D01-dev-overview.md](D-Development/D01-dev-overview.md) |
| See the honest implementation scorecard | [E-Quality/E06-implementation-status.md](E-Quality/E06-implementation-status.md) |
| See known defects / tech debt | [E-Quality/E02-issues/README.md](E-Quality/E02-issues/README.md) |

## Layer map (A–E materialized)

| Layer | Folder | Contents |
|---|---|---|
| **A** | [A-Project-Information/](A-Project-Information/) | AI config (A01-CLAUDE), overview, living context, prompt catalog, distilled framework/workflow primers, evolution log |
| **B** | [B-Requirements/](B-Requirements/) | Project & technical requirements, 16 features, 9 business workflows, 16 acceptance-criteria sets, 8 integrations |
| **C** | [C-Design/](C-Design/) | Architecture decision, user roles, entity-module map, 29 entities, 28 modules, API contracts, sitemap, wireframes, skins, prototypes, **C11 per-page implementation specs**, **C12 workflows**, **C13 usecases** |
| **D** | [D-Development/](D-Development/) | Coding conventions (D00), build order/registry (D01), 11 epics, ~35 stories (one per module + cross-cutting), task kanban (D04), session/handoff/code-map |
| **E** | [E-Quality/](E-Quality/) | Test strategy, 18 real-defect issues, test definitions, traceability, implementation status, solution patterns, NFR tracking |

> Layers F–U are reserved canonical slots (see [A05-framework-primer.md](A-Project-Information/A05-framework-primer.md)); references to them are marked "(planned)".

## Key facts (as-built, 2026-06-20)

- **Stack:** Drupal 11.3.5 · PHP 8.3 · MariaDB 10.11 (`everlms_pro`) · DDEV (`everlms.ddev.site`) · Drush 13.7.2 · Bootstrap 5.3.3 (CDN) · mPDF · Stripe SDK · Flutter app (`app/`).
- **Custom code:** 27 `lms_*` modules + `open_switcher` (impersonation utility); 29 custom entities (28 content + `lms_question_type` config, 8 question bundles); 11 taxonomy vocabularies; 137 routes; default theme `lms_theme` (15 homepage skins).
- **Config:** exported to `config/sync/` (396 YAML, git-tracked). DB seed in `database/db.sql.gz` + `.agent/generated/` JSON.
- **Notable architecture:** quiz grading = static `GradingStrategyFactory` (8 strategies); certificates auto-issued via `QuizPassedEvent` subscriber + mPDF; payments via `@PaymentGateway` plugin host (BankTransfer + Stripe real; PayPal/ZaloPay stub; `lms_paypal` dead code; **no webhook endpoints**); AI via `@AiProvider` plugins (OpenAI/Gemini/Pollinations); mobile via custom bearer-token API (`/api/v1`).
- **Top open risks:** third-party secrets committed in config YAML (no Key module); no payment webhooks/signature verification; `lms_badge` award logic absent; notification email/push stubs; ~0% automated test coverage. Full list: [E-Quality/E02-issues/](E-Quality/E02-issues/).

## Provenance

Rebuilt 2026-06-20 from a 13-file ground-truth map of the real codebase (modules, entities/fields from config export, routes/controllers/templates, DB schema, setup/infra, themes & Flutter app). The prior `.agent/docs-draft/` tree informed structure and requirements intent but its stale status labels and deprecated Group/Unit vocabulary were corrected against the live code. Canonical structure authority: `.agent/00-Guidelines/A01-document-structure.md`.
