# ISSUE-0001 - Third-party secrets committed in config YAML

**Severity:** P0 · **Status:** open · **Risk:** R-001 · **Task:** [E06S02T02](../../../D-Development/D04-tasks/todo/E06S02T02-move-secrets-to-key.md)

## Description
Payment/AI/Zoom API secrets are stored directly in `lms_payment.settings.yml`, `lms_ai.settings.yml`, `lms_ai.engine.settings.yml`, `lms_zoom.settings.yml` (git-tracked `config/sync`). The `key` module is a declared dependency of `lms_ai` but **no key entities are configured**.

## Impact
Secrets in version control = credential exposure; blocks safe public release.

## Evidence
`grep -liE "secret|api_key|token" config/sync/lms_*.yml` → matches; no `key.key.*.yml` present.

## Fix
Implement the Secrets standard module: move all secrets to Key/env (single entry point), purge from config + git history, add a secret-scan CI check.
