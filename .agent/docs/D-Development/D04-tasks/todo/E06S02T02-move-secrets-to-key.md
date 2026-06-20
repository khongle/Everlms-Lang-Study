---
id: E06S02T02
title: Move secrets from config YAML to Key/env
status: todo
story: E06S02
owning_modules: [lms_payment, lms_ai, lms_zoom]
risk: R-001
adequacy: NOT_RUN
---

# E06S02T02 - Move secrets to Key/env

## Goal
Remove third-party secrets from `lms_*.settings.yml`; load via Key module / environment (R-001 / BLK-001, P0). Realises the missing **Secrets** standard module.

## Steps
1. Inventory secrets in `lms_payment/lms_ai/lms_zoom` settings.
2. Configure Key entities (or env vars) as the single secret entry point.
3. Refactor gateways/providers to read via Key; purge secrets from config + git history.
4. Add a secret-scan CI check.

## Acceptance / gates
- No secret values in `config/sync`; secret-scan clean; payments/AI/Zoom still function.

## Links
[story](../../D03-stories/E06S02-lms_payment.md) · [C01 standard modules](../../../C-Design/C01-architecture-decision/_index.md) · [G02 R-001](../../../G-Governance/G02-risk-register/_index.md)
