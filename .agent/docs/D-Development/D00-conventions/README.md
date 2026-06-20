# D00 — Development Conventions

> **Purpose:** Cross-cutting implementation rules for EverLMS (Drupal 11, PHP 8.3). Load these files before any coding task. Conventions apply at every layer: Epics, Stories, Tasks, implementation, review, and maintenance.

Conventions are **as-built** unless marked otherwise. When a convention conflicts with a C01 ADR or C05 module spec, the design doc wins and D00 must be corrected in the same change set.

---

## Project-Defining Rules

| # | File | One-line rule |
|---|------|---------------|
| 1 | [`CONV-001-php-strict-types-di.md`](CONV-001-php-strict-types-di.md) | Every PHP file declares `strict_types=1`; services and controllers use constructor-promoted DI — no `\Drupal::` statics inside injectable classes. |
| 2 | [`CONV-002-entity-conventions.md`](CONV-002-entity-conventions.md) | Content entities use the PHP 8 `#[ContentEntityType]` attribute; all handlers include `views_data => EntityViewsData`; bundles are config entities via `#[ConfigEntityType]`. |
| 3 | [`CONV-003-twig-first-rendering.md`](CONV-003-twig-first-rendering.md) | All HTML output goes through registered `#theme` render arrays and Twig templates in `templates/` — never `#markup` with raw HTML. |
| 4 | [`CONV-004-routing-controllers.md`](CONV-004-routing-controllers.md) | Routes belong to one of four named groups (public / app / manage / api); controllers are thin and delegate business logic to services; `/api/v1` routes declare `_access: TRUE` with bearer-token validation inside the controller. |
| 5 | [`CONV-005-service-plugin-patterns.md`](CONV-005-service-plugin-patterns.md) | Reusable logic goes in named services; use Drupal Plugin API only for open extension points; static PHP factory only for closed strategy sets. |
| 6 | [`CONV-007-config-secrets-flags.md`](CONV-007-config-secrets-flags.md) | Secrets are never committed in config YAML — always use Key module or env vars. |
| 7 | [`CONV-008-events-cross-module.md`](CONV-008-events-cross-module.md) | Cross-module communication uses Symfony events, hooks, or `RouteSubscriber` — never direct inter-module internal calls. |

---

## Implementation Standards

| Convention | Topic |
|------------|-------|
| [`CONV-001-php-strict-types-di.md`](CONV-001-php-strict-types-di.md) | PHP 8.3 strict types, `final`, constructor promotion, `use`-statements, no static service locator in injectable classes. |
| [`CONV-002-entity-conventions.md`](CONV-002-entity-conventions.md) | `#[ContentEntityType]` annotation shape, `baseFieldDefinitions`, handlers (including `views_data`), `entity_keys`, `links`, `field_ui_base_route`; bundle-via-config-entity pattern. |
| [`CONV-003-twig-first-rendering.md`](CONV-003-twig-first-rendering.md) | `#theme` render arrays, `templates/` directory naming, `hook_theme()`, no `#markup` raw HTML, theme suggestions. |
| [`CONV-004-routing-controllers.md`](CONV-004-routing-controllers.md) | Route groups public / app / manage / api, access requirements per group, thin controllers, `/api/v1` `_access: TRUE` + in-controller bearer-token check. |
| [`CONV-005-service-plugin-patterns.md`](CONV-005-service-plugin-patterns.md) | Named Drupal services vs. Drupal plugin managers vs. static PHP factory; when to use each; `services.yml` registration rules. |
| [`CONV-006-naming-conventions.md`](CONV-006-naming-conventions.md) | Consistent naming across PHP classes, service IDs, config YAML, CSS class names, and Twig template hooks. |
| [`CONV-007-config-secrets-flags.md`](CONV-007-config-secrets-flags.md) | Config YAML for structure/defaults only — secrets must never be committed; Key module or env vars required. |
| [`CONV-008-events-cross-module.md`](CONV-008-events-cross-module.md) | Cross-module communication via Symfony EventDispatcher, entity/hook API, and `RouteSubscriber`; never call another module's internals directly. |

---

## How To Use

1. Before starting a Story or Task, open the convention files that cover the touched surface area.
2. In D04 task acceptance, list the conventions checked under `## Conventions checked`.
3. During review, cite convention file + section when rejecting a pattern.
4. When a reusable rule emerges from implementation or review, add or update the relevant D00 file and note the trigger (issue ID, session, or ADR).

---

## Adding A Convention

1. Create `CONV-NNN-<slug>.md` in this folder.
2. Open with **Rule + Why** in 2–3 lines.
3. Reference the owning ADR when the rule is architectural.
4. Give **one canonical pattern**, not a catalogue of alternatives.
5. Add a **Don't** list (3–5 items).
6. Add a **Checklist** (5–8 items).
7. Update this README table.

---

## Planned Conventions (not yet materialized)

The following topics from the Drupal 11 D00 standard set have not been written yet. Add them as implementation work touches the area.

| Topic | Slug |
|-------|------|
| Update hooks (`hook_update_N`, `hook_post_update_NAME`, idempotence) | `CONV-009-update-hooks.md` |
| Caching (cache tags, contexts, max-age, lazy-builders) | `CONV-010-caching-render-arrays.md` |
| Security (XSS, CSRF, SQL placeholders, access checks) | `CONV-011-security.md` |
| Forms (`ConfigFormBase`, `EntityForm`, `FormBase`, validation lifecycle) | `CONV-012-forms.md` |
| Permissions and roles (`*.permissions.yml`, role granularity) | `CONV-013-permissions-roles.md` |
| Tests (Unit / Kernel / Functional selection) | `CONV-014-tests.md` |
| Logging (per-module channels, severity ladder, no secrets/PII) | `CONV-015-logging.md` |
| CSS / JS (library ordering, `Drupal.behaviors`, `once()`) | `CONV-016-css-js.md` |
| i18n / translation (`t()` for chrome, entity/config translation) | `CONV-017-i18n.md` |
