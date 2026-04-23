# D03 — Unit Spec: Error/Empty State Pages

**Unit ID:** U-084
**Group:** G-UI-POLISH
**Title:** Error/empty state pages
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Design and implement consistent error pages (403, 404, 500) and empty state components for all list pages (no courses, no notifications, no orders, empty dashboard). Use lms_theme illustrations + Bootstrap.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/error/403.html.twig` | Create/Modify | lms_theme |
| `templates/error/404.html.twig` | Create/Modify | lms_theme |
| `templates/error/500.html.twig` | Create/Modify | lms_theme |
| `templates/components/_empty-state.html.twig` | Create | lms_theme |
| `scss/components/_empty-state.scss` | Create | lms_theme |
| `scss/pages/_errors.scss` | Create | lms_theme |

---

## Empty State Catalog

| Page | Trigger | CTA |
|------|---------|-----|
| Dashboard | No enrollments | "Browse Courses" |
| My Courses | No purchases | "Explore Catalog" |
| Notifications | No notifications | "Keep learning to earn notifications" |
| Orders | No orders | "Browse Courses" |
| Certificates | No certificates | "Complete a course to earn one" |
| Forum | No topics | "Start the first discussion" |
| Search results | No matches | "Clear filters" + "Browse all" |

---

## Acceptance Criteria

- [ ] 403 page: "Access Denied" message, back button, login link (if anonymous)
- [ ] 404 page: "Page Not Found" illustration, search bar, "Back to Home"
- [ ] 500 page: "Something went wrong" message, no technical details, support link
- [ ] All empty states use consistent component (illustration + heading + subtext + CTA)
- [ ] Error pages themed with lms_theme (not Drupal default)
- [ ] Error pages accessible (WCAG AA contrast, proper heading hierarchy)

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Verify 403/404/500 render lms_theme not Drupal default |
| G4 Adversarial | PENDING | 500 page must not expose stack trace |

---

## Dependencies

- Must run after all other G-UI-POLISH units to know all empty states needed
- U-067 (error handling standardization) for 500 behavior

**Feature Flag:** None

*End of U-084-error-empty-states.md*
