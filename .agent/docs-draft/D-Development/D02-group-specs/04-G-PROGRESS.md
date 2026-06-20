# D02 — Group Spec: G-PROGRESS

**Group ID:** G-PROGRESS
**Group Name:** Progress Tracking
**Order:** 4
**Units in Scope:** U-021 – U-024
**Dependencies:** G-CONTENT, G-ASSESSMENT
**Estimated Sprints:** 1
**Status:** DONE (scaffold)

---

## Purpose

Theo dõi tiến trình học tập của user: UserCourse và UserLesson entities, cascade progress service (lesson → chapter → course), learner dashboard, và "My Courses / Continue Learning" flow.

---

## Units

| Unit ID | Name | Status | Gate | Priority |
|---------|------|--------|------|----------|
| U-021 | UserCourse/UserLesson entities | DONE | G4 pass | P0 |
| U-022 | Progress cascade service | DONE | G4 pass | P0 |
| U-023 | Dashboard controller + template | DONE | G4 pass | P0 |
| U-024 | My Courses + Continue learning | DONE | G4 pass | P0 |

---

## Build Order Within Group

1. U-021 — Entities (depends on G-CONTENT, G-ASSESSMENT)
2. U-022 — Progress cascade service (depends on U-021)
3. U-023, U-024 — Dashboard + My Courses (depends on U-021, U-022)

---

## Key Modules

- `lms_progress` — U-021, U-022, U-023, U-024

---

## Exit Criteria

- [x] UserCourse + UserLesson entities with status tracking
- [x] Cascade: lesson complete → chapter progress recalculated → course progress recalculated
- [x] Dashboard showing progress summary
- [x] "Continue learning" resumes last lesson

---

## Key Risks

- None active — scaffold DONE.
- **Critical:** Race condition on concurrent progress updates → addressed in G-HARDENING U-059 (RISK-002).

---

*End of 04-G-PROGRESS.md*
