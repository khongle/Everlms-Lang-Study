# D03 — Unit Spec: Mobile Responsive Audit

**Unit ID:** U-079
**Group:** G-UI-POLISH
**Title:** Mobile responsive audit
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Full responsive audit across all templates at 375px (mobile), 768px (tablet), and 1440px (desktop). Fix overflow issues, touch target sizing, navigation collapse, and any layout regressions from G-UI-POLISH Phase A-C work.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `scss/breakpoints.scss` | Audit | lms_theme |
| All `templates/*.html.twig` | Audit | lms_theme |
| `scss/components/_nav.scss` | Modify (mobile nav) | lms_theme |
| `scss/pages/*.scss` (all) | Audit + fix | lms_theme |

---

## Audit Checklist

| Page | 375px | 768px | 1440px |
|------|-------|-------|--------|
| Homepage | [ ] | [ ] | [ ] |
| Course catalog | [ ] | [ ] | [ ] |
| Course detail | [ ] | [ ] | [ ] |
| Lesson view | [ ] | [ ] | [ ] |
| Quiz | [ ] | [ ] | [ ] |
| Dashboard | [ ] | [ ] | [ ] |
| Checkout | [ ] | [ ] | [ ] |
| Certificate | [ ] | [ ] | [ ] |
| Forum | [ ] | [ ] | [ ] |
| Profile | [ ] | [ ] | [ ] |

---

## Acceptance Criteria

- [ ] No horizontal scrollbar at any breakpoint
- [ ] Touch targets >= 44×44px (WCAG 2.5.5)
- [ ] Navigation collapses to hamburger menu at <= 768px
- [ ] Tables on mobile: horizontal scroll or card layout (not overflowing)
- [ ] Forms: full-width inputs on mobile
- [ ] Sidebar (lesson view): collapses on mobile with toggle button

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | BrowserStack or Chrome DevTools device emulation |
| G4 Adversarial | PENDING | — |

---

## Dependencies

- Must run AFTER U-071 through U-078 (Phase A-C) are complete

**Feature Flag:** None

*End of U-079-mobile-responsive-audit.md*
