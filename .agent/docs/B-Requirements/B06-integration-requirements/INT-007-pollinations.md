# INT-007 - Pollinations

## Requirement
Free text/image generation backend (e.g. illustrative imagery, low-cost drafts) via the `@AiProvider` plugin (Pollinations) in `lms_ai`.

## Participating modules
[lms_ai](../../C-Design/C05-modules/lms_ai.md)

## Contract expectations
1. **Auth/authz:** typically keyless/public endpoint; still called server-side; treat any token as SENSITIVE.
2. **Failure/retry/fallback:** graceful error; editor can retry or switch provider; no corrupt saves.
3. **CI test fixture/stub:** mocked provider; no live calls in CI.
4. **Operational owner + alerting:** AI-features owner; monitor availability (free service → lower SLA).

## Design link
[C06 INT-007](../../C-Design/C06-apis/INT-007-pollinations.md)
