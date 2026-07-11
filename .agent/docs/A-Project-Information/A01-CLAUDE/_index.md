# A01-CLAUDE - AI Agent Configuration

**Last updated:** 2026-06-21

The agent-config seed for **EverLMS**. Read this file alone and you know the project, how to run/rebuild it, what is banned, the gate order, and the non-negotiable documentation rules. Structure authority lives in `.agent/_LodeStar/00-Guidelines-v6/A01-target-docs-structure.md`; how-to-work in `.agent/_LodeStar/00-Guidelines-v6/A02-agent-operating-protocol.md`.

## Project
- Name: **EverLMS** — a full-featured Drupal 11 Learning Management System (courses, chapters, lessons, quizzes, progress, commerce, certificates, community, AI authoring) with a Flutter mobile app. Shipped demo = occupational-safety training (Vietnamese).
- machine_name: lms — project admin namespace: workspace tại `/workspace/*`, `/manage/*`; admin config tại `/admin/config/lms/*` và `/admin/lms/*` (theo convention Drupal core). Deviation as-built: `lms_payment` dùng `/admin/config/everlms/payment`; `open_switcher` dùng `/admin/switch-user` (route của package) — ghi nhận, không đổi.
- Posture: **rebuild / as-built** — documenting an existing, working system from its live code, config export, and database (not a greenfield brief).
- Created: 2026 (documentation baseline) · Last updated: 2026-06-21
- Project input: `00-project-input/` (raw sources referenced in-place — see its `README.md`)
- docs_language: vi — văn bản dẫn dắt/diễn giải cho người đọc dùng **tiếng Việt**; định danh kỹ thuật giữ tiếng Anh (ID module/entity/field, `P###`/`F-NNN`/`WF-NNN`, config keys, code, route paths, giá trị enum). Domain terms gloss một lần trong `K-Knowledge/K01-domain-glossary/`.
- design_reference: ../Templates — repo design-reference ngang hàng project root (nguồn C09 token + C10 look-and-feel; dùng khi dựng prototype).
- build_tier: level-4-advanced
- (build_tier note) — dùng hierarchical WBS `E##S##T##` (story/task); có D09 queue + A13 coding-navigator (level-4+ MANDATORY trừ khi có ADR waiver).
- framework_path: .agent/_LodeStar/00-Guidelines-v6 — bản copy framework package (Gate 2.E/10.8 resolve từ đây; bản distilled shippable ở `A05-framework-primer`/`A06-workflow-primer`).

## Working surface
- Build docs into: `.agent/docs/` (target tree authoritative in `.agent/_LodeStar/00-Guidelines-v6/A01-target-docs-structure.md`).
- **Source of truth (rebuild precedence):** live **code** (`web/modules/custom/`, `web/themes/custom/`) > **config export** (`config/sync/`) > **database** (`database/db.sql`) > prior docs drafts. No `02-xlsx` normalization layer is used for this project; A-U cite code/config/DB directly.
- Code map: `D-Development/D07-code-map/` mirrors `web/modules/custom/<module>/`.

## Commands
All commands run inside the DDEV web container (`ddev …`). Project type `drupal11`, docroot `web/`, host `https://everlms.ddev.site`.

```bash
# --- Start / rebuild from scratch ---
ddev start                              # spin up containers (nginx-fpm, PHP 8.3, MariaDB 10.11); writes settings.ddev.php
ddev composer install                   # install PHP deps into vendor/
ddev import-db --file=database/db.sql   # import full dump (UNCOMPRESSED .sql; 202 tables, seeded demo)
ddev drush updatedb -y                  # run pending update hooks
ddev drush cr                           # cache:rebuild
ddev drush uli                          # one-time login link (uid 1)
ddev launch                             # open the site in a browser

# --- Config (config_sync_directory = ../config/sync) ---
ddev drush cim -y                       # import config from config/sync/ (396 YAML)
ddev drush cex -y                       # export config to config/sync/ (commit the diff)

# --- Demo / seed content (Drush commands) ---
ddev drush lms:seed-input               # lms_content_tools: load .agent/seed/seed-input.json
ddev drush lms:import                   # lms_content_tools: import .agent/generated/*.json
ddev drush lms:generate-demo            # lms_base: generate demo courses/chapters/lessons
ddev drush lms:mock-certs               # lms_certificate: mock user certificates
ddev drush lms:clean                    # lms_content_tools: wipe generated demo content

# --- Maintenance PHP scripts (repo root scripts/) ---
ddev drush php-script scripts/fix_all_categories.php
ddev drush php-script scripts/fix_empty_categories.php
ddev drush php-script scripts/fix_dummy_quiz_results.php
ddev drush php-script scripts/regenerate_all_certs.php

# --- Lint / quality ---
ddev exec vendor/bin/phpcs --standard=Drupal,DrupalPractice web/modules/custom/

# --- Tests ---
ddev exec vendor/bin/phpunit -c web/core/phpunit.xml.dist web/modules/custom/

# --- Cron ---
ddev drush cron
```

