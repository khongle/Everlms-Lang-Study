# F-019 Acceptance Criteria - Mobile app API

## Core criteria
1. **Given** a mobile user, **when** they request and verify an OTP (`MobileAuthController`/`OtpService`), **then** a bearer token is issued.
2. **Given** a valid token, **when** the app calls catalog/learning endpoints (`/api/v1/...`), **then** JSON responses match the documented contract.
3. **Given** a missing/invalid token, **when** a protected endpoint is called, **then** the API returns 401/403.
4. **Given** the same user on web and mobile, **when** progress/enrollment is read, **then** results are consistent across surfaces.

## Evidence expected
- Module: [lms_mobile_api](../../C-Design/C05-modules/lms_mobile_api.md); spec in [I01](../../I-External/I01-public-api-spec/README.md).
- Test: auth (OTP→token) test; protected-route auth test; web/mobile parity test (E03).
