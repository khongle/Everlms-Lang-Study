# D03 — Unit Spec: Forum UI

**Unit ID:** U-080
**Group:** G-UI-POLISH
**Title:** Forum UI
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Final UI polish on forum: topic list with activity indicators, thread view with nested reply styling, rich text editor for posts, like/upvote on replies, and moderator action menu.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/forum-list.html.twig` | Modify | lms_forum |
| `templates/forum-thread.html.twig` | Modify | lms_forum |
| `scss/pages/_forum.scss` | Create/Modify | lms_theme |
| `js/forum.js` | Create | lms_theme |

---

## Acceptance Criteria

- [ ] Topic list: shows reply count, last reply time, author avatar, "Hot" badge for active topics
- [ ] Thread view: replies indented one level, author avatar, timestamp, edit indicator "(edited)"
- [ ] Rich text editor (simple Markdown or CKEditor basic) for new topic/reply
- [ ] Like button on replies (counter, toggle via AJAX, no reload)
- [ ] Moderator action dropdown (pin, delete) visible only to instructors/admins
- [ ] Pagination: 20 replies per page with page controls
- [ ] "Jump to newest reply" button on thread view

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Like CSRF test, IDOR on like action |
| G4 Adversarial | PENDING | XSS via rich text input |

---

## Dependencies

- U-043 (forum thread UI + permissions — functional layer)
- G-HARDENING complete (XSS input sanitization via U-057)

**Feature Flag:** None

*End of U-080-forum-ui.md*
