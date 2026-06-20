# D03 — Story Spec: Progress Race Condition Fix

**Story ID:** progress-race-condition
**Story type:** nfr
**Title:** Progress Race Condition — DB lock/transaction on cascade update
**Primary source:** `C-Design/C05-modules/lms_progress.md`
**Owning module(s):** lms_progress
**Service(s):** ProgressCascadeService (lms_progress), ProgressLockService (lms_progress — new)
**Epic:** E-10 (Quality & Hardening)
**Contributes to Feature(s):** F-003 (Learning Progress)
**Design source(s):** `C-Design/C05-modules/lms_progress.md`
**Round:** N/A
**Driver:** NFR gap; G02 risk register (RISK-002 — High, progress race condition on concurrent requests); DEC-003 (open decision: lock vs queue)
**Status:** SPEC
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `story: progress-race-condition` — maps to draft TASK-017, TASK-049 (spike)

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 2-3
**Splitting strategy:**
- S1 (spike): Run TASK-049 spike to verify DEC-003 decision (DB transaction + SELECT FOR UPDATE vs queue-based). Produce a spike report in `D08-sessions/`. Once DEC-003 is closed, open S2.
- S2: Implement `ProgressLockService` + wrap `ProgressCascadeService` in transaction + SELECT FOR UPDATE
- S3: Concurrency regression tests (parallel request simulation)

## Scope (from primary source)

Fix the race condition in `ProgressCascadeService` where concurrent lesson completions from the same user can produce incorrect chapter/course progress percentages.

**How the race manifests:** If a user completes two lessons simultaneously (e.g., fast-clicking or network retry), two concurrent PHP-FPM workers both read the same `user_chapter` progress row, both compute 50% completion independently, and both write 50% — the final value is 50% instead of the correct 100%.

**Resolution (DEC-003 — pending spike):**
- Decision: DB transaction + `SELECT ... FOR UPDATE` on `user_course` and `user_chapter` rows before reading + updating. Queue-based approach was considered but rejected (adds latency for mobile API sync).
- If spike invalidates this decision, update DEC-003 and reopen adequacy before S2.

**Implementation plan:**

1. Create `ProgressLockService` in `lms_progress/src/Service/`:
   - `withLock(int $userId, int $courseId, callable $fn): mixed`
   - Acquires `SELECT ... FOR UPDATE` row-level lock on `user_course` row
   - Executes `$fn` inside `\Drupal::database()->startTransaction()`
   - Lock acquisition timeout: 5s; retry: 2x (log warning on each retry, error on final fail)
   - Releases lock on transaction commit or explicit rollback

2. Modify `ProgressCascadeService::completeLessonAndCascade()`:
   - Wrap the existing read-modify-write cycle inside `ProgressLockService::withLock()`
   - The callable `$fn` performs: `SELECT FOR UPDATE user_course WHERE user=? AND course=?` → compute new progress → `UPDATE user_course SET percentage=?` → cascade to chapter
   - On deadlock: log to watchdog `lms_progress` channel; return existing progress (do not crash)

3. Rollback on any exception inside the transaction: log failure, rethrow as `LmsException` (from Story `hardening-security`)

**Real implementation status (as-built):**
- `ProgressCascadeService` exists and handles cascade logic, but uses no DB transactions or row locking.
- MariaDB 10.11 InnoDB supports `SELECT ... FOR UPDATE` — no infrastructure change needed.
- The `user_course` and `user_chapter` tables use InnoDB with row-level locking available.

## Source Documents

| Source | Why loaded |
|---|---|
| `C-Design/C05-modules/lms_progress.md` | ProgressCascadeService design; user_course entity schema |
| `C-Design/C04-entities/01-content-entities.md` | user_course, user_chapter, user_lesson entity schemas |
| `B02-technical-requirements.md` | NFR-concurrency: concurrent updates must produce correct state |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Concurrent lesson completions for same user+course produce correct final progress | RISK-002 | Non-Functional |
| DB transaction wraps the read-modify-write cycle | DEC-003 | Non-Functional |
| SELECT FOR UPDATE used on user_course row before updating percentage | DEC-003 | Non-Functional |
| Lock acquisition has 5s timeout + 2 retries (no deadlock crash) | RISK-002 | Non-Functional |
| DEC-003 resolved and documented before implementation begins | DEC-003 (open) | Non-Functional |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_progress/src/Service/ProgressLockService.php` | Create | Row-level lock acquisition + transaction wrapper | TASK-017 | — |
| `web/modules/custom/lms_progress/src/Service/ProgressCascadeService.php` | Modify | Wrap cascade update in ProgressLockService::withLock() | TASK-017 | — |
| `web/modules/custom/lms_progress/lms_progress.services.yml` | Modify | Register ProgressLockService | TASK-017 | — |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| DEC-003 must be closed (spike TASK-049 complete) before S2 opens | Pre-condition | D08-sessions/spike-DEC-003 |
| Story `hardening-security` S1 — LmsException hierarchy needed for rollback error reporting | Intra-Epic | this D03-stories/ |
| MariaDB 10.11 InnoDB — `SELECT FOR UPDATE` supported | External | setup-infra.md |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| `D00-conventions/php-drupal.md` | All PHP files | PHPCS Drupal standard clean |
| Constructor injection (no `\Drupal::database()` static call) | ProgressLockService | Verified in DI cleanup |

## Editorial Usability / Entity-First Evidence

Not applicable — this Story modifies backend service logic only; no visible content changes.

## Feature Flags Introduced or Read

| Flag ID | Introduced / Read | Purpose | Rollout plan link |
|---------|-------------------|---------|-------------------|
| — | — | No feature flags; locking is always-on after deploy | — |

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-PRC-001 | Unit | ProgressLockService::withLock() commits transaction on success | TASK-017 AC |
| T-PRC-002 | Unit | ProgressLockService::withLock() rolls back on exception | TASK-017 AC |
| T-PRC-003 | Unit | Lock timeout (>5s) logs warning; retries 2x then fails gracefully | TASK-017 AC |
| T-PRC-004 | Integration | 10 concurrent requests completing lessons for same user+course → correct final progress (100%) | TASK-017 AC (concurrency test) |
| T-PRC-005 | Integration | Single request completes normally with lock enabled — no latency regression > 50ms | TASK-017 AC |
| T-PRC-006 | Integration | Simulate DB exception inside lock → transaction rolls back; progress unchanged | TASK-017 AC |

## Gate Results (per Story, see A02 §3)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PENDING | — | 0/3 | — |
| G2 Lint | PENDING | — | 0/3 | — |
| G3 Tests | PENDING | Coverage: — | 0/3 | — |
| G4 Adversarial | PENDING | 7 categories: deadlock scenarios, lock timeout edge cases | 0/3 | — |

## Notes

- DEC-003 must be resolved before S2 opens. The spike (TASK-049) should take 1–2 days and produce a decision document in `D08-sessions/`.
- MariaDB InnoDB row-level locking works at the primary-key level. The lock is on the `user_course` row `WHERE uid=? AND course_id=?`. This is fine-grained enough to not block other users' progress updates.
- Do NOT use table-level locking (`LOCK TABLES`) — it would serialize all progress updates globally.
- The mobile API (`lms_mobile_api`) also triggers progress updates via `/api/v1/lesson/complete`. This Story must ensure the same `ProgressCascadeService` is called (and thus locked) for mobile API requests too.
- If ProgressLockService detects a deadlock (MySQL errno 1213), it should retry after 100ms up to 2 times before failing. Log each retry at `warning` level.
