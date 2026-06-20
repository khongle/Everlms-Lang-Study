# D03 — Story Spec: lms_progress

**Story ID:** lms_progress
**Story type:** module
**Title:** Enrollment & Progress Tracking — user portal pages, lesson view, progress API
**Primary source:** `C-Design/C05-modules/lms_progress.md`
**Owning module(s):** lms_progress
**Service(s):** progress
**Epic:** E-04 (Progress & Learner Portal)
**Contributes to Feature(s):** Learning Progress, Course Management
**Design source(s):** `../C-Design/C05-modules/lms_progress.md`
**Round:** N/A
**Driver:** SA directive
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** (pre-2026-04-15; implemented before D03 layer was materialized)
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_progress`

---

## Session Strategy

**Estimated session count:** 3
**Splitting strategy:**
- S1: Entity definitions (`lms_user_course`, `lms_user_lesson`); routing; module declaration
- S2: `UserPortalController` (dashboard, myCourses, lessonView, myCertificates); `ProgressApiController` (markComplete); template registration
- S3: `lms_progress.module` hooks (form_alter, after_build, submit_redirect, set_comment_parent); Twig templates

All sessions reached a buildable state. G1–G4 not formally recorded (pre-D03 era).

---

## Scope

Tracks enrollment and lesson/course completion progress for learners, and serves the user-facing portal:

- Two content entities: `lms_user_course` (enrollment + course-level progress) and `lms_user_lesson` (per-lesson status + video position). No chapter-level entity; chapter progress is derived.
- `UserPortalController`: five methods across four routes (dashboard, my-courses, lesson view, my-certificates). Auto-enrolls on first lesson view. Resolves video embed URL for YouTube/Vimeo/direct. Handles lesson types (pdf, pptx, video, h5p, zoom, scorm).
- `ProgressApiController`: `markComplete` JSON endpoint; upserts lesson status; recalculates course progress %; sets course to 'completed' at 100%.
- `lms_progress.module`: `hook_theme()` (4 templates); `hook_form_alter()` for `comment.lesson_comment` form (grants formats, hides fields, AJAX wrapper) — contains a known security side-effect bug.
- Four Twig templates (dark glassmorphism theme consistent with site design).

Full design: [`../C-Design/C05-modules/lms_progress.md`](../../C-Design/C05-modules/lms_progress.md)

---

## Source Documents

| Source | Why loaded |
|---|---|
| `../C-Design/C05-modules/lms_progress.md` | Owning module design — authoritative reference |
| `../C-Design/C05-modules/lms_note.md` | Note loaded in lessonView; lms_note storage queried at runtime |
| `../C-Design/C05-modules/lms_quiz.md` | Quiz embedded in lesson view via quiz_id param |

---

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Learners see dashboard with enrolled courses, in-progress, completed stats | B03-features/03-learning-progress.md | Acceptance |
| Lesson view auto-enrolls first-time visitors | B03-features/03-learning-progress.md | Acceptance |
| Progress API marks lesson complete and recalculates course % | B03-features/03-learning-progress.md | Acceptance |
| Lesson view supports video, pdf, pptx, h5p, zoom, scorm lesson types | B03-features/05-content-media.md | Acceptance |
| Lesson comments use AJAX-enabled comment field | B03-features/08-communication.md | Acceptance |
| Certificate list visible from /my-certificates | B03-features/06-certification-badges.md | Acceptance |

---

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_progress/lms_progress.info.yml` | Create | Module declaration; deps: lms_base, lms_course, lms_chapter, lms_lesson | — | S1 |
| `web/modules/custom/lms_progress/lms_progress.routing.yml` | Create | 5 routes: /dashboard, /my-courses, /learn/{course}/{lesson}, /my-certificates, /api/lms/progress/mark-complete | — | S1 |
| `web/modules/custom/lms_progress/src/Entity/UserCourse.php` | Create | lms_user_course entity; 6 base fields; EntityOwnerTrait | — | S1 |
| `web/modules/custom/lms_progress/src/Entity/UserLesson.php` | Create | lms_user_lesson entity; 6 base fields; EntityOwnerTrait | — | S1 |
| `web/modules/custom/lms_progress/src/Controller/UserPortalController.php` | Create | dashboard, myCourses, lessonView, myCertificates, profile (dead) | — | S2 |
| `web/modules/custom/lms_progress/src/Controller/ProgressApiController.php` | Create | markComplete JSON endpoint | — | S2 |
| `web/modules/custom/lms_progress/lms_progress.module` | Create | hook_theme(); hook_form_alter() for lesson comments; helper functions | — | S3 |
| `web/modules/custom/lms_progress/templates/lms-dashboard.html.twig` | Create | vars: stats, course_summary, recent_courses | — | S3 |
| `web/modules/custom/lms_progress/templates/lms-my-courses.html.twig` | Create | vars: courses, summary | — | S3 |
| `web/modules/custom/lms_progress/templates/lms-lesson-view.html.twig` | Create | vars: course, lesson, curriculum, video_position, video_embed_url, quiz_id, is_admin, rendered_attachments, comments, existing_note | — | S3 |
| `web/modules/custom/lms_progress/templates/lms-my-certificates.html.twig` | Create | vars: certificates, summary | — | S3 |

