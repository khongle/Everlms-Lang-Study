# D03 — Unit Spec: UserCourse / UserLesson Entities

**Unit ID:** U-021
**Group:** G-PROGRESS
**Title:** UserCourse/UserLesson entities
**Status:** DONE
**Priority:** P0

---

## Scope

Implements the `lms_user_course` and `lms_user_lesson` custom content entities that track enrollment status and lesson completion state per user. These are the core data structures for all progress tracking in the system.

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_progress/lms_progress.info.yml` | Module declaration |
| `web/modules/custom/lms_progress/lms_progress.module` | Hook implementations |
| `web/modules/custom/lms_progress/src/Entity/UserCourse.php` | UserCourse entity class |
| `web/modules/custom/lms_progress/src/Entity/UserLesson.php` | UserLesson entity class |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_user_course` (`UserCourse extends ContentEntityBase`)
- Key fields: `uid`, `field_course` (ref: `lms_course`), `field_status` (`enrolled`/`completed`), `field_progress` (integer 0-100), `field_completed_at`

**Entity:** `lms_user_lesson` (`UserLesson extends ContentEntityBase`)
- Key fields: `uid`, `field_lesson` (ref: `lms_lesson`), `field_status` (`in_progress`/`completed`), `field_video_position` (integer), `field_completed_at`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Both entity schemas install correctly |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Entities created on enrollment and lesson completion |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_user_course` entity created on course enrollment
- [x] `lms_user_lesson` entity created/updated on lesson completion
- [x] `field_progress` on UserCourse stores 0-100 percentage
- [x] `field_video_position` stores last-watched video timestamp
- [x] `field_completed_at` timestamps populated when status → `completed`

---

## Known Debt / Issues

- Race condition risk on concurrent lesson completion updates (tracked as U-059 in G-HARDENING).

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-005 | Course entity referenced by UserCourse |
| U-007 | Lesson entity referenced by UserLesson |
| U-022 | Progress cascade reads/writes these entities |
| U-023 | Dashboard controller reads these entities |

---

*End of U-021-user-course-lesson-entities.md*
