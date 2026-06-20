# Task: Empty State CTAs

**ID:** TASK-225
**Unit:** U-055 (theme)
**Group:** G-ONBOARDING
**Priority:** P1
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Every list or grid view in the LMS must show a meaningful empty state when there is no data, rather than a blank page or a raw "No results" string. Each empty state consists of: (a) an illustrative icon/SVG, (b) a clear headline, (c) a short body explanation, and (d) a primary CTA button. Apply to: My Courses, Orders, Certificates, Wishlist, Notes, Badges, and Search Results.

## Context
- Existing onboarding: WF-007, TASK-059, TASK-222–224.
- Currently most views show Drupal default "No content available" — not user-friendly and kills conversion.
- A shared Twig macro keeps all empty states consistent.

## Acceptance Criteria
- [ ] Twig include macro `lms_theme/templates/components/_empty-state.html.twig` defined with variables: `icon`, `headline`, `body`, `cta_label`, `cta_url`
- [ ] Empty state applied to all seven pages: My Courses (`/my-courses`), Orders (`/orders`), Certificates (`/certificates`), Wishlist (`/wishlist`), Notes (`/notes`), Badges (`/badges`), Search Results (`/search`)
- [ ] Each page has a unique `icon`, `headline`, `body`, and `cta_url` appropriate to the context (e.g., My Courses empty → "Start learning" → `/courses`)
- [ ] CTA buttons on empty states are tracked via `data-ga-event` attributes for analytics
- [ ] Empty state renders correctly on mobile (≤768px) and desktop
- [ ] Existing views with data are completely unaffected
- [ ] No hardcoded text — all strings use `t()` / Drupal translatable strings for i18n (TASK-110 series)

## Technical Notes
- Module: `lms_theme` (macro template, SCSS, per-view twig override)
- Macro: `lms_theme/templates/components/_empty-state.html.twig` — called via `{% include %}` in each view template
- SCSS: `lms_theme/scss/components/_empty-state.scss` — centered layout, max-width 400px, icon 80px
- Icons: SVG inline in `lms_theme/images/empty/` (one per context); reuse if icon fits multiple contexts
- View templates to update: `views-view--my-courses--page.html.twig`, `views-view--orders--page.html.twig`, etc. — add `{% if rows is empty %}` guard
- CTA urls must use `path()` Twig function, not hardcoded strings

## Dependencies
- TASK-110 (i18n baseline — strings must be translatable)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (FE: all seven empty state pages render correctly with no data)
- [ ] No new debt introduced
