# Task: Progress Race Condition Fix

**ID:** TASK-017
**Unit:** U-059
**Group:** G-HARDENING
**Priority:** P0
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG (0% — CRITICAL)

## Audit Notes
- **No ProgressManager or ProgressCalculator service exists**. Progress logic lives directly in ProgressApiController::markComplete()
- **Zero database transactions** (`startTransaction()`) anywhere in entire codebase
- **Zero locking mechanisms** (FOR UPDATE, lock service)
- `markComplete()` does check-then-act pattern: query if completed → create/update. Classic TOCTOU race condition
- Per-lesson completion check runs in foreach loop = N+1 query pattern
- Concurrent requests CAN create duplicate `lms_user_lesson` entities
- **Severity: CRITICAL**

## Description
Fix race condition khi concurrent requests update progress. Implement DB lock/transaction strategy.

## Acceptance Criteria
- [ ] Concurrent mark-complete requests không tạo duplicate UserLesson/UserChapter records
- [ ] Progress cascade (lesson→chapter→course) atomic trong 1 transaction
- [ ] Optimistic locking hoặc SELECT FOR UPDATE cho progress updates
- [ ] Retry logic nếu lock contention

## Technical Notes
- Module: `lms_progress`
- Service: `ProgressManager::markComplete()` → wrap trong transaction
- Database: `\Drupal::database()->startTransaction()` + entity load with lock
- Test: concurrent phpunit test (simulate race)

## Dependencies
- U-022 (Progress service) ✅ DONE

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Concurrency test passing
- [ ] No duplicate records under load
