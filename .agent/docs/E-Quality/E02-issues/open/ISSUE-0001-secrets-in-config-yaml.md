# ISSUE-0001 - Third-party secrets committed in config YAML

**State:** open
**Severity:** P0 · **Module:** [lms_payment](../../../C-Design/C05-modules/lms_payment.md) / [lms_ai](../../../C-Design/C05-modules/lms_ai.md) / [lms_zoom](../../../C-Design/C05-modules/lms_zoom.md) · **Story:** [E06S02-lms_payment](../../../D-Development/D03-stories/E06S02-lms_payment.md) · **Risk:** R-001 · **Task:** [E06S02T02](../../../D-Development/D04-tasks/todo/E06S02T02-move-secrets-to-key.md)

## Summary
Payment/AI/Zoom API secrets are stored directly in `lms_payment.settings.yml`, `lms_ai.settings.yml`, `lms_ai.engine.settings.yml`, `lms_zoom.settings.yml` (git-tracked `config/sync`). The `key` module is a declared dependency of `lms_ai` but **no key entities are configured**, so nothing actually routes secrets away from plaintext config.

## Steps to reproduce
1. `grep -liE "secret|api_key|token" config/sync/lms_*.yml` on a fresh checkout.
2. Observe matches inside `lms_payment.settings.yml`, `lms_ai.settings.yml`, `lms_ai.engine.settings.yml`, `lms_zoom.settings.yml`.
3. `drush config:get key.key` (or browse `/admin/config/system/keys`) → no `key.key.*` entities configured, confirming the Key module is installed but unused.

## Root cause
The Secrets standard module pattern (single entry point via the `key` module) was never wired up during initial gateway/AI/Zoom integration — settings forms were built to save straight into `config/sync` instead of storing a `key_id` reference, so secrets ride along with every config export/import and land in git history.

## Fix
Implement the Secrets standard module: move all secrets to Key/env (single entry point), purge secret values from `config/sync` (and scrub git history), and add a secret-scan CI check (e.g. `gitleaks`/`trufflehog`) that fails the build on any future plaintext secret in `config/sync`.

## Regression test
No automated regression test exists yet (tracked under [ISSUE-0004](ISSUE-0004-zero-test-coverage.md) / [X01](../../../D-Development/D04-tasks/todo/X01-establish-test-baseline.md)). Planned: an [E03](../../E03-test-definitions/_index.md) `secrets-not-in-config` static check (CI job asserting `grep -liE "secret|api_key|token" config/sync/*.yml` returns empty) to be added alongside the fix so this cannot regress silently.
