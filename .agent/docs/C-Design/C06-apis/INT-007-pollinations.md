# C06 API/Contract - INT-007 Pollinations

**Requirement:** [B06 INT-007](../../B-Requirements/B06-integration-requirements/INT-007-pollinations.md)
**Participating modules:** [lms_ai](../C05-modules/lms_ai.md)

## Purpose
Free text/image generation via the Pollinations `@AiProvider` (low-cost drafts/imagery).

## Proposed contract
- **REST namespace:** outbound to Pollinations endpoint; internal trigger `/ai/generate`.
- **Auth:** typically keyless/public; any token treated as SENSITIVE; server-side.
- **Authorization:** editor roles only.
- **Response envelope:** `{ data: {draft|image_url}, error|null, meta: {provider: "pollinations"} }`.
- **Audit:** log calls + errors; monitor availability (free service → lower SLA).

## Test requirements
- CI: mocked provider; no live calls.
- Auth+authz: editor gate.
- Failure/fallback: graceful error + provider switch.
- Contract test: routing + failure path.
