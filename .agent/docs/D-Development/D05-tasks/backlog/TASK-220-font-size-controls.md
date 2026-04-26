# Task: User Font Size Controls

**ID:** TASK-220
**Unit:** U-055 (theme)
**Group:** G-ACCESSIBILITY (extension)
**Priority:** P2
**Estimate:** 4h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Add user-facing font size controls (Aa- / Aa+ / Reset buttons) to the site header and user accessibility preferences page. Size preference persists in a cookie for anonymous users and in `lms_base` user preferences for authenticated users. All font sizes across the UI scale via a single `--lms-font-scale` CSS custom property on `:root`, ranging from 80% to 150%.

## Context
- Existing a11y work: TASK-085 (axe test), TASK-107 (WCAG audit).
- Complements TASK-218 (high-contrast) and TASK-219 (reduced motion) as the third accessibility personalization control.
- Scale range: five steps — 80%, 90%, 100% (default), 120%, 150%.

## Acceptance Criteria
- [ ] "Aa-" / "Aa+" / "Reset" toolbar widget renders in site header (always visible, small footprint)
- [ ] Clicking Aa+ increases scale by one step; Aa- decreases; Reset returns to 100%
- [ ] `:root { --lms-font-scale: 1.0; }` CSS variable set; all `font-size` declarations in `lms_theme` use `calc(Xrem * var(--lms-font-scale))`
- [ ] Preference stored as `lms_user_pref.font_scale` (integer step 1–5) via `lms_base` UserPreference service for authenticated users
- [ ] Cookie `lms_font_scale` (integer 1–5) used as fallback for anonymous
- [ ] JS reads pref from `drupalSettings.lmsA11y.fontScale` and applies `:root` variable immediately on page load (no FOUC)
- [ ] Controls pass keyboard navigation (Tab + Enter/Space) and have visible focus ring
- [ ] Layout does not break at any scale step on all viewport sizes

## Technical Notes
- Modules: `lms_theme` (JS widget + SCSS), `lms_base` (UserPreference service extension)
- New file: `lms_theme/js/font-size-control.js` — lightweight, no framework
- New SCSS partial: `lms_theme/scss/components/_font-size-control.scss`
- All existing `font-size` values in SCSS must be converted from raw `rem` to `calc(Xrem * var(--lms-font-scale))`; do a codebase-wide SCSS search before implementation
- `UserPreferenceService::getFontScale()` / `setFontScale(int $step)` in `lms_base`
- Cookie max-age: 1 year; `SameSite=Lax`

## Dependencies
- TASK-218 (high-contrast user pref — same pref storage pattern to follow)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: pref service; FE: all five scale steps, keyboard nav)
- [ ] No new debt introduced