> **Fresh-install alternative (no DB dump):** `ddev start && ddev composer install && ddev drush site:install standard -y && ddev drush cim -y && ddev drush cr`. This builds from `config/sync/` but yields an **empty** catalog — use the demo/seed commands above to populate. The DB-import path is the supported way to reproduce the documented state.

## Target implementation stack
- Platform: **Drupal 11** (core `^11.3`) · PHP **8.3** · DB **MariaDB 10.11** (`db`) · Webserver **nginx-fpm** · Local env **DDEV** (`everlms`, `everlms.ddev.site`).
- Drush **^13.7** · Composer **2** · Install profile **standard**.
- PDF: **mPDF ^8.3** (certificates). Payments: **Stripe SDK** (`lms_stripe`), BankTransfer (core), PayPal/ZaloPay (stub).
- Frontend: default theme **`lms_theme`** (Bootstrap 5.x, Twig, SCSS, 15 homepage skins) + alt theme `lms_classic`; admin theme **Claro**.
- Mobile: **Flutter** app in `app/` (v1.0.10+33) consuming the custom bearer-token API `/api/v1` (`lms_mobile_api`).

## Conventions
- Module/entity IDs: snake_case, single project prefix **`lms_*`** (plus `open_switcher`). Pages: `P###`. See `A05-framework-primer/_index.md` for the full ID registry.
- Coding standards enforced by the Lint command (Drupal + DrupalPractice). Full catalogue in `D-Development/D00-conventions/`.
- Folder-per-slot + index convention per `.agent/_LodeStar/00-Guidelines-v6/A04-layer-contracts/_index.md`.

## Banned patterns
- No hardcoded editorial content in PHP/Twig/JS/CSS (Entity-first Editorial-Usability invariant).
- No secrets/.env committed; **note as-built deviation:** third-party API keys currently live in config YAML (no Key module) — tracked as a risk in `G-Governance/G02-risk-register/` and `E-Quality/E02-issues/`. Do not extend this pattern.
- No renaming canonical IDs/paths; no file↔folder swaps; no parallel aliases (Structure Lock).
- No skipping gates; no "Looks good" in G4 without proof; no editing code outside Story scope without a `G04` change request.

## Gate sequence
- **G1 Build → G2 Lint → G3 Tests → G4 Adversarial.** Strict order; max 3 retries/gate; 3 fails ⇒ BLOCKED + notify SA + STOP.
- Details: `.agent/_LodeStar/00-Guidelines-v6/A06-validation-and-adequacy-gates.md`.
- ⚠️ As-built reality: automated test coverage is ~0% (no PHPUnit suites). G3 currently passes vacuously — treat manual verification + `E-Quality/E03-test-definitions/` as the interim behavior source until suites exist (`E-Quality/E02-issues/`).

## G4 adversarial categories (find ≥1 real issue OR give the proof statement per category)
- SECURITY, ERROR_HANDLING, PERFORMANCE, EDGE_CASES, ARCHITECTURE, DATA_INTEGRITY, MAINTAINABILITY.
- "Looks good" / "No issues found" without proof = auto-fail. Proof format: "Examined [files/functions]. Verified [check]. No issues because [reason]."

## Mandatory documentation rules
- **Entity-first Editorial-Usability invariant:** every business-owned visible surface is editable by a non-developer via a management UI; each `C11` page declares editable source, editor URL, role/permission, validation, revision/moderation/translation policy, and cache tags/contexts invalidated.
- **Structure Lock:** `.agent/_LodeStar/00-Guidelines-v6/A01-target-docs-structure.md` is structure-authoritative; update it FIRST in any structural change-set.
- Update `D07-code-map/` on file create/modify; check `D06-handoff-protocol/` at session start; update `D05-session-status/` at session end.
- Tests are source-of-truth for behavior (after intent-vs-observed triage); feature flags default OFF on first deploy.

## Context loading
- Tier-1 (always load, Phase 3 onward): `A01-CLAUDE/`, `A03-project-context/`, `D05-session-status/`, `D06-handoff-protocol/`.
- Keep total context ≤ 80% of window. Structure-affecting work loads `A01-target-docs-structure.md` directly (not the primer).
