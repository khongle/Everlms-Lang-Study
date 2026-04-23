# Task: Certificate PDF Generation

**ID:** TASK-007
**Unit:** U-037
**Group:** G-CERTIFICATE
**Priority:** P0
**Estimate:** 8h
**Assigned:** unassigned
**Status:** DONE (95%)
**Completed:** 2026-04-15

## Audit Notes
- CertificateGenerator service uses **mPDF** (not dompdf). `generate()` renders Twig→HTML→mPDF→file ✅
- Saves to `public://certificates/issued/Certificate_{CODE}.pdf` as managed file ✅
- Orientation support (landscape/portrait) from template ✅
- Preview route + Regenerate route ✅
- **Known gaps**: Template only 1.2KB (simple layout). No watermark support.

## Description
Render certificate thành PDF sử dụng dompdf. Template-based rendering với student info, course info, completion date.

## Acceptance Criteria
- [ ] PDF render từ CertificateTemplate + student data
- [ ] PDF chứa: student name, course name, completion date, certificate ID, QR code verify link
- [ ] Download PDF từ certificate detail page
- [ ] PDF formatting đúng (A4 landscape, professional layout)
- [ ] Hỗ trợ multiple templates

## Technical Notes
- Module: `lms_certificate`
- Service: `CertificatePdfGenerator::generate()`
- Library: `dompdf/dompdf` (already in composer)
- Template: Twig template cho PDF content → dompdf render
- Route: `/certificates/{id}/download`

## Dependencies
- TASK-006 (Certificate issuance)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Visual test: PDF looks correct
- [ ] Performance: PDF generation <5s
