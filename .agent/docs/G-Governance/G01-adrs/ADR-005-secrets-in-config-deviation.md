# ADR-005 - Secrets-in-config is a deviation to remediate

**Status:** Accepted (records a known deviation) · **Date:** 2026-06-21

## Context
The framework mandates a **Secrets** standard module (single entry point + rotation + audit). As-built, EverLMS stores gateway/AI/Zoom secrets in `lms_*.settings.yml` (git-tracked); the `key` module is installed (lms_ai dep) but unused.

## Decision
Record this as an explicit, accepted **deviation with a remediation plan**, not a sanctioned pattern. Remediation = [E06S02T02](../../D-Development/D04-tasks/todo/E06S02T02-move-secrets-to-key.md) (move to Key/env), tracked as R-001/BLK-001 (P0 release blocker). No new secrets may be added to config.

## Consequences
- Transparent risk; release gated until remediated.
- Until then, the docs tree never reproduces secret values (referenced only).
