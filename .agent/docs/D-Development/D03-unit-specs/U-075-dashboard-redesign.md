# D03 — Unit Spec: Dashboard Redesign

**Unit ID:** U-075
**Group:** G-UI-POLISH
**Title:** Dashboard redesign
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Redesign the learner dashboard: progress summary cards, course progress bars, recent activity feed, upcoming live sessions, streak/badge section, and quick-access links.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/dashboard.html.twig` | Modify | lms_theme |
| `scss/pages/_dashboard.scss` | Create/Modify | lms_theme |
| `js/dashboard.js` | Create/Modify | lms_theme |
| `src/Controller/DashboardController.php` | Modify | lms_progress |

---

## Acceptance Criteria

- [ ] Stats row: enrolled courses, completed courses, certificates earned, badges
- [ ] In-progress courses: card with progress bar, last lesson link, "Continue" button
- [ ] Recent activity: last 10 actions (lesson completed, quiz taken, badge earned)
- [ ] Upcoming Zoom sessions: next 3 live sessions with join link (if FF_FEAT_ZOOM enabled)
- [ ] Badge/streak section: current streak days, recent badges earned
- [ ] Empty states: friendly message + CTA when no courses enrolled

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | — |

---

## Dependencies

- U-023 (dashboard controller)
- U-024 (my courses / continue learning)
- U-054 (badge entity)
- G-HARDENING complete

**Feature Flag:** None (Zoom section conditional on FF_FEAT_ZOOM)

*End of U-075-dashboard-redesign.md*
