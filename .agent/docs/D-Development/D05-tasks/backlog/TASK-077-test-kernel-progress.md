# Task: Kernel Test Suite — G-PROGRESS (Cascade + Concurrency)

**ID:** TASK-077
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P1
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Xây dựng PHPUnit kernel test suite cho `lms_progress` module — kiểm tra toàn bộ progress cascade từ Lesson → Chapter → Course. Suite này bao gồm hai phase: (1) unit/kernel tests cho cascade logic thuần, và (2) concurrency regression tests sau khi PROTO-001 landing (TASK-049) xác định strategy locking.

Phase 2 (concurrency) chỉ được merge sau khi DEC-003 resolved và TASK-017 (locking implementation) complete. Tuy nhiên test skeleton cần được chuẩn bị ngay để unblock parallel development.

## Acceptance Criteria
- [ ] **Phase 1 — cascade logic:**
  - [ ] Lesson mark-complete → Chapter progress % recalculated correctly
  - [ ] All lessons in Chapter complete → Chapter marked complete, Course progress % updated
  - [ ] All chapters in Course complete → Course marked complete, `CourseCompletedEvent` fired
  - [ ] Lesson un-complete → cascade rollback (Chapter + Course revert)
  - [ ] Boundary: Course với 0 lessons → không lỗi, progress = 0%
- [ ] **Phase 2 — concurrency (after TASK-049 + TASK-017):**
  - [ ] Simulate 10 concurrent mark-complete requests cho cùng 1 lesson (pcntl_fork hoặc mock)
  - [ ] Verify 0 duplicate `CourseCompletedEvent` dispatched
  - [ ] Verify final `score` không bị race-corrupted
- [ ] Coverage ≥80% trên `ProgressService`, `ProgressCascadeHandler`
- [ ] Annotated `@group TEST-003` theo E03 catalog
- [ ] Pass trong DDEV (`ddev phpunit --group kernel-progress`)

## Technical Notes
- Module: `lms_progress`
- Key files:
  - `web/modules/custom/lms_progress/src/ProgressService.php`
  - `web/modules/custom/lms_progress/src/EventSubscriber/ProgressCascadeHandler.php`
  - `web/modules/custom/lms_progress/src/Entity/LmsProgress.php`
- Test path: `web/modules/custom/lms_progress/tests/src/Kernel/`
- Phase 2 test: dùng `Drupal\Core\Database\Connection` với transaction isolation test
- Locking strategy (Option A / B / C) xác định bởi PROTO-001 → ảnh hưởng mock setup
- Concurrency test cần `pcntl` extension hoặc mock parallel dispatcher

## Dependencies
- TASK-049 (PROTO-001 spike — xác định locking strategy, Phase 2 prerequisite)
- TASK-017 (locking implementation — Phase 2 prerequisite)
- TASK-067 (test catalog — TEST-003)
- TASK-069 (CI gate G2)

## Definition of Done
- [ ] Phase 1 test complete + passing
- [ ] Phase 2 skeleton merged (skipped until TASK-017 done)
- [ ] G1-G4 gates passed
- [ ] Coverage ≥80% on ProgressService
- [ ] CI integrated
