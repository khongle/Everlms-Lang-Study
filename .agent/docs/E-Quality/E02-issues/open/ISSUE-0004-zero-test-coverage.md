# ISSUE-0004 - ~0% automated test coverage

**State:** open
**Severity:** P1 · **Module:** all `lms_*` (cross-cutting) · **Story:** [X01](../../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md) · **Risk:** R-006 · **Task:** [X01](../../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md)

## Summary
No PHPUnit suites exist in `web/modules/custom/`. The G3 (tests) gate currently passes vacuously because there is nothing to run, so regressions across the whole `lms_*` surface are undetected until manual/QA-round discovery.

## Steps to reproduce
1. `find web/modules/custom -type d -name tests` on a fresh checkout.
2. `find web/modules/custom -name "*Test.php"`.
3. Both return empty — zero automated tests back any of the 29 custom modules.

## Root cause
Development prioritized shipping feature surface (catalog → learn → quiz → certificate → commerce → membership → community → AI → mobile) over building the test pyramid in parallel; no CI test stage was ever wired to enforce a coverage floor, so the gap compounded module by module.

## Fix
Establish the Unit/Kernel/Functional/E2E/Performance/Security baseline defined in [E01](../../E01-test-strategy/_index.md), starting with the highest-risk targets (8 quiz grading strategies, certificate issuance idempotency, gateway/webhook routing, enrollment gating matrix); wire the suite into CI with a coverage threshold (≥60%) enforced on new/changed code from that point forward.

## Regression test
This issue IS the regression-test gap; its own "fix" is the regression baseline. Tracked as [E03](../../E03-test-definitions/_index.md) definitions `gating-matrix`, `grading-per-strategy`, `issuance-idempotency`, `access-matrix`, `api-auth-parity` (all currently TARGET status — see [E03 index](../../E03-test-definitions/_index.md)); resolution requires at least the P1 subset of these running in CI, recorded in [E04](../../E04-test-results/README.md).
