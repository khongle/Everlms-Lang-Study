# Task: Public Certificate Verification

**ID:** TASK-008
**Unit:** U-038
**Group:** G-CERTIFICATE
**Priority:** P0
**Estimate:** 4h
**Assigned:** unassigned
**Status:** DONE (80%)
**Completed:** 2026-04-15

## Audit Notes
- Public route `GET /certificate/verify` with `_access: TRUE` ✅
- VerificationForm: code input → lookup by `field_certificate_number` → shows user/course + revoked status ✅
- Result template: `certificate-verification-result.html.twig` ✅
- **Known gaps**: Form-based (POST + rebuild) not direct URL (`/certificate/verify/{code}`). No rate limiting. No QR code generation.

## Description
Public endpoint để verify certificate. Bất kỳ ai có link đều có thể kiểm tra tính hợp lệ.

## Acceptance Criteria
- [ ] Route `/certificate/verify/{code}` accessible by anonymous
- [ ] Valid certificate → hiển thị: student name, course, date, status "Valid"
- [ ] Revoked certificate → hiển thị status "Revoked"
- [ ] Invalid code → 404 page
- [ ] Không expose sensitive data (email, phone)

## Technical Notes
- Module: `lms_certificate`
- Controller: `CertificateVerifyController::verify()`
- Template: `lms-certificate-verify.html.twig`
- Route: public, no auth required

## Dependencies
- TASK-006 (Certificate issuance)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Security review: no data leakage
