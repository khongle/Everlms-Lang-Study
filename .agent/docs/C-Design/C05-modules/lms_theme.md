# C05 Module - lms_theme

**Name:** EverLMS front-end theme · **Phase:** 1 · **Epic:** [E01](../../D-Development/D02-epics/E01-foundation.md) · **Status:** done

## Responsibility
The default front-end **theme** (not a module): Twig templates by area, SCSS/CSS, JS, 16 regions, 21 asset libraries, 15 selectable homepage skins, and `theme-settings.php` for branding. `base theme: false` (standalone; bundles its own Bootstrap-based styling).

## Owned / related entities
- None (presentation only; data/access stay in modules — see [C09](../C09-themes/_index.md) scope boundary).

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-024](../../B-Requirements/B03-features/F-024-theming-and-homepage-skins.md) | Theming & homepage skins |

## Business workflows (B04)
- —

## Integrations / APIs (B06 / C06)
- —

## Backing pages
- Renders all front-end pages (see [C11](../C11-pages-components/_index.md)); admin uses Claro.

## Cross references
- Design system: [C09 themes](../C09-themes/_index.md); wireframes: [C08](../C08-wireframes/_index.md). Alt theme: `lms_classic`.

## Dependencies
- Drupal core theme system; `core_version_requirement: ^11`.

## Implementation notes
- 16 regions (header, primary_menu, secondary_menu, hero, breadcrumb, highlighted, content_top, content, content_bottom, sidebar_first/second, footer_first..fourth, footer_bottom).
- Thin `lms_theme.theme` preprocess; SCSS compiled to CSS; `package.json` for the build toolchain.
