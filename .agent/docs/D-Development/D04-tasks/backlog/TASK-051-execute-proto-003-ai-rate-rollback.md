# Task: Execute PROTO-003 — AI Provider Rate Limit + Cost Cap + Rollback Spike

**ID:** TASK-051
**Unit:** U-063 (precedes implementation; extends U-047 contract)
**Group:** G-HARDENING
**Priority:** P1
**Estimate:** 8h (timebox per PROTO-003)
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Run the PROTO-003 spike defined in `C-Design/C10-prototypes/PROTO-003-ai-provider-rate-limit-rollback.md` to validate adding (a) `max_tokens` enforcement, (b) per-user rate limit, (c) saga compensation rollback, and (d) Gemini API key header migration on top of the accepted plugin pattern (ADR-006). Resolves FIND-003 + FIND-008 + RISK-003.

## Acceptance Criteria
- [ ] New `AiCallOptions` value object (maxTokens, timeoutMs, userId, callerContext) and `AiResult` (success, data, errorCode, tokensUsed, latencyMs) compile with all 3 provider plugins (`OpenAiProvider`, `GeminiProvider`, `PollinationsProvider`)
- [ ] OpenAI request body contains `max_tokens` field on every call (verified via HTTP capture)
- [ ] **FIND-008 fix:** Gemini calls use header `x-goog-api-key` instead of URL query string; `git grep -n "?key=" web/modules/custom/lms_ai/` returns no matches
- [ ] Rate limit (Redis key `lms_ai:rl:{userId}:{providerWindow}`, 30/min/user): 100-call spam from 1 user → ≥99% of calls 31+ rejected with `errorCode = 'rate_limited'`
- [ ] Daily cost counter accurate within ±5% vs provider report after 24h shadow run
- [ ] Saga rollback: inject failure at each of the 4 steps (`createCourseDraft`, `generateChapters`, `generateLessons`, `generateQuiz`) → 4/4 runs end with `state = rolled_back` and 0 orphan rows in DB
- [ ] Per-call overhead (rate-limit Redis check + state write) ≤ 10ms P95
- [ ] PROTO-003 file `Outcome`/`Result`/`Decision`/`Artifacts` populated

## Technical Notes
- Module: `lms_ai`
- Service: new `AiRateLimiter` (Redis-backed) + `AiGenerationOrchestrator` (saga)
- Mock HTTP server: `wiremock` or PHPUnit double for deterministic failure injection
- New table: `lms_ai_generation_state` (status: pending/partial/completed/rolled_back) — Phase 1 spike via `hook_update_N`
- Rotate Gemini API key after FIND-008 fix lands

## Dependencies
- ADR-006 ACCEPTED (plugin pattern stable)
- Redis available (already in stack per C01)
- SA confirm daily cost cap value before spike (default $10/provider/day in spec)

## Definition of Done
- [ ] Spike branch `spike/proto-003-ai-contract` pushed
- [ ] PROTO-003 file populated with measurements + decision
- [ ] Propose ADR-011 draft (saga compensation pattern) if VALIDATED
- [ ] Unblocks TASK-032 (AI rollback implementation), TASK-054 (Gemini header migration)
