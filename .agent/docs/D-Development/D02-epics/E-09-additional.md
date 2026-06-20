# D02 — Epic Spec: Additional Features

**Epic ID:** E-09
**Features contributed to:** F-015 (Wishlist), F-016 (Course Bundle), F-017 (Notes), F-018 (Badges), F-019 (FAQ), F-020 (Blog)
**Owner:** SA (primary) + Agent_Implementer (executes Stories); CP4 signed off by SA
**Services affected:** lms_wishlist, lms_bundle, lms_note, lms_badge, lms_faq, lms_blog
**Dependencies:** E-01 (Foundation), E-02 (Content)
**Estimated Duration:** 1 week
**Status:** IN_PROGRESS

## Purpose

Deliver enrichment features that improve learner experience without touching the core learning path: Wishlist (bookmark courses), CourseBundle (bundled purchases), Note (student notes with video timestamps), Badge/UserBadge (gamification scaffolding), FAQ (three-scope: global/course/lesson), and Blog (dual-track: public node:blog + admin lms_article entity). All six entity families are scaffolded (as-built at entity/CRUD level); remaining work is badge award logic, which is currently a complete stub.

## Stories

| Story ID | Title | Type | Primary source | Owning module(s) | Service(s) | Feature/AC refs | Dependencies | Est. Effort | Priority |
|---|---|---|---|---|---|---|---|---|---|
| S-09-01 | Wishlist entity + CRUD | module | [C05-modules/lms_wishlist.md](../../C-Design/C05-modules/lms_wishlist.md) | lms_wishlist | lms_wishlist | F-015 | None | 0.5d (as-built) | Should |
| S-09-02 | CourseBundle entity + CRUD | module | [C05-modules/lms_bundle.md](../../C-Design/C05-modules/lms_bundle.md) | lms_bundle | lms_bundle | F-016 | None | 0.5d (as-built) | Should |
| S-09-03 | Note entity + video-timestamp API | module | [C05-modules/lms_note.md](../../C-Design/C05-modules/lms_note.md) | lms_note | lms_note | F-017 | None | 0.5d (as-built) | Should |
| S-09-04 | Badge + UserBadge entities (scaffold) | module | [C05-modules/lms_badge.md](../../C-Design/C05-modules/lms_badge.md) | lms_badge | lms_badge | F-018 | None | 0.5d (as-built) | Could |
| S-09-05 | Badge award logic (criteria evaluator) | module | [C05-modules/lms_badge.md](../../C-Design/C05-modules/lms_badge.md) | lms_badge | lms_badge | F-018 | S-09-04, E-04 (Progress) | 3d | Could |
| S-09-06 | FAQ entity (3-scope) + CRUD | module | [C05-modules/lms_faq.md](../../C-Design/C05-modules/lms_faq.md) | lms_faq | lms_faq | F-019 | None | 0.5d (as-built) | Should |
| S-09-07 | Blog dual-track (node:blog public + lms_article admin) | module | [C05-modules/lms_blog.md](../../C-Design/C05-modules/lms_blog.md) | lms_blog | lms_blog | F-020 | None | 0.5d (as-built) | Should |

## Build Order Within Epic

1. S-09-01, S-09-02, S-09-03, S-09-04, S-09-06, S-09-07 — All entity-scaffold Stories (no intra-Epic dependencies; parallel)
2. S-09-05 — Badge award logic (depends on S-09-04 and E-04 Progress events)

S-09-01 through S-09-04 and S-09-06, S-09-07 have no intra-Epic dependencies and MAY be built in parallel.

## Acceptance Criteria (from B05)

