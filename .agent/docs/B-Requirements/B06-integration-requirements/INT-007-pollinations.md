# INT-007 - Pollinations

## Requirement
Backend sinh text/image miễn phí (ví dụ hình minh họa, draft chi phí thấp) qua plugin `@AiProvider` (Pollinations) trong `lms_ai`.

## Participating modules
[lms_ai](../../C-Design/C05-modules/lms_ai.md)

## Contract expectations
1. **Auth/authz:** thường là endpoint keyless/public; vẫn được gọi server-side; coi mọi token là SENSITIVE.
2. **Failure/retry/fallback:** lỗi graceful; editor có thể retry hoặc đổi provider; không lưu dữ liệu bị corrupt.
3. **CI test fixture/stub:** provider được mock; không gọi live trong CI.
4. **Operational owner + alerting:** AI-features owner; theo dõi availability (free service → SLA thấp hơn).

## Design link
[C06 INT-007](../../C-Design/C06-apis/INT-007-pollinations.md)
