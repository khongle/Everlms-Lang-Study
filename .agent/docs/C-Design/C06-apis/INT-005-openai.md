# C06 API/Contract - INT-005 OpenAI

**Requirement:** [B06 INT-005](../../B-Requirements/B06-integration-requirements/INT-005-openai.md)
**Participating modules:** [lms_ai](../C05-modules/lms_ai.md)

## Purpose
Sinh nội dung text bằng LLM cho draft course/lesson/quiz qua `@AiProvider` là OpenAI.

## Proposed contract
- **REST namespace:** gọi ra ngoài tới OpenAI API; trigger nội bộ `/ai/generate`.
- **Auth:** OpenAI API key nằm trong `lms_ai.settings.yml`/`lms_ai.engine.settings.yml` (R-001) → cần chuyển sang Key/env.
- **Authorization:** chỉ instructor/content_editor; chỉ gọi phía server-side.
- **Response envelope:** `{ data: {draft}, error|null, meta: {provider: "openai", model, tokens} }`.
- **Audit:** ghi log metadata của prompt + số token dùng + lỗi; không log đầy đủ key.

## Test requirements
- CI: mock `@AiProvider` trả về completion mẫu (canned, không gọi LLM thật).
- Auth+authz: gate theo role editor.
- Failure/fallback: timeout/rate-limit → báo lỗi rõ ràng, cho retry/đổi provider, không lưu dữ liệu hỏng.
- Contract test: provider-routing + đường lỗi (failure path).
