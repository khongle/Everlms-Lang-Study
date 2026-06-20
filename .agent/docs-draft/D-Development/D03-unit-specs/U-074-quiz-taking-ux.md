# D03 — Unit Spec: Quiz Taking UX

**Unit ID:** U-074
**Group:** G-UI-POLISH
**Title:** Quiz taking UX
**Status:** SPEC
**Priority:** P2
**Assigned:** unassigned

---

## Scope

Polish the quiz-taking interface: question navigator sidebar, timer display, answer state persistence (auto-save), review screen before submit, and animated result reveal.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `templates/quiz-take.html.twig` | Modify | lms_theme |
| `templates/quiz-review.html.twig` | Create | lms_theme |
| `scss/pages/_quiz.scss` | Create/Modify | lms_theme |
| `js/quiz-player.js` | Create/Modify | lms_theme |

---

## Acceptance Criteria

- [ ] Question navigator: numbered grid, answered = green, current = blue, unanswered = grey
- [ ] Timer: countdown display (if quiz has time limit), red highlight when < 10% time remains
- [ ] Auto-save: answer stored in sessionStorage on selection (survives accidental page refresh)
- [ ] Review screen: lists all questions with selected answers, shows unanswered count, "Submit" button
- [ ] After submit: animated score reveal (e.g., count-up animation to final score)
- [ ] Pass/Fail indicator with course completion trigger if applicable
- [ ] Keyboard accessible: Tab to navigate questions, Enter to select answer

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Auto-save + timer tests |
| G4 Adversarial | PENDING | — |

---

## Dependencies

- U-018 (quiz take controller)
- U-020 (grading service)
- G-HARDENING complete

**Feature Flag:** None

*End of U-074-quiz-taking-ux.md*
