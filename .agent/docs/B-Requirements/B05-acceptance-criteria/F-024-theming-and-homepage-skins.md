# F-024 Acceptance Criteria - Theming & homepage skins

## Core criteria
1. **Given** `lms_theme` is the default theme, **when** any front-end page renders, **then** it uses the theme's Twig templates and Bootstrap styling.
2. **Given** 15 homepage skins, **when** an admin selects a skin in theme settings, **then** the homepage renders in that skin.
3. **Given** homepage content blocks/regions, **when** an editor edits them, **then** the homepage updates (no hardcoded copy).
4. **Given** the admin area, **when** accessed, **then** it uses the Claro admin theme.

## Evidence expected
- Theme: [lms_theme](../../C-Design/C05-modules/lms_theme.md); [C09 themes](../../C-Design/C09-themes/_index.md).
- Test: skin-switch smoke (each of 15); homepage editor-usability smoke (E03).
