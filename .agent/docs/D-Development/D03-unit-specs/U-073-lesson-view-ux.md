# D03 — Unit Spec: Lesson View UX Improvements

**Unit ID:** U-073
**Group:** G-UI-POLISH
**Title:** Lesson view UX improvements
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Improve the lesson viewing experience: sidebar curriculum navigator, video player controls (with timestamp notes), lesson progress bar, next/previous navigation, and mark-complete button.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/lesson-view.html.twig` | Modify | lms_theme |
| `templates/lesson-sidebar.html.twig` | Create | lms_theme |
| `scss/pages/_lesson-view.scss` | Create/Modify | lms_theme |
| `js/lesson-player.js` | Create/Modify | lms_theme |

---

## Acceptance Criteria

- [ ] Left sidebar: collapsible curriculum tree (chapter/lesson), current lesson highlighted
- [ ] Video lessons: custom controls overlay (play/pause, seek, volume, fullscreen, playback speed)
- [ ] Video timestamp: click to add note at current time (uses Note entity U-053)
- [ ] Progress bar below video showing completion percentage
- [ ] "Mark Complete" button updates UserLesson and triggers progress cascade
- [ ] Next/Previous lesson navigation arrows
- [ ] Sidebar collapses to icon-only on mobile

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Mark-complete integration test |
| G4 Adversarial | PENDING | — |

---

## Dependencies

- U-007 (lesson entity + types)
- U-022 (progress cascade)
- U-053 (note entity)
- G-HARDENING complete

**Feature Flag:** None

*End of U-073-lesson-view-ux.md*
