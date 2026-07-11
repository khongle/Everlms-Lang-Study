# F-019 Acceptance Criteria - Mobile app API

## Core criteria
1. **Given** một mobile user, **when** họ request và verify OTP (`MobileAuthController`/`OtpService`), **then** một bearer token được cấp.
2. **Given** một token hợp lệ, **when** app gọi các endpoint catalog/learning (`/api/v1/...`), **then** JSON response khớp với contract đã tài liệu hóa.
3. **Given** token bị thiếu/không hợp lệ, **when** một endpoint được bảo vệ được gọi, **then** API trả về 401/403.
4. **Given** cùng một user trên web và mobile, **when** progress/enrollment được đọc, **then** kết quả nhất quán trên cả hai surface.

## Evidence expected
- Module: [lms_mobile_api](../../C-Design/C05-modules/lms_mobile_api.md); spec trong [I01](../../I-External/I01-public-api-spec/README.md).
- Test: auth (OTP→token) test; protected-route auth test; web/mobile parity test (E03).
