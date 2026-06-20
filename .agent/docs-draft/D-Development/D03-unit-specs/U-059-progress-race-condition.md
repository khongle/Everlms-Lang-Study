# D03 — Unit Spec: Progress Race Condition Fix

**Unit ID:** U-059
**Group:** G-HARDENING
**Title:** Progress race condition fix (DB lock/transaction)
**Status:** SPEC
**Priority:** P0
**Assigned:** unassigned
**Risk Ref:** RISK-002 (High — Progress race condition on concurrent requests)

---

## Scope

Fix the race condition in `ProgressCascadeService` (U-022) where concurrent lesson completions from the same user can result in incorrect chapter/course progress. Resolve DEC-003: use DB transaction + SELECT FOR UPDATE lock (not queue, to keep latency low).

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/ProgressCascadeService.php` | Modify | lms_progress |
| `src/Service/ProgressLockService.php` | Create | lms_progress |

---

## Interfaces / Services

- Wrap progress update in `\Drupal::database()->startTransaction()`
- Use `SELECT ... FOR UPDATE` on `user_course` and `user_chapter` rows before reading + updating
- `ProgressLockService::withLock(int $userId, int $courseId, callable $fn): mixed`
  - Acquires row-level lock, executes `$fn`, releases on transaction commit
- Rollback on exception; log failure to watchdog

---

## Acceptance Criteria

- [ ] Concurrent lesson completions for same user+course result in correct final progress (not 50% drift)
- [ ] DB transaction wraps the read-modify-write cycle for progress
- [ ] `SELECT FOR UPDATE` used on user_course row before updating percentage
- [ ] No deadlock: lock acquisition has timeout (5s) + retry (2x)
- [ ] DEC-003 resolved and documented
- [ ] Regression test: simulate 10 concurrent requests, verify final state is consistent

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | Concurrency test with parallel requests |
| G4 Adversarial | PENDING | — |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-022 | ProgressCascadeService — modified by this unit |

---

**Feature Flag:** None
**Resolves:** DEC-003 (Open Decision — lock vs queue strategy)

*End of U-059-progress-race-condition.md*