| AC ID | Scenario | Feature | Contributing Story(ies) |
|-------|----------|---------|-------------------------|
| AC-901 | Authenticated user can add/remove course from wishlist | F-015 | S-09-01 |
| AC-902 | Bundle created with multiple courses; bundle purchase enrolls in all | F-016 | S-09-02 |
| AC-903 | Note saved at video timestamp via POST `/api/lms/note/save`; retrieved via GET `/api/lms/note/get?lesson_id=` | F-017 | S-09-03 |
| AC-904 | `/my-notes` groups notes by course | F-017 | S-09-03 |
| AC-905 | Badge entity with `field_criteria` JSON visible in admin; UserBadge entities are createable | F-018 | S-09-04 |
| AC-906 | Badge criteria evaluator awards badge when `courses_completed` threshold met via progress event | F-018 | S-09-05 |
| AC-907 | FAQ items scoped to global/course/lesson display on correct pages | F-019 | S-09-06 |
| AC-908 | Public blog reads `node:blog`; `/manage/blog` CRUD works via `lms_article` entity | F-020 | S-09-07 |

## Integration Points (from B06)

| INT ID | Integration | Contributing Story(ies) | Feature Flag |
|--------|------------|--------------------------|-------------|
| INT-901 | lms_badge criteria evaluator → lms_progress UserCourse progress events | S-09-05 | FF_FEAT_BADGE_AWARD |
| INT-902 | lms_blog public frontend → core node:blog content type | S-09-07 | — |
| INT-903 | lms_bundle → lms_order (bundle items in checkout) | S-09-02 | — |
| INT-904 | lms_mobile_api getCourseDetail → lms_wishlist storage | S-09-01 | — |

## Test Requirements

- **Unit + integration tests per Story:** coverage >= 60% per Story
- **Cross-Story integration tests:** Verify note timestamp saved on lesson view page wires to `lms_note` API (S-09-03 integration with lms_progress lesson view); verify badge award fires on course completion event (S-09-05 + E-04 integration)
- **Cross-Story Integration Test Owner:** S-09-03 — owner of `E03-test-definitions/cross-story/E-09-additional/`
- **Acceptance tests:** AC-901 through AC-908

## Feature Flag Plan

| Flag ID | Story | Description | Risk Level | Rollout Strategy |
|---------|-------|-------------|-----------|-----------------|
| FF_FEAT_BADGE_AWARD | S-09-05 | Enable badge criteria evaluation + automatic award on events | Medium | 0%→10%→100% (after E-04 progress events stable) |

## Implementation Status Notes

| Story | Status | Evidence |
|-------|--------|---------|
| S-09-01 | **as-built** | `lms_wishlist` entity real; CRUD via AdminHtmlRouteProvider; referenced by `lms_mobile_api` getCourseDetail |
| S-09-02 | **as-built** | `lms_bundle` entity real; bundle-course relationship via field_courses |
| S-09-03 | **as-built** | `lms_note` entity + `NoteController` fully real; 4 API endpoints (save/delete/get/my-notes); video timestamp via `field_video_timestamp`; `lms_note_update_10001` adds `field_course` |
| S-09-04 | **as-built (scaffold only)** | `lms_badge` + `lms_user_badge` entity definitions real; `field_criteria` JSON format documented in code; zero award logic |
| S-09-05 | **stub / target-state** | No service, cron, or event subscriber evaluates `field_criteria`; badge award completely absent; must be implemented from scratch |
| S-09-06 | **as-built** | `lms_faq` entity real; 3-scope via taxonomy or reference field |
| S-09-07 | **as-built** | `lms_blog` dual-track: public reads `node:blog`; `lms_article` entity backs `/manage/blog` admin CRUD only |

## CP4 Readiness Criteria

- [ ] Every Story in this Epic has status: DONE
- [ ] G1-G4 passed for every Story
- [ ] Cross-Story integration tests within Epic passing
- [ ] D07 code-map delta complete for all files touched in this Epic
- [ ] E05 traceability complete for every contributed Feature
- [ ] E07 epic retrospective written (E07-retrospectives/)
- [ ] Feature flags registered for FF_FEAT_BADGE_AWARD
- [ ] Open G4 Medium/Low issues logged in E02-issues/ (lms_badge zero award logic — near-stub)
- [ ] Every Story that touched code/config/UI lists relevant `D00-conventions/` files in its D03 or D04 evidence
