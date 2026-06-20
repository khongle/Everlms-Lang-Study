# Task: Course Prerequisites Enforcement

**ID:** TASK-115
**Unit:** U-015 (lms_course)
**Group:** G-CONTENT (extension)
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description

Enforce course prerequisites before allowing enrollment. A course may require other courses to be completed (passed final quiz or 100% progress) before a learner can enroll. The enroll button shows a lock icon and lists unmet prerequisites when conditions are not satisfied.

## Context

- Module: `lms_course`
- The `Course` entity needs a multi-value entity reference field `field_prerequisites` pointing to other `lms_course` entities (and optionally `lms_quiz`).
- Enrollment gate must check prerequisite state via `lms_progress` completion records before allowing purchase or free enrollment.
- UI must surface unmet prerequisites so learners know what to do next.

## Acceptance Criteria

- [ ] `field_prerequisites` field added to `lms_course` entity (multi-value, entity reference to `lms_course` and/or `lms_quiz`)
- [ ] Prerequisite check service (`PrerequisiteChecker`) queries `lms_progress` completion for all prereqs
- [ ] Enroll button replaced with lock icon + tooltip when any prerequisite is unmet
- [ ] Unmet prerequisites list displayed as linked course titles below the lock button
- [ ] Completed prerequisites shown with checkmark in the same list
- [ ] Check bypassed for admin and instructor roles
- [ ] Prerequisite config exposed in Course edit form (admin UI)
- [ ] Unit test: 0 prereqs → enrollable; all met → enrollable; 1 unmet → blocked

## Technical Notes

- New service: `web/modules/custom/lms_course/src/PrerequisiteChecker.php`
- Hook into enrollment gate in `EnrollmentManager::canEnroll()` (extend, do not bypass existing checks)
- Template override: `lms-course-enroll-button.html.twig` — add `locked` modifier class
- CSS: add `.enroll-btn--locked` state in `_course.scss`
- Field config in `web/modules/custom/lms_course/config/install/field.field.lms_course.lms_course.field_prerequisites.yml`
- No schema migration needed if field is optional and defaults to empty

## Dependencies

- TASK-027 (user CRUD, roles established)

## Definition of Done

- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing
- [ ] No new debt introduced
