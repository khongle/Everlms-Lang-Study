# D03 — Unit Spec: Course Detail Page Polish

**Unit ID:** U-072
**Group:** G-UI-POLISH
**Title:** Course detail page polish
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Final polish on course detail page: hero with video preview, curriculum accordion (chapter/lesson tree), instructor profile sidebar, ratings summary, enroll/buy CTA, and sticky purchase box on desktop.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/course-detail.html.twig` | Modify | lms_theme |
| `scss/pages/_course-detail.scss` | Create/Modify | lms_theme |
| `js/course-detail.js` | Create/Modify | lms_theme |

---

## Acceptance Criteria

- [ ] Course hero: title, short description, ratings, enrolled count, video preview thumbnail
- [ ] Curriculum: expandable accordion, lesson icons by type (video/text/quiz/live)
- [ ] Instructor card: avatar, name, bio, course count
- [ ] Rating summary: star distribution bar chart
- [ ] CTA: "Enroll Now" (free) or "Buy Course" (paid) or "Continue" (enrolled)
- [ ] Sticky purchase box on desktop (position: sticky, top: 80px)
- [ ] Mobile: purchase box at bottom of page

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

- U-011 (course detail controller)
- U-041 (rating entity)
- G-HARDENING complete

**Feature Flag:** None

*End of U-072-course-detail-polish.md*
