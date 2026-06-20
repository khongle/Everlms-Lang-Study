# EverLMS — Project Documentation (`.agent/docs/`)

> Shareable, development-grade documentation for **EverLMS**, a full-featured **Drupal 11** Learning Management System (courses, chapters, lessons, quizzes, progress, custom commerce, certificates, community, AI authoring) with a companion **Flutter** mobile app.
>
> **Documentation mode:** *as-built / rebuild* — every spec is grounded in the live codebase, `config/sync/` export, and database as of **2026-06-21**. Stubs, dead code, and gaps are labelled honestly.
>
> **Build:** full **A–U + Z** tree, regenerated fresh from source per `.agent/00-Guidelines/A01-target-docs-structure.md`.

## Start here
| If you want to… | Read |
|---|---|
| **Run / rebuild the project** (DDEV, commands, bootstrap) | [A01-CLAUDE](A-Project-Information/A01-CLAUDE/_index.md) |
| Understand what EverLMS is (vision, scope) | [A02-project-overview](A-Project-Information/A02-project-overview/_index.md) |
| See current state, decisions, blockers, next steps | [A03-project-context](A-Project-Information/A03-project-context/_index.md) |
| Browse requirements (24 features, 10 workflows, 9 integrations) | [B-Requirements](B-Requirements/B03-features/_index.md) |
| Understand the architecture | [C01-architecture-decision](C-Design/C01-architecture-decision/_index.md) |
| Browse the 28 modules / 29 entities | [C05-modules](C-Design/C05-modules/_index.md) · [C04-entities](C-Design/C04-entities/_index.md) |
| See every page's implementation contract | [C11-pages-components](C-Design/C11-pages-components/_index.md) |
| See the build order & delivery status | [D01-dev-overview](D-Development/D01-dev-overview/_index.md) |
| See the honest implementation scorecard | [E06-implementation-status](E-Quality/E06-implementation-status/_index.md) |
| See known defects / risks | [E02-issues](E-Quality/E02-issues/README.md) · [G02-risk-register](G-Governance/G02-risk-register/_index.md) |
| See the readiness verdict | [Z-Reviews](Z-Reviews/README.md) → **GO-WITH-FIXES** |
| Use the mobile API | [I01-public-api-spec](I-External/I01-public-api-spec/README.md) |

## Layer map
| Layer | Folder | Contents |
|---|---|---|
| **00** | [00-project-input/](00-project-input/README.md) | Raw-source index (code/config/DB/app referenced in place) |
| **A** | [A-Project-Information/](A-Project-Information/) | AI config, overview, living context, prompt catalog, primers, evolution log |
| **B** | [B-Requirements/](B-Requirements/) | Project/technical reqs, 24 features, 10 workflows, 24 AC, 9 integrations |
| **C** | [C-Design/](C-Design/) | Architecture, roles, 29 entities, 28 modules, APIs, sitemap (51 pages), page/block contracts, workflows, usecases, steps, solution workbook |
| **D** | [D-Development/](D-Development/) | Conventions, 13 epics, 29 stories, kanban tasks, code-map, queue, sessions |
| **E** | [E-Quality/](E-Quality/) | Test strategy, 10 issues, test defs, traceability, status, NFR |
| **F** | [F-Release/](F-Release/) | Deployment, release checklist, changelog, comms |
| **G** | [G-Governance/](G-Governance/) | 5 ADRs, risk register, blockers, change requests |
| **H** | [H-Operations/](H-Operations/) | Incidents, runbooks, deps, tech-debt, flags, capacity, observability |
| **I** | [I-External/](I-External/) | Mobile API spec, SDK, webhooks, portal, changelog, rate limits, migration |
| **K** | [K-Knowledge/](K-Knowledge/) | Glossary, coding conventions, architecture patterns, lessons |
| **M** | [M-Maintenance/](M-Maintenance/) | Maintenance workflows, decision trees, impact templates, monitoring, evolution |
| **S** | [S-System/](S-System/) | Service catalog, contracts, topology, data-flow, deps matrix, SLOs |
| **U** | [U-Userguide/](U-Userguide/) | User/admin guides, getting-started, 24 feature guides, FAQ, troubleshooting, tutorials |
| **Z** | [Z-Reviews/](Z-Reviews/README.md) | Independent build-readiness audits |

## Key facts (as-built, 2026-06-21)
- **Stack:** Drupal 11 (^11.3) · PHP 8.3 · MariaDB 10.11 · nginx-fpm · DDEV (`everlms.ddev.site`) · Drush ^13.7 · mPDF · Stripe SDK · theme `lms_theme` (16 regions, 15 skins) · Flutter app (`app/`).
- **Custom code:** 27 `lms_*` modules + `open_switcher`; 29 entities (28 content + `lms_question_type` config, 8 question bundles); 11 vocabularies; 7 roles; ~137 routes; mobile `/api/v1` (~22 routes).
- **Architecture:** modular monolith; plugin hosts (`@PaymentGateway`, `@AiProvider`); quiz grading via `GradingStrategyFactory`; certificates via `QuizPassedEvent` + mPDF; custom commerce (no Drupal Commerce).
- **Seed data (live DB):** 40 courses · 176 chapters · 600 lessons · 40 quizzes · 158 questions · 20 orders · 81 enrollments · 39 users · 24 certificates · 79 ratings.
- **Top risks (P0):** secrets in config YAML (R-001), no payment webhooks (R-002). **P1:** badge awarding (R-003), ~0% tests (R-006), email/push (R-005). Full list: [G02](G-Governance/G02-risk-register/_index.md).

## Provenance
Rebuilt 2026-06-21 fresh from source (live **code > config export > database**); prior drafts (`.agent/docs-draft`, `.agent/docs-draft-1`) used only as reference and demoted to [00-project-input](00-project-input/README.md). Structure authority: `.agent/00-Guidelines/A01-target-docs-structure.md`. No secrets/PII reproduced anywhere in this tree.
