# INT-006 - Google Gemini

## Requirement
Alternative LLM backend for AI content generation (F-018), behind the `@AiProvider` plugin (Gemini) in `lms_ai`; selectable by admin.

## Participating modules
[lms_ai](../../C-Design/C05-modules/lms_ai.md)

## Contract expectations
1. **Auth/authz:** Google Gemini API key — SENSITIVE (R-001); server-side.
2. **Failure/retry/fallback:** graceful error on failure; provider switch possible; no corrupt saves.
3. **CI test fixture/stub:** mocked provider with canned responses; no live calls in CI.
4. **Operational owner + alerting:** AI-features owner; monitor error rate / quota.

## Design link
[C06 INT-006](../../C-Design/C06-apis/INT-006-google-gemini.md)
