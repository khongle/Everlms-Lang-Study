# F-024 - Theming & homepage skins

**Status:** built
**Primary module:** [lms_theme](../../C-Design/C05-modules/lms_theme.md) (theme)
**Epic:** [E01 Foundation](../../D-Development/D02-epics/E01-foundation.md)
**Related workflows:** —
**Acceptance:** [B05 F-024](../B05-acceptance-criteria/F-024-theming-and-homepage-skins.md)
**Source:** `web/themes/custom/lms_theme/`, `web/themes/custom/lms_classic/`

## Requirement
The platform ships a branded front-end theme (`lms_theme`, Bootstrap 5.x, Twig/SCSS) with **15 selectable homepage skins** and a secondary `lms_classic` theme. Admins choose a skin and branding via theme settings (`theme-settings.php`); homepage content is editor-managed (no hardcoded copy).

## Business value
Strong, switchable branding lets each deployment look bespoke without code changes — important for reselling/white-labelling the LMS.

## Scope notes
- `lms_theme` templates organised by area (home, user, navigation, block, page, content…).
- 15 homepage skins selectable; homepage regions/blocks editable.
- Admin theme is Claro; `lms_theme` is default front-end.

## Cross references
- Design: [C09 themes](../../C-Design/C09-themes/_index.md), [C11 pages/blocks](../../C-Design/C11-pages-components/_index.md)
