# C06 API/Contract - INT-006 Google Gemini

**Requirement:** [B06 INT-006](../../B-Requirements/B06-integration-requirements/INT-006-google-gemini.md)
**Participating modules:** [lms_ai](../C05-modules/lms_ai.md)

## Purpose
LLM backend thay thế cho sinh nội dung AI qua `@AiProvider` là Gemini.

## Proposed contract
- **REST namespace:** gọi ra ngoài tới Google Generative Language API; trigger nội bộ `/ai/generate`.
- **Auth:** Gemini API key nằm trong settings của `lms_ai` (R-001) → cần chuyển sang Key/env.
- **Authorization:** chỉ role editor; chỉ server-side.
- **Response envelope:** `{ data: {draft}, error|null, meta: {provider: "gemini", model} }`.
- **Audit:** ghi log metadata của prompt + lỗi; redact key.

## Test requirements
- CI: mock provider; không gọi live.
- Auth+authz: gate theo role editor.
- Failure/fallback: báo lỗi rõ ràng + đổi provider.
- Contract test: routing + đường lỗi (failure path).
