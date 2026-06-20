# C09-themes - Theme and Design System

**Last updated:** 2026-06-21 · **source_state:** AS_BUILT (`web/themes/custom/lms_theme/`)

Presentation architecture only — business data, route ownership, access, and workflow logic stay in modules/C11.

## Direction

### Canonical decision table
| Concern | Decision |
| ------- | -------- |
| Theme machine name | `lms_theme` (default front-end); `lms_classic` (alternate); Claro (admin) |
| Owning module | theme (not a module); thin `lms_theme.theme` preprocess |
| Visual source | live site / [C10](../C10-prototypes/_index.md) |
| Page/block source | [C11](../C11-pages-components/_index.md) (canonical) |
| Wireframe source | [C08](../C08-wireframes/_index.md) |
| Base theme | `false` (standalone; bundles Bootstrap-based styling via own libraries) |
| Posture | server-rendered Twig + SCSS; 15 selectable homepage skins; responsive |

### Three-theme model
- **fe (front-end):** `lms_theme` — public + learner surfaces (catalog, player, dashboard).
- **be (back-office):** `lms_theme` workspace surfaces (`/workspace`, `/manage`) — same theme, staff layouts. (`lms_classic` is an alternate fe skin.)
- **admin:** Claro — Drupal config/admin (`/admin/*`).
- Mobile is the **Flutter app** (separate client, not a theme); print/export = mPDF for certificates.

### Non-goals
- No business logic, access decisions, or entity queries in Twig — those live in controllers/Views/route access.

## Tokens

### Token scopes
| Scope | Audience | Base palette | Notes |
| ----- | -------- | ------------ | ----- |
| `--fe` | public + learners | `{brand}` primary/secondary | 15 skins re-map these |
| `--be` | staff workspace | neutral + `{brand}` accents | denser tables |
| `--brand` | shared identity | `{brand}` | logo/colors via theme settings |
| `print` | certificates (mPDF) | high-contrast | PDF-safe |

### Core variables
Typography (heading/body families, scale), spacing scale, radius, color (primary/secondary/surface), semantic states (success/warn/error/info), component directions (cards, buttons, forms). Skin = a token re-map selected in theme settings.

### Status tokens
| Status | Color rule | Text/icon rule |
| ------ | ---------- | -------------- |
| success (passed/paid/completed) | green | "Passed"/"Paid" label + check icon |
| warning (pending/partial) | amber | "Pending" label + clock icon |
| error (failed/declined/revoked) | red | "Failed" label + alert icon |
| info (draft/new) | blue | "Draft" label + dot |
Color is never the only indicator (color-blind safe).

### SDC contract
Props carry prepared display values only; slots carry render arrays; render metadata (cache tags/contexts) preserved through components (course_card, curriculum, KPI tiles).

## Drupal theme notes
- **Theme files:** `lms_theme.info.yml` (16 regions, libraries), `lms_theme.libraries.yml` (21 libraries), thin `lms_theme.theme` (preprocess), `templates/` (by area: home, user, navigation, block, page, content, layout, form, misc), SCSS compiled to `css/`.
- **Render ownership:** data/access/cache stay in route/controller/form/View/block; Twig owns markup/classes/slots.
- **Region inventory (16):** `header`, `primary_menu`, `secondary_menu`, `hero`, `breadcrumb`, `highlighted`, `content_top`, `content`, `content_bottom`, `sidebar_first`, `sidebar_second`, `footer_first`, `footer_second`, `footer_third`, `footer_fourth`, `footer_bottom`.
- **Asset libraries:** `lms_theme/global` global; 20 feature libraries attached per page/component.
- **Theme negotiation:** `lms_theme` for front-end + workspace; Claro for `/admin/*`.
- **Accessibility:** semantic HTML, alt text on images, keyboard-navigable quiz/player, WCAG 2.1 AA target (B02).
- **QA pages:** homepage (all skins), catalog, player, quiz, checkout, certificate PDF.

## Scope boundary
| In C09 | Outside C09 |
| ------ | ----------- |
| tokens, regions, components, libraries, negotiation, a11y, skins | business data, route ownership, access decisions, queries, workflow logic (modules/C11) |
