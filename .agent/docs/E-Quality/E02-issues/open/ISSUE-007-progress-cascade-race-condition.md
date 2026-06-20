# ISSUE-007 — Progress Cascade Race Condition (No DB Lock / Transaction on Concurrent Lesson Complete)

| Field | Value |
|---|---|
| ID | `ISSUE-007` |
| Title | Concurrent `mark-complete` requests for the same user+course produce incorrect progress percentage — no DB lock or transaction |
| Source | G4-Data Integrity / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `HIGH` |
| Affected page / route | `POST /api/lms/progress/mark-complete`, `POST /api/v1/progress/mark-complete` |
| Owning Epic | E-10 (G-HARDENING) |
| Owning Story | [`progress-race-condition`](../../../D-Development/D03-stories/progress-race-condition.md) |
| Owning module(s) | `lms_progress`, `lms_mobile_api` |

## Summary

Both `ProgressApiController::markComplete()` (web, `lms_progress`) and `MobileLearningController::markLessonComplete()` (mobile API, `lms_mobile_api`) implement a **read-modify-write** cycle on `lms_user_course.field_progress` without any database-level locking or transaction:

1. Read all `lms_user_lesson` records for the user+course to count completed lessons.
2. Compute `field_progress = floor(completed / total * 100)`.
3. Write `field_progress` back to `lms_user_course`.
4. If `field_progress == 100`, set `field_status = 'completed'`.

Under concurrent requests (e.g., a student rapidly taps "Mark Complete" on multiple lessons in the Flutter app, or a network retry causes duplicate POSTs), two PHP-FPM workers can execute this cycle simultaneously with the same initial read state. Both workers compute a stale progress value and write it; the second write clobbers the first, producing an incorrect (lower) progress percentage.

**Concrete scenario:**
- Course has 4 lessons; user has completed 2.
- Two concurrent `mark-complete` calls arrive for lessons 3 and 4.
- Worker A reads: 2 completed → marks lesson 3 complete → counts 3 completed → writes 75%.
- Worker B reads: 2 completed → marks lesson 4 complete → counts 3 completed → writes 75%.
- Final state: `field_progress = 75%` instead of 100%; `field_status` remains `in_progress`; course completion and certificate issuance are never triggered.

This same pattern exists in both the web controller and the mobile API controller independently (code is not shared).

## Reproduction

1. Set up a course with 4 lessons. Enroll a user; mark 2 lessons complete via the web UI.
2. Simultaneously POST to `POST /api/lms/progress/mark-complete` from two browser tabs or two concurrent `curl` calls:
   ```
   curl -X POST /api/lms/progress/mark-complete -d '{"course_id":1,"lesson_id":3}'
   curl -X POST /api/lms/progress/mark-complete -d '{"course_id":1,"lesson_id":4}'
   ```
   (Send both within the same 50ms window.)
3. Observe `lms_user_course.field_progress` in the DB — value is 75% rather than 100%.
4. Confirm no `lms_user_certificate` was created (certificate issuance requires `field_status = 'completed'`).

## Root Cause

The `ProgressApiController::markComplete()` method (and its mobile equivalent) performs the read-modify-write cycle using Drupal entity API calls — entity storage queries and saves — which by default do not acquire row-level locks in MariaDB InnoDB. There is no `\Drupal::database()->startTransaction()` wrapper and no `SELECT ... FOR UPDATE` statement locking the `lms_user_course` row before the read.

The mobile API controller (`MobileLearningController`) duplicates the same logic independently, compounding the issue — a fix to `lms_progress` would not automatically cover the mobile path.

MariaDB 10.11 InnoDB supports `SELECT ... FOR UPDATE` row-level locking; no infrastructure changes are needed, only code changes.

## Proposed Fix

Per Story [`progress-race-condition`](../../../D-Development/D03-stories/progress-race-condition.md):

1. **Create `ProgressLockService`** in `web/modules/custom/lms_progress/src/Service/ProgressLockService.php`:
   - `withLock(int $userId, int $courseId, callable $fn): mixed`
   - Opens a database transaction (`\Drupal::database()->startTransaction()`).
   - Executes `SELECT id FROM {lms_user_course} WHERE uid=? AND field_course_target_id=? FOR UPDATE` to acquire a row-level lock.
   - Calls the `$fn` callback (the read-modify-write logic).
   - Commits transaction on success; rolls back on exception.
   - Lock timeout: 5 seconds; retry: 2×; logs warning on each retry.

2. **Wrap both controllers** in `ProgressLockService::withLock()`:
   - `ProgressApiController::markComplete()` in `lms_progress`.
   - `MobileLearningController::markLessonComplete()` in `lms_mobile_api`.

3. **Extract shared progress logic** into a service (`ProgressCascadeService` or similar) that both controllers call — eliminates code duplication and ensures the lock is always applied.

4. **Register `ProgressLockService`** in `lms_progress.services.yml` with constructor injection of `@database`.

## Trace links

- **Story:** `D-Development/D03-stories/progress-race-condition.md`
- **C05 modules:** `C-Design/C05-modules/lms_progress.md`, `C-Design/C05-modules/lms_mobile_api.md`
- **Facts base:** `modules-progress-comms.md` §ProgressApiController; `modules-mobile.md` §MobileLearningController::markLessonComplete
- **Related issues:** [ISSUE-006](ISSUE-006-progress-formalt-grants-textformats.md) (other lms_progress bug), [ISSUE-008](ISSUE-008-mobile-api-undeclared-deps.md) (lms_mobile_api structural issues that share the same session scope)
- **K04:** Reusable rule — any read-modify-write cycle on a shared entity field under concurrent load requires a DB transaction + SELECT FOR UPDATE. Drupal entity API alone does not provide optimistic or pessimistic locking.
