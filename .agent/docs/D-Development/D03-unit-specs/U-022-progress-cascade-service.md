# D03 — Unit Spec: Progress Cascade Service

**Unit ID:** U-022
**Group:** G-PROGRESS
**Title:** Progress cascade service
**Status:** DONE (PARTIAL)
**Priority:** P0

---

## Scope

Implements progress cascade logic: when a lesson is marked complete, recalculate and update the parent `UserCourse.field_progress` percentage and transition UserCourse to `completed` when all lessons are done. This logic is currently implemented inline within `ProgressApiController::markComplete()` rather than as a dedicated injectable service class.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_progress/src/Controller/ProgressApiController.php` | Cascade logic in `markComplete()` |
| `web/modules/custom/lms_progress/lms_progress.routing.yml` | `/api/progress/mark-complete` route |

---

## Entities / Services / Controllers / Templates

**Progress cascade flow (inline in `ProgressApiController::markComplete()`):**
1. Mark `lms_user_lesson` as `completed`
2. Load all active lessons across all chapters of the course
3. Count completed lessons for the user
4. Compute `progress_percentage = (completed / total) * 100`
5. Update `lms_user_course.field_progress`
6. If `progress_percentage >= 100`: set `field_status = completed`, record `field_completed_at`

Also computes `next_lesson_url` for client-side redirect.

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Endpoint functional |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Progress updates correctly on lesson complete |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] Lesson completion updates parent UserCourse progress
- [x] UserCourse status transitions to `completed` at 100%
- [x] `field_completed_at` set on completion
- [x] Next lesson URL returned in response
- [ ] Progress logic extracted to dedicated injectable service (PARTIAL — still inline)

---

## Known Debt / Issues

- Cascade logic is embedded in `ProgressApiController` rather than a dedicated `ProgressCascadeService` — refactor tracked in G-HARDENING (U-064 DI cleanup). Also susceptible to race conditions under concurrent requests (U-059).
- Static `\Drupal::` call on line 101 (U-064 debt).

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-021 | UserCourse/UserLesson entities written here |
| U-023 | Dashboard reads progress updated by this |
| U-059 | G-HARDENING: race condition fix needed |
| U-064 | G-HARDENING: DI cleanup of static call |

---

*End of U-022-progress-cascade-service.md*
