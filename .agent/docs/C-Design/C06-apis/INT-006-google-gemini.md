# C06 API/Contract - INT-006 Google Gemini

**Requirement:** [B06 INT-006](../../B-Requirements/B06-integration-requirements/INT-006-google-gemini.md)
**Participating modules:** [lms_ai](../C05-modules/lms_ai.md)

## Purpose
Alternative LLM backend for AI content generation via the Gemini `@AiProvider`.

## Proposed contract
- **REST namespace:** outbound to Google Generative Language API; internal trigger `/ai/generate`.
- **Auth:** Gemini API key in `lms_ai` settings (R-001) → Key/env.
- **Authorization:** editor roles only; server-side.
- **Response envelope:** `{ data: {draft}, error|null, meta: {provider: "gemini", model} }`.
- **Audit:** log prompt metadata + errors; redact keys.

## Test requirements
- CI: mocked provider; no live calls.
- Auth+authz: editor gate.
- Failure/fallback: graceful error + provider switch.
- Contract test: routing + failure path.
