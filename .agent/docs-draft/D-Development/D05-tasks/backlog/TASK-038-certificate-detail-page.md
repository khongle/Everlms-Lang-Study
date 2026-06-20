# Task: Certificate Detail Page

**ID:** TASK-038
**Unit:** U-078
**Group:** G-UI-POLISH
**Priority:** P2
**Estimate:** 6h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Trang chi tiết certificate cho student: preview certificate, download PDF, share link, verification QR code.

## Acceptance Criteria
- [ ] Certificate preview (visual representation)
- [ ] Download PDF button
- [ ] Share link (copy to clipboard)
- [ ] Verification QR code
- [ ] Certificate metadata (course, date, expiry)
- [ ] My certificates listing page
- [ ] Responsive layout

## Technical Notes
- Module: `lms_certificate` (CertificateController exists)
- Template: `lms-certificate-detail.html.twig`
- SCSS: `_certificate.scss`
- Integration: PDF generation service

## Dependencies
- TASK-006, TASK-007, TASK-008 (Certificate tasks — DONE)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] PDF download functional
- [ ] Mobile responsive verified
