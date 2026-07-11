# INT-006 - Google Gemini

## Requirement
Backend LLM thay thế cho AI content generation (F-018), đứng sau plugin `@AiProvider` (Gemini) trong `lms_ai`; admin có thể chọn.

## Participating modules
[lms_ai](../../C-Design/C05-modules/lms_ai.md)

## Contract expectations
1. **Auth/authz:** Google Gemini API key — SENSITIVE (R-001); server-side.
2. **Failure/retry/fallback:** lỗi graceful khi thất bại; có thể đổi provider; không lưu dữ liệu bị corrupt.
3. **CI test fixture/stub:** provider được mock với response dựng sẵn; không gọi live trong CI.
4. **Operational owner + alerting:** AI-features owner; theo dõi error rate / quota.

## Design link
[C06 INT-006](../../C-Design/C06-apis/INT-006-google-gemini.md)
