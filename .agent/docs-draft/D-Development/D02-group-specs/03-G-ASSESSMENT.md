# D02 — Group Spec: G-ASSESSMENT

**Group ID:** G-ASSESSMENT
**Group Name:** Assessment Engine
**Order:** 3
**Units in Scope:** U-013 – U-020
**Dependencies:** G-FOUNDATION
**Estimated Sprints:** 2
**Status:** DONE (scaffold)

---

## Purpose

Xây dựng toàn bộ quiz/assessment engine: Quiz entity, 8 question bundles, answer + matching pair entities, QuestionHandlerManager với 8 strategies, QuizResult/QuestionResponse entities, quiz take flow, result view, và grading service (auto-grade + essay routing).

---

## Units

| Unit ID | Name | Status | Gate | Priority |
|---------|------|--------|------|----------|
| U-013 | Quiz entity | DONE | G4 pass | P0 |
| U-014 | Question entity (8 bundles) | DONE | G4 pass | P0 |
| U-015 | Answer + MatchingPair entities | DONE | G4 pass | P0 |
| U-016 | QuestionHandlerManager (8 strategies) | DONE | G4 pass | P0 |
| U-017 | QuizResult + QuestionResponse entities | DONE | G4 pass | P0 |
| U-018 | Quiz take controller + template | DONE | G4 pass | P0 |
| U-019 | Quiz result view + template | DONE | G4 pass | P0 |
| U-020 | Grading service (auto + essay route) | DONE | G4 pass | P0 |

---

## Build Order Within Group

1. U-013 — Quiz entity
2. U-014, U-015 — Question + Answer entities (parallel, depend on U-013)
3. U-016 — QuestionHandlerManager (depends on U-014, U-015)
4. U-017 — QuizResult + QuestionResponse (depends on U-013, U-014)
5. U-020 — Grading service (depends on U-016, U-017)
6. U-018 — Quiz take controller (depends on U-013, U-014, U-016)
7. U-019 — Result view (depends on U-017, U-020)

---

## Key Modules

- `lms_quiz` — U-013, U-017, U-018, U-019, U-020
- `lms_question` — U-014, U-015, U-016

---

## Exit Criteria

- [x] Quiz entity with all question types
- [x] 8 question bundles + handler strategies
- [x] Auto-grading service with essay routing
- [x] Quiz take + result flow end-to-end

---

## Key Risks

- None active — group scaffold DONE.
- Note: Essay grading route only routes to instructor review; AI-assisted grading is U-047/U-048 scope.

---

*End of 03-G-ASSESSMENT.md*