---

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Internal module | Shared entity traits, config |
| lms_course | Internal module | lms_course entity param upcasting in routes |
| lms_chapter | Internal module | Curriculum structure in lessonView |
| lms_lesson | Internal module | lms_lesson entity param upcasting; lesson type logic |
| lms_certificate (runtime, not declared) | Internal module | lessonView queries lms_user_certificate storage |
| lms_note (runtime, not declared) | Internal module | lessonView queries lms_note storage for existing_note |
| ajax_comments (contrib) | External | AJAX-enabled comment library attached in lessonView |

---

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| No services.yml | UserPortalController, ProgressApiController | Controllers use \Drupal::service() static calls (known anti-pattern — DEBT-PROG-03) |
| Form-alter pattern | hook_form_alter for comment.lesson_comment | Side-effect: saves authenticated role entity at form-alter time (BUG-PROG-02) |
| Progress derivation | Chapter progress | No lms_user_chapter entity; chapter progress derived from lesson completions |

---

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission |
|---|---|---|---|---|
| Dashboard | enrolled/in_progress/completed counts, continue URL | lms_user_course entities | (user-facing, not editable) | authenticated |
| Lesson view | lesson title, content, curriculum nav, comments, notes | lms_lesson, lms_chapter, lms_course entities | `/manage/lessons/{id}/edit` (separate module) | authenticated |
| My Certificates | certificate list | lms_user_certificate entities (lms_certificate module) | (read-only) | authenticated |

**Hardcoded content exceptions:** `dashboard()` and `myCourses()` duplicate "find next lesson" logic (DEBT-PROG-01); `profile()` method is not routed (DEBT-PROG-02).

---

## Feature Flags Introduced or Read

None. No feature flags in this module.

---

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-PROG-001 | Integration | lessonView auto-enrolls user on first visit | lms_user_course created with status=enrolled |
| T-PROG-002 | Integration | markComplete sets lesson status=completed; recalculates course progress | Progress % correct; course=completed at 100% |
| T-PROG-003 | Integration | dashboard shows correct stats for user with varied enrollment states | Stats match entity counts |
| T-PROG-004 | Unit | myCertificates loads lms_user_certificate for current user | Certificates list populated |
| T-PROG-005 | Integration | lessonView resolves YouTube/Vimeo video embed URL | video_embed_url correct |

---

## Gate Results

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Entities install; routes resolve; lesson view renders | — | pre-2026-04-15 |
| G2 Lint | PASS | No coding standard violations | — | pre-2026-04-15 |
| G3 Tests | PASS | Dashboard/my-courses/lesson view functional; mark-complete API works | — | pre-2026-04-15 |
| G4 Adversarial | PASS | No blockers at close; open bugs logged below | — | pre-2026-04-15 |

---

## Notes

### Known Bugs (as-built)

| ID | Severity | Description |
|----|----------|-------------|
| BUG-PROG-01 | Medium | `lms_progress_comment_form_submit_redirect()` returns undefined variable `$response` on non-AJAX path when no course/lesson found — PHP undefined variable warning |
| BUG-PROG-02 | High | `hook_form_alter` for lesson comments calls `$role->save()` to grant all text formats to the `authenticated` role at form-alter time — unconditional role mutation on every comment form render (security + performance risk) |

### Technical Debt

| ID | Severity | Description |
|----|----------|-------------|
| DEBT-PROG-01 | Low | `dashboard()` and `myCourses()` share near-identical "find next lesson" logic; should be extracted to a shared private method or service |
| DEBT-PROG-02 | Low | `UserPortalController::profile()` is never routed — dead code |
| DEBT-PROG-03 | Low | Controllers use `\Drupal::service()` static calls instead of injected constructor dependencies |

These bugs feed [E02 Issues](../../E-Quality/E02-issues/).
