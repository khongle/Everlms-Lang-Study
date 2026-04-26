# Task: Kernel Test Suite — G-CERTIFICATE (Issuance, PDF, Verify)

**ID:** TASK-079
**Unit:** — (cross-cutting)
**Group:** — (cross-cutting)
**Priority:** P2
**Estimate:** 8h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Xây dựng PHPUnit kernel test suite cho `lms_certificate` module, cover 3 flow chính: (1) issuance trigger khi student hoàn thành Course, (2) PDF render pipeline, và (3) public verify endpoint trả về certificate data hợp lệ. Scope phụ thuộc vào outcome của PROTO-005 — nếu PROTO-005 chọn wkhtmltopdf thì mock render khác so với Dompdf.

Suite chạy độc lập với PDF engine thật (dùng mock renderer) để tránh binary dependency trong CI. Một integration test riêng (không block PR) verify PDF binary output.

## Acceptance Criteria
- [ ] **Issuance trigger:**
  - [ ] `CourseCompletedEvent` → certificate entity được tạo với đúng `user_id`, `course_id`, `issued_at`
  - [ ] Duplicate prevention: event fired 2 lần → chỉ 1 certificate được tạo
  - [ ] Certificate không được tạo nếu Course không có `certificate_enabled = TRUE`
- [ ] **PDF render (mocked):**
  - [ ] `CertificateRenderService::render()` nhận đúng template variables
  - [ ] Mock renderer trả về non-empty binary string
  - [ ] File saved tới public/private files correctly (path format validated)
- [ ] **Verify endpoint:**
  - [ ] `GET /certificate/verify/{uuid}` trả về 200 + certificate metadata (JSON)
  - [ ] Invalid UUID → 404
  - [ ] Revoked certificate → 410
- [ ] Coverage ≥75% trên `CertificateService`, `CertificateRenderService`, `CertificateAccessController`
- [ ] Pass trong DDEV (`ddev phpunit --group kernel-certificate`)

## Technical Notes
- Module: `lms_certificate`
- Key files:
  - `web/modules/custom/lms_certificate/src/CertificateService.php`
  - `web/modules/custom/lms_certificate/src/CertificateRenderService.php`
  - `web/modules/custom/lms_certificate/src/Controller/CertificateController.php`
- Test path: `web/modules/custom/lms_certificate/tests/src/Kernel/`
- PDF mock: implement `CertificateRendererInterface` với dummy return
- PROTO-005 outcome ảnh hưởng engine choice — abstract qua interface trước
- Verify endpoint: route `lms_certificate.verify` trong `lms_certificate.routing.yml`
- Annotated `@group TEST-005` (hoặc mới nếu E03 chưa có)

## Dependencies
- TASK-067 (test catalog)
- TASK-069 (CI)
- PROTO-005 (PDF engine decision — affects mock interface)

## Definition of Done
- [ ] Code complete — issuance, render (mock), verify endpoint tests
- [ ] G1-G4 gates passed
- [ ] 0 real PDF binary calls trong unit/kernel tests
- [ ] Verify endpoint test chạy qua Drupal HTTP kernel
- [ ] Coverage ≥75% confirmed
