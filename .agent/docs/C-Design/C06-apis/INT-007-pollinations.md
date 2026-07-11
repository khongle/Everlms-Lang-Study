# C06 API/Contract - INT-007 Pollinations

**Requirement:** [B06 INT-007](../../B-Requirements/B06-integration-requirements/INT-007-pollinations.md)
**Participating modules:** [lms_ai](../C05-modules/lms_ai.md)

## Purpose
Sinh text/image miễn phí qua `@AiProvider` là Pollinations (dùng cho draft/hình ảnh chi phí thấp).

## Proposed contract
- **REST namespace:** gọi ra ngoài tới endpoint Pollinations; trigger nội bộ `/ai/generate`.
- **Auth:** thường không cần key/công khai; mọi token đều coi là SENSITIVE; chỉ server-side.
- **Authorization:** chỉ role editor.
- **Response envelope:** `{ data: {draft|image_url}, error|null, meta: {provider: "pollinations"} }`.
- **Audit:** ghi log các lời gọi + lỗi; theo dõi availability (dịch vụ miễn phí → SLA thấp hơn).

## Test requirements
- CI: mock provider; không gọi live.
- Auth+authz: gate theo role editor.
- Failure/fallback: báo lỗi rõ ràng + đổi provider.
- Contract test: routing + đường lỗi (failure path).
