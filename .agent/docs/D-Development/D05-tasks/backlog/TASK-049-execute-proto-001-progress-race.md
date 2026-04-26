# Task: Execute PROTO-001 — Progress Cascade Race Condition Spike

**ID:** TASK-049
**Unit:** U-059 (precedes implementation)
**Group:** G-HARDENING
**Priority:** P0
**Estimate:** 8h (timebox per PROTO-001)
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Run the PROTO-001 spike defined in `C-Design/C10-prototypes/PROTO-001-progress-race-condition.md` to validate which locking strategy (A: SELECT FOR UPDATE / B: queue worker / C: optimistic lock) closes the race condition without unacceptable latency. Resolution of DEC-003 depends on the outcome — without that resolution, TASK-017 cannot start.

## Acceptance Criteria
- [ ] Reproduce race condition in baseline code with `pcntl_fork`/Drush parallel script (≥1 duplicate `CourseCompletedEvent` observed in 100 runs × 20 concurrent requests)
- [ ] Implement Option A (SELECT FOR UPDATE + retry) on `spike/proto-001-progress-lock` branch and re-run script: 0 duplicates over 100 runs
- [ ] Measure P50/P95/P99 added latency vs baseline; record deadlock count and lock-wait timeouts at 20/50/100 concurrent users
- [ ] Implement Option C (optimistic lock with `version` column) for comparison baseline only (no production schema change in this task)
- [ ] Fill `Outcome`, `Result`, `Decision`, and `Artifacts` sections of PROTO-001 file
- [ ] If Option A passes all success criteria → propose SA mark DEC-003 = A and ADR-010 status `ACCEPTED`
- [ ] If Option A fails any criterion → escalate Option B (queue worker), open new ADR

## Technical Notes
- Module: `lms_progress` (`ProgressApiController.php` is the entry point that the spike branch wraps)
- Stress script lives at `.agent/scripts/proto-001-stress.php` (create if absent)
- Outputs: `.agent/generated/proto-001/{latency.csv, run-*.log}`
- Do NOT merge spike branch to main — this task only produces the decision artefact
- Expected runtime per script run: ~30s per concurrency level

## Dependencies
- ADR-008 (cascade strategy) accepted — confirms cascade shape
- DEC-003 OPEN → this task closes it

## Definition of Done
- [ ] Spike branch pushed with reproduction + Option A + Option C variants
- [ ] PROTO-001 file `Outcome`/`Result`/`Decision` populated (committed to main)
- [ ] SA notified for DEC-003 sign-off
- [ ] Unblocks TASK-017 (race condition implementation)
