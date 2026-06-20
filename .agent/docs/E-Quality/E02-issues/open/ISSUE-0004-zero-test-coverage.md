# ISSUE-0004 - ~0% automated test coverage

**Severity:** P1 · **Status:** open · **Risk:** R-006 · **Task:** [X01](../../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md)

## Description
No PHPUnit suites exist in `web/modules/custom/`. The G3 gate passes vacuously; regressions are undetected.

## Impact
No safety net for the gap-closure work (webhooks, badges, secrets refactor); risky to change code.

## Evidence
No `tests/` directories with `*Test.php` under custom modules.

## Fix
Establish kernel/functional/unit baseline (see [E01](../../E01-test-strategy/_index.md)); wire into CI with a coverage threshold for new code.
