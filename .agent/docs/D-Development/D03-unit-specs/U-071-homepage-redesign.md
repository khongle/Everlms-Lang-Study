# D03 — Unit Spec: Homepage Redesign (Final Iteration)

**Unit ID:** U-071
**Group:** G-UI-POLISH
**Title:** Homepage redesign (final iteration)
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Final homepage design: hero section with CTA, featured courses carousel, categories grid, testimonials, stats bar (learners, courses, instructors). Responsive at all breakpoints. Bootstrap 5.3 + lms_theme.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/homepage.html.twig` | Modify | lms_theme |
| `src/Controller/HomepageController.php` | Modify | lms_course |
| `scss/pages/_homepage.scss` | Create/Modify | lms_theme |
| `js/homepage.js` | Create/Modify | lms_theme |

---

## Acceptance Criteria

- [ ] Hero: headline, subheadline, "Browse Courses" CTA button
- [ ] Featured courses: 4-6 course cards (enrolled indicator for logged-in users)
- [ ] Categories grid with icons, course count per category
- [ ] Stats bar: total learners, courses, instructors (from AnalyticsService)
- [ ] Lighthouse performance >= 80 (mobile)
- [ ] Renders correctly at 375px (mobile), 768px (tablet), 1440px (desktop)

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Visual regression at 3 breakpoints |
| G4 Adversarial | PENDING | — |

---

## Dependencies

- G-HARDENING fully complete
- U-009 (catalog service for featured courses)
- U-046 (analytics for stats)

**Feature Flag:** None

*End of U-071-homepage-redesign.md*
