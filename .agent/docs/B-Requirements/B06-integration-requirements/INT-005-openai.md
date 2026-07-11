# INT-005 - OpenAI

## Requirement
Sinh nội dung draft cho course/lesson/quiz (F-018/WF-010) qua LLM của OpenAI, đứng sau plugin `@AiProvider` (OpenAI) trong `lms_ai`.

## Participating modules
[lms_ai](../../C-Design/C05-modules/lms_ai.md)

## Contract expectations
1. **Auth/authz:** OpenAI API key — SENSITIVE (R-001), hiện nằm trong config YAML; chỉ gọi server-side.
2. **Failure/retry/fallback:** khi API lỗi/timeout/rate-limit, generator surface một lỗi graceful; editor có thể retry hoặc đổi provider; không entity nào bị lưu ở trạng thái partial-corrupt.
3. **CI test fixture/stub:** `@AiProvider` được mock trả về completion dựng sẵn; không gọi LLM thật trong CI.
4. **Operational owner + alerting:** AI-features owner; theo dõi token spend / error rate.

## Design link
[C06 INT-005](../../C-Design/C06-apis/INT-005-openai.md)
