# 00-project-input - Raw Source Index (EverLMS)

**Last updated:** 2026-06-21
**Documentation mode:** *as-built / rebuild* — EverLMS is an **existing** Drupal 11 system being documented from its live code, configuration export, and database, not a greenfield brief.

This folder is the **raw-source drop zone** that every `A-U` layer is derived from. Because EverLMS is documented *in place* (the source of truth is the working repository itself), raw evidence is **referenced by in-repo path** rather than copied into `_reference/` — this avoids duplicating a ~21 MB database dump and a full codebase into the shareable docs tree, and keeps SENSITIVE artifacts (DB dumps, signing keys) out of `.agent/docs/`. See `_reference/README.md` for that decision and the integrity rule.

> **Provenance precedence (rebuild):** live **code** > **config export** (`config/sync/`) > **database** (`database/db.sql`) > prior **docs drafts**. Where a draft and the code disagree, the code wins and the discrepancy is recorded in the citing doc as `INFERENCE`.

## Source index

| Source ID | Path (repo-relative) | Type | Status | Contains | Sensitive | Last checked |
|---|---|---|---|---|---|---|
| SRC-01 | `web/modules/custom/` | Legacy/as-built code | current | 27 `lms_*` modules + `open_switcher`; entities, controllers, services, plugins, routing, forms, events | N | 2026-06-21 |
| SRC-02 | `config/sync/` | Config export (`drush cex`) | current | 396 YAML: 29 entity types, 48 fields, 53 blocks, 15 views, 11 vocabularies, 7 roles, 6 menus, 8 question-type bundles | N | 2026-06-21 |
| SRC-03 | `database/db.sql` | DB dump (full) | current | 202 tables incl. 52 `lms_*` tables + Drupal core; seeded demo data (40 courses, 600 lessons, 39 users) | **Y** | 2026-06-21 |
| SRC-04 | `database/db_bk_version_1.sql` | DB dump (prior backup) | reference | Earlier DB snapshot (~17 MB); historical comparison only | **Y** | 2026-06-21 |
| SRC-05 | `web/themes/custom/lms_theme/` | Theme / front-end | current | Default theme: Twig templates (by area), SCSS/CSS, JS, `theme-settings.php`, 15 homepage skins | N | 2026-06-21 |
| SRC-06 | `web/themes/custom/lms_classic/` | Theme (alt) | current | Secondary/classic theme | N | 2026-06-21 |
| SRC-07 | `app/` | Mobile app (Flutter) | current | `everlms` Flutter app v1.0.10+33; ~330 feature Dart files, core, l10n, Firebase; consumes `/api/v1` | partial | 2026-06-21 |
| SRC-08 | `composer.json`, `composer.lock` | Dependency manifest | current | Drupal core ^11.3, Drush ^13.7, mPDF ^8.3, Stripe SDK; PHP 8.3 | N | 2026-06-21 |
| SRC-09 | `.ddev/config.yaml` | Infra / local env | current | DDEV project `everlms`, drupal11, nginx-fpm, PHP 8.3, MariaDB; host `everlms.ddev.site` | N | 2026-06-21 |
| SRC-10 | `.agent/generated/` | Seed/content generators (JSON) | current | `manifest.json` + per-domain JSON (courses, quizzes, users, certificates, community, commerce) used by `lms_content_tools` | N | 2026-06-21 |
| SRC-11 | `.agent/seed/seed-input.json` | Seed input brief | current | Domain seed brief (occupational-safety training catalog) | N | 2026-06-21 |
| SRC-12 | `.agent/prompts/` | Content/import guides | reference | `CONTENT_GENERATION.md`, `IMPORT_GUIDE.md` — how demo content is generated/imported | N | 2026-06-21 |
| SRC-13 | `.agent/rules/`, `.agent/workflows/` | Project working rules | reference | Behaviour/code/design/theme rules; ops workflows (bootstrap, backup, test, …) | N | 2026-06-21 |
| SRC-14 | `.agent/docs-draft-1/` | Prior docs draft (A–E, newest) | superseded-by-this-build | As-built A–E tree (2026-06-20), 715 files; structure intent + requirements | N | 2026-06-21 |
| SRC-15 | `.agent/docs-draft/` | Prior docs draft (A–U, older) | superseded-by-this-build | Full A–U scaffolding, 669 files; F–U intent | N | 2026-06-21 |
| SRC-16 | `recipes/` | Drupal recipes | reference | Install-time recipe(s) | N | 2026-06-21 |

## Domain summary (from SRC-11 / SRC-10)

EverLMS is shipped as a **Learning Management System for occupational-safety / workplace-safety training** (Vietnamese: *an toàn lao động*). The seeded catalog (5 hero courses + scaled demo to 40) covers general workplace safety, working-at-height, electrical safety on site, safe operation of machinery, and first-aid/emergency response. The platform itself is domain-agnostic; the seed defines the shipped demo.

## How downstream layers cite these sources

- `C-Design/C04-entities`, `C05-modules` ← SRC-01 + SRC-02
- `C-Design/C02-user-roles` ← SRC-02 (`user.role.*`)
- `B-Requirements` features/workflows ← SRC-01 behaviour + SRC-11 domain + SRC-14 intent
- Data-derived counts (40 courses, 600 lessons, …) ← SRC-03 live DB (`ddev drush sqlq`)
- Mobile API (`I-External`, `C06`) ← SRC-01 `lms_mobile_api` + SRC-07

> **No secrets, ever.** No credential, token, connection string, signing key, or PII value is copied from any source into this index or any `A-U` doc. SRC-03/04 (DB dumps) and key material under SRC-07 (`app/*.p8`, `app/file.keystore`, `app/keystore.txt`) are flagged SENSITIVE and live only in the repo, never in the shared docs tree.

## Cross-check log
Sổ đối soát nguồn ↔ docs (disposition ledger). Cập nhật khi một source được ingest/verify/reconcile.

| Date | Source | Disposition | Note |
| ---- | ------ | ----------- | ---- |
| 2026-06-21 | SRC-01 live code `web/modules/custom/` | INGESTED | Nguồn as-built chính (28 module); mirror sang C05/D07 |
| 2026-06-21 | SRC-02 `config/sync/` (396 YAML) | INGESTED | Roles/fields/entities/vocab → C02/C03/C04 |
| 2026-06-21 | SRC-03 `database/db.sql` | INGESTED | Seed counts → README baseline |
| 2026-07-11 | C10 prototype `C-Design/C10-prototypes/html/` | RECONCILED | Nối C11 pages (Message copy/Responsive) vs prototype |
