# Task: Execute PROTO-004 — SCORM/xAPI Tracking Performance Spike

**ID:** TASK-052
**Unit:** new (raise unit ID after spike — likely G-INTEGRATION extension)
**Group:** G-INTEGRATION
**Priority:** P2
**Estimate:** 8h (timebox per PROTO-004)
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Run the PROTO-004 spike defined in `C-Design/C10-prototypes/PROTO-004-scorm-xapi-tracking-performance.md` to validate (a) SCORM 1.2 runtime endpoint serving `LMSCommit` under load (50 → 200 concurrent users), (b) xAPI Statement inbox via Drupal Queue, (c) SCORM package extraction security (ZipSlip, symlink, zip-bomb, extension whitelist).

## Acceptance Criteria
- [ ] **SCORM commit latency:** 50 concurrent users P95 ≤ 80ms; 200 concurrent users P95 ≤ 150ms with error rate < 1%
- [ ] **xAPI throughput:** 500 statements/s burst handled; queue worker drains within 60s
- [ ] **Extraction security:** 4/4 malicious test zips REJECTED (ZipSlip path traversal, symlink escape, zip-bomb, banned extension)
- [ ] **Extraction performance:** 50MB Articulate SCORM package extracts < 5s sync (or async via queue ≤ 30s)
- [ ] **Cascade integration:** SCORM commit → `markLessonComplete()` does not increase deadlock rate vs PROTO-001 baseline
- [ ] No regression in existing `lms_lesson` test suite
- [ ] PROTO-004 file `Outcome`/`Result`/`Decision`/`Artifacts` populated

## Technical Notes
- Modules: `lms_lesson`, `lms_progress`
- New tables (spike-time): `lms_scorm_attempts`, `lms_xapi_statements`
- Audit existing `_lms_lesson_extract_scorm()` in `lms_lesson.module` for canonical-path check + size cap
- k6/wrk script lives at `.agent/scripts/proto-004-scorm-load.js`
- Sample SCORM package source: Articulate Storyline sample or `scorm-test-track` repo

## Dependencies
- PROTO-001 / TASK-049 result (cascade lock pattern) — SCORM bridge depends on chosen lock approach
- Redis available (already in stack)

## Definition of Done
- [ ] Spike branch `spike/proto-004-scorm-xapi` pushed
- [ ] PROTO-004 file populated with latency, security audit, decision
- [ ] If extraction security tests FAIL → raise new FIND in G02 + create H04 tech-debt entry
- [ ] If VALIDATED → propose ADR-012 (SCORM runtime + xAPI inbox) and add new units to D03
