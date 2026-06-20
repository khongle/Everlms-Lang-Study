# Task: High-Contrast Theme Variant

**ID:** TASK-218
**Unit:** U-055 (theme)
**Group:** G-ACCESSIBILITY (extension)
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement a high-contrast theme variant that raises color contrast ratios to ≥7:1 (WCAG AAA) for users who need it. The variant activates via two paths: a manual toggle stored in user preferences, and automatic detection of the `prefers-contrast: more` CSS media query.

## Context
- Existing a11y work: TASK-085 (axe test), TASK-107 (WCAG audit), TASK-108 (screen reader), TASK-109 (color contrast — current target 4.5:1).
- High-contrast raises the bar to ≥7:1 on all text/background pairs.
- User pref flag stored alongside other `lms_base` user settings (similar to font-size pref in TASK-220).

## Acceptance Criteria
- [ ] `lms_theme/scss/_high-contrast.scss` exists with overrides for all design tokens that fail ≥7:1
- [ ] `prefers-contrast: more` media query automatically applies the SCSS variant without JS
- [ ] A "High Contrast" toggle is available in the user accessibility preferences UI (route `/user/{uid}/accessibility`)
- [ ] Toggle state persists in `lms_base` user pref storage (cookie fallback for anonymous)
- [ ] `body` receives class `lms--high-contrast` when variant is active; SCSS is scoped to that class
- [ ] All interactive elements (links, buttons, form fields, focus rings) pass ≥7:1 in high-contrast mode
- [ ] Regular theme is unchanged when variant is off
- [ ] axe-core scan (TASK-085 pipeline) passes with no new violations in either mode

## Technical Notes
- Modules: `lms_theme` (SCSS), `lms_base` (pref model + toggle block)
- SCSS strategy: define a `$hc-` palette of token overrides in `_high-contrast.scss`; wrap all rules in `.lms--high-contrast { }` + `@media (prefers-contrast: more) { }` blocks
- JS: small `high-contrast.js` library attached via `lms_theme.libraries.yml`; reads pref from `drupalSettings`, toggles body class, sets cookie
- Pref stored as `lms_user_pref.high_contrast` boolean in `lms_base` UserPreference service
- Do NOT fork the entire theme; use CSS custom property overrides only

## Dependencies
- TASK-107 (WCAG audit baseline — defines which tokens fail)
- TASK-109 (color contrast review — base palette reference)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit for pref service; FE: axe scan in both modes)
- [ ] No new debt introduced
