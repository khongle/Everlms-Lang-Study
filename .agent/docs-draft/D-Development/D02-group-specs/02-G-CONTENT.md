# D02 — Group Spec: G-CONTENT

**Group ID:** G-CONTENT
**Group Name:** Content Layer
**Order:** 2
**Units in Scope:** U-005 – U-012
**Dependencies:** G-FOUNDATION
**Estimated Sprints:** 2
**Status:** DONE (scaffold)

---

## Purpose

Xây dựng toàn bộ content layer: Course, Chapter, Lesson entities (7 lesson types), CRUD controllers, catalog page, category management, course detail page, và homepage controller. Đây là backbone của LMS — mọi feature khác phụ thuộc vào content layer.

---

## Units

| Unit ID | Name | Status | Gate | Priority |
|---------|------|--------|------|----------|
| U-005 | Course entity + handlers | DONE | G4 pass | P0 |
| U-006 | Chapter entity + handlers | DONE | G4 pass | P0 |
| U-007 | Lesson entity (7 types) + handlers | DONE | G4 pass | P0 |
| U-008 | Course CRUD controller + templates | DONE | G4 pass | P0 |
| U-009 | Catalog page + search/filter | DONE | G4 pass | P0 |
| U-010 | Category management | DONE | G4 pass | P0 |
| U-011 | Course detail page | DONE | G4 pass | P0 |
| U-012 | Homepage controller + template | DONE | G4 pass | P0 |

---

## Build Order Within Group

1. U-005, U-006, U-007 — Entities (parallel, no inter-dependency)
2. U-010 — Category management (depends on U-005)
3. U-008 — Course CRUD (depends on U-005, U-006, U-007)
4. U-009 — Catalog + search (depends on U-005, U-010)
5. U-011 — Course detail (depends on U-005, U-006, U-007)
6. U-012 — Homepage (depends on U-009, U-011)

---

## Key Modules

- `lms_course` — U-005, U-008, U-009, U-010, U-011, U-012
- `lms_chapter` — U-006
- `lms_lesson` — U-007

---

## Exit Criteria

- [x] Course/Chapter/Lesson entities with all 7 lesson bundles
- [x] CRUD controllers for course management
- [x] Catalog page with search/filter functional
- [x] Category taxonomy management
- [x] Course detail + homepage templates rendering

---

## Key Risks

- None active — group scaffold DONE.
- Note: "scaffold" status means entity+routes+templates exist but some business logic may be stubbed. G-HARDENING audit required.

---

*End of 02-G-CONTENT.md*
