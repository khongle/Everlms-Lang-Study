# D02 — Group Spec: G-UI-POLISH

**Group ID:** G-UI-POLISH
**Group Name:** UI/UX Polish
**Order:** 11
**Units in Scope:** U-071 – U-084
**Dependencies:** G-HARDENING (must be fully complete)
**Estimated Sprints:** 2
**Status:** NOT_STARTED
**Priority:** P2

---

## Purpose

Final iteration trên toàn bộ UI/UX: redesign homepage, polish course/lesson/quiz views, dashboard, checkout flow, certificate page, mobile responsive audit, forum/notification UX, profile page, search results, và error/empty states.

---

## Units

| Unit ID | Name | Status | Priority |
|---------|------|--------|----------|
| U-071 | Homepage redesign (final iteration) | SPEC | P2 |
| U-072 | Course detail page polish | SPEC | P2 |
| U-073 | Lesson view UX improvements | SPEC | P2 |
| U-074 | Quiz taking UX | SPEC | P2 |
| U-075 | Dashboard redesign | SPEC | P2 |
| U-076 | Management pages CRUD polish | SPEC | P2 |
| U-077 | Checkout flow UI | SPEC | P2 |
| U-078 | Certificate detail page | SPEC | P2 |
| U-079 | Mobile responsive audit | SPEC | P2 |
| U-080 | Forum UI | SPEC | P2 |
| U-081 | Notification center UI | SPEC | P2 |
| U-082 | Profile page UI | SPEC | P2 |
| U-083 | Search results UX | SPEC | P2 |
| U-084 | Error/empty state pages | SPEC | P2 |

---

## Build Order Within Group

**Phase A — Core pages (parallel):**
- U-071 Homepage, U-072 Course detail, U-073 Lesson view, U-074 Quiz UX

**Phase B — User flows (parallel, after Phase A):**
- U-075 Dashboard, U-077 Checkout UI, U-078 Certificate page

**Phase C — Secondary pages (parallel):**
- U-076 CRUD management, U-080 Forum UI, U-081 Notification center, U-082 Profile

**Phase D — Cross-cutting (after all above):**
- U-079 Mobile responsive audit (touches all pages)
- U-083 Search results, U-084 Error/empty states

---

## Key Modules (theme work)

- `lms_theme` — all units (Twig templates, SCSS, JS)
- Specific controllers: `lms_course`, `lms_quiz`, `lms_progress`, `lms_payment`, `lms_certificate`, `lms_forum`, `lms_notification`

---

## Exit Criteria

- [ ] All 14 pages/flows reviewed against UX spec
- [ ] Mobile responsive verified at 375px, 768px, 1440px breakpoints (U-079)
- [ ] Empty states and error pages consistent (U-084)
- [ ] Lighthouse score >= 80 for key pages (U-071, U-072, U-073)

---

## Key Risks

- Depends on G-HARDENING fully complete — do not start until all U-057–U-070 are DONE.
- U-079 mobile audit may uncover layout regressions in multiple Twig templates.
- U-077 Checkout UI is blocked on U-030 (Checkout workflow) being DONE.

---

*End of 11-G-UI-POLISH.md*
