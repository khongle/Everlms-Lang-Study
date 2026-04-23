# D02 — Group Spec: G-ADDITIONAL

**Group ID:** G-ADDITIONAL
**Group Name:** Additional Features
**Order:** 9
**Units in Scope:** U-051 – U-056
**Dependencies:** G-FOUNDATION
**Estimated Sprints:** 1
**Status:** DONE (scaffold)

---

## Purpose

Các feature bổ sung làm phong phú trải nghiệm người dùng: Wishlist, CourseBundle, Note với video timestamp, Badge/UserBadge gamification, FAQ (3-scope: global/course/lesson), và Blog.

---

## Units

| Unit ID | Name | Status | Gate | Priority |
|---------|------|--------|------|----------|
| U-051 | Wishlist entity | DONE | G4 pass | P2 |
| U-052 | CourseBundle entity | DONE | G4 pass | P2 |
| U-053 | Note entity (video timestamp) | DONE | G4 pass | P2 |
| U-054 | Badge + UserBadge entities | DONE | G4 pass | P2 |
| U-055 | FAQ entity (3-scope) | DONE | G4 pass | P2 |
| U-056 | Blog entity | DONE | G4 pass | P2 |

---

## Build Order Within Group

All units are independent; built in parallel.

---

## Key Modules

- `lms_wishlist` — U-051
- `lms_bundle` — U-052
- `lms_note` — U-053
- `lms_badge` — U-054
- `lms_faq` — U-055
- `lms_blog` — U-056

---

## Exit Criteria

- [x] All 6 entities scaffolded with CRUD
- [x] All G1-G4 gates passed

---

## Key Risks

- None active — group scaffold DONE.
- Note: Badge awarding logic (trigger rules) may need integration with G-PROGRESS events — verify in UI polish phase.

---

*End of 09-G-ADDITIONAL.md*
