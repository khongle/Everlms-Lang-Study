# INT-005 - OpenAI

## Requirement
Generate course/lesson/quiz draft content (F-018/WF-010) via OpenAI's LLM, behind the `@AiProvider` plugin (OpenAI) in `lms_ai`.

## Participating modules
[lms_ai](../../C-Design/C05-modules/lms_ai.md)

## Contract expectations
1. **Auth/authz:** OpenAI API key — SENSITIVE (R-001), config YAML today; server-side calls only.
2. **Failure/retry/fallback:** on API error/timeout/rate-limit, the generator surfaces a graceful error; editor can retry or switch provider; no partial-corrupt entities saved.
3. **CI test fixture/stub:** mocked `@AiProvider` returning canned completions; no live LLM calls in CI.
4. **Operational owner + alerting:** AI-features owner; track token spend / error rate.

## Design link
[C06 INT-005](../../C-Design/C06-apis/INT-005-openai.md)
