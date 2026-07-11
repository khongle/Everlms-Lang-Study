# ADR-007 - Single shared theme instead of one theme per route group

**Status:** Accepted (as-built) · **Date:** 2026-07-11

## Context
The framework's theme-per-route-group convention expects a distinct Drupal theme package per core route group (`<machine_name>_public`, `<machine_name>_manage`, conditionally `<machine_name>_app`), with Admin left on the untouched core default. As-built, `web/themes/custom/` ships exactly two theme packages: **`lms_theme`** (default front-end) and **`lms_classic`** (an alternate skin/theme, not a route-group theme). There is no `lms_public`, `lms_manage`, or `lms_app` package — `lms_theme` alone renders **Public** (catalog, blog, marketing), **App** (dashboard, learner "My *" pages, lesson/quiz player), and **Manage** (`/workspace/*`, `/manage/*`) surfaces, distinguished internally by the `--fe` (front-end: Public + App) and `--be` (back-office: Manage) token scopes and by per-area Twig templates/regions, not by separate theme negotiation.

## Decision
Accept **one physical theme, three logical route-group roles** as the as-built pattern:
- `lms_public` and `lms_app` are logical roles both served by `lms_theme` under the `--fe` token scope (same navigation chrome, same 15 homepage skins apply site-wide, not just to marketing pages).
- `lms_manage` is a logical role also served by `lms_theme`, under the `--be` token scope (denser tables, workspace chrome) — same theme package, different templates/regions/token scope, not a separate `.info.yml`.
- Admin (`/admin/*`) remains the Drupal core default admin theme (Claro), completely untouched — no custom admin theme, no admin-scoped template/library/CSS override.
- Theme negotiation (`hook_theme_suggestions` / route-based) picks `lms_theme` for every front-end + workspace route and leaves `/admin/*` on Claro; there is no negotiation logic that swaps in a second custom theme.

Rationale: EverLMS's Public/App/Manage surfaces share one navigation system, one component library (`course_card`, KPI tiles, etc.) and one skin mechanism; splitting them into separate theme packages would duplicate `templates/`, `libraries.yml` and SCSS for no reuse benefit at this project's scale — a single theme with scoped tokens and per-area templates was simpler to build and maintain.

## Consequences
- One `.info.yml`/`.libraries.yml`/`.theme` to maintain instead of two or three; shared component library (`course_card`, `curriculum`, KPI tiles) never drifts between Public/App/Manage.
- No hard negotiation boundary between Public/App and Manage — a template or region bleeding into the wrong surface is caught by code review / QA, not by theme isolation.
- `lms_classic` exists as a genuinely separate, selectable **alternate** front-end theme (a full skin swap), independent of this route-group question.
- C09 documents `lms_public` / `lms_manage` / `lms_app` as logical roles (token scope + template area) inside `lms_theme`, not as separate theme machine names, per this ADR — closing Gate 6.C3 without misrepresenting the as-built theme count.
