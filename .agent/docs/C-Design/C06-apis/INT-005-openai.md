# C06 API/Contract - INT-005 OpenAI

**Requirement:** [B06 INT-005](../../B-Requirements/B06-integration-requirements/INT-005-openai.md)
**Participating modules:** [lms_ai](../C05-modules/lms_ai.md)

## Purpose
LLM text generation for course/lesson/quiz drafts via the OpenAI `@AiProvider`.

## Proposed contract
- **REST namespace:** outbound to OpenAI API; internal trigger `/ai/generate`.
- **Auth:** OpenAI API key in `lms_ai.settings.yml`/`lms_ai.engine.settings.yml` (R-001) → move to Key/env.
- **Authorization:** instructors/content_editors only; server-side calls.
- **Response envelope:** `{ data: {draft}, error|null, meta: {provider: "openai", model, tokens} }`.
- **Audit:** log prompt metadata + token usage + errors; do not log full keys.

## Test requirements
- CI: mocked `@AiProvider` returning canned completions (no live LLM).
- Auth+authz: editor-role gate.
- Failure/fallback: timeout/rate-limit → graceful error, retry/switch provider, no corrupt save.
- Contract test: provider-routing + failure path.
