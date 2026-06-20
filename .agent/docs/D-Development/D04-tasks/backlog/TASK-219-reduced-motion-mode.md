# Task: Reduced Motion Mode

**ID:** TASK-219
**Unit:** U-055 (theme)
**Group:** G-ACCESSIBILITY (extension)
**Priority:** P2
**Estimate:** 3h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Respect the `prefers-reduced-motion: reduce` OS-level preference by disabling or minimizing all non-essential animations in the LMS front-end: scroll-triggered animations, parallax backgrounds, autoplay carousels, CSS transitions, and JS-driven motion effects.

## Context
- Existing a11y work: TASK-085 (axe test), TASK-107 (WCAG audit), TASK-108 (screen reader).
- WCAG 2.1 SC 2.3.3 (AAA) requires a mechanism to suppress animation; `prefers-reduced-motion` satisfies it at the browser level.
- This is a pure CSS + minimal JS fix; no new backend storage needed.

## Acceptance Criteria
- [ ] `lms_theme/scss/_reduced-motion.scss` created and imported in main SCSS entry
- [ ] All CSS `transition`, `animation`, and `@keyframes` declarations wrapped/overridden in a `@media (prefers-reduced-motion: reduce)` block
- [ ] Parallax scroll effect on hero banner disabled when media query fires
- [ ] Autoplay on carousels (course catalog, testimonials) stopped via JS listener on `matchMedia('(prefers-reduced-motion: reduce)')`
- [ ] Scroll-triggered fade-in/slide-in animations replaced with instant visibility
- [ ] Page behavior verified with OS "Reduce Motion" setting on macOS + Windows
- [ ] No regressions in standard motion mode

## Technical Notes
- Module: `lms_theme` only; no backend changes
- Add `@media (prefers-reduced-motion: reduce)` overrides inside `_reduced-motion.scss`; import at the end of `main.scss` so specificity wins
- Carousels (Splide / Swiper): listen to `window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', ...)` in existing carousel init JS
- Parallax: set `background-attachment: scroll` and zero `transform` under the media query
- Autoplay: call `carousel.pause()` when reduced-motion matches; call `carousel.play()` if user later changes OS setting

## Dependencies
- TASK-085 (axe test pipeline — run scan post-implementation)
- TASK-107 (WCAG audit — baseline for animation inventory)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (CSS lint; manual QA checklist in OS reduced-motion mode)
- [ ] No new debt introduced
