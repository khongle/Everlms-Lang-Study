# Task: FIND-008 — Migrate Gemini API Key from URL Query to Header

**ID:** TASK-054
**Unit:** U-047 (scope extension)
**Group:** G-HARDENING
**Priority:** P1 (security — High severity)
**Estimate:** 3h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
The `GeminiProvider` plugin currently passes the API key via URL query string (`?key=...`), causing the secret to leak into webserver access logs, browser history, and proxy logs. Move authentication to the `x-goog-api-key` request header per Google's recommendation, then rotate the leaked key.

## Acceptance Criteria
- [ ] All Gemini HTTP requests carry header `x-goog-api-key: {key}`; URL no longer contains `?key=` parameter
- [ ] `git grep -n "?key=" web/modules/custom/lms_ai/` returns 0 matches
- [ ] HTTP capture (Wireshark / Drupal HTTP middleware log) on a sample call confirms header present and URL clean
- [ ] Production Gemini API key rotated AFTER deploy (separate runbook step in H02)
- [ ] Existing AI generation tests still pass (no behavior change beyond auth transport)

## Technical Notes
- File: `web/modules/custom/lms_ai/src/Plugin/AiProvider/GeminiProvider.php`
- Drupal HTTP client: `\Drupal::httpClient()` — pass headers via Guzzle `['headers' => ['x-goog-api-key' => $key]]`
- Key storage stays in Drupal State (`Drupal::state()->get('lms_ai.gemini_api_key')`) — no schema change
- Document key rotation in `H-Operations/H02-runbooks/`

## Dependencies
- TASK-051 (PROTO-003 spike) — combines with `AiCallOptions` rework; may merge into that branch if landed in same window
- SA approval to rotate live key (production blast radius zero if header migration deploys before rotation)

## Definition of Done
- [ ] Code change merged to main
- [ ] FIND-008 status in G02-risk-register.md → CLOSED with date and commit ref
- [ ] Runbook entry for key rotation in H02
- [ ] Smoke test: 1 successful Gemini generation post-rotation in staging
