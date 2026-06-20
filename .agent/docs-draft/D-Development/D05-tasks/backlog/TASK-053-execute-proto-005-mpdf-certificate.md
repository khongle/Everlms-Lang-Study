# Task: Execute PROTO-005 — mPDF Certificate Generation Spike

**ID:** TASK-053
**Unit:** lms_certificate flow (extends U-037)
**Group:** G-CERTIFICATE
**Priority:** P3
**Estimate:** 4h (timebox per PROTO-005)
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Run the PROTO-005 spike defined in `C-Design/C10-prototypes/PROTO-005-mpdf-certificate-generation.md` to validate (a) mPDF rendering Vietnamese diacritics with custom font, (b) full-bleed background image, (c) embedded QR verification, (d) sync vs async generation decision based on render latency. Also resolves the C01 documentation mismatch (C01 says `dompdf`; code uses `mPDF`).

## Acceptance Criteria
- [ ] 5/5 sample certificates render Vietnamese diacritics correctly (`Nguyễn Hoàng Hải`, `Đỗ Thị Hằng`, `Lê Văn Đức`) — visual diff ≤ 2% pixel diff vs reference
- [ ] Full-bleed background image: 0 white margin > 1mm
- [ ] QR scan from phone opens `/certificate/verify/{code}` and verify endpoint returns JSON metadata
- [ ] Long name (50+ chars) wraps without layout overflow; HTML entities escaped correctly
- [ ] **Render latency:** P95 ≤ 1.5s sync OR async-queue variant ≤ 5s P95
- [ ] **Memory:** peak ≤ 64MB per generation
- [ ] **File size:** ≤ 500KB for standard A4 landscape template
- [ ] **No memory leak:** 50 sequential generations don't grow `memory_get_usage()` monotonically
- [ ] **C01 alignment:** if mPDF VALIDATED → update C01 §Technology Decisions (replace `dompdf`); else switch service implementation to `dompdf`
- [ ] PROTO-005 file `Outcome`/`Result`/`Decision`/`Artifacts` populated

## Technical Notes
- Module: `lms_certificate` (`CertificateGenerator.php` already uses mPDF)
- Composer adds: `mpdf/mpdf:^8.2`, `endroid/qr-code:^5.0` (verify if already present)
- Font: Noto Sans or DejaVu Sans for Vietnamese diacritics (verify license for commercial use)
- Verify endpoint already exists per C07 sitemap: `/certificate/verify/{code}` (confirm route in `lms_certificate.routing.yml`)
- Outputs: `.agent/generated/proto-005/{samples/, visual-diff.html, render-bench.csv, memory-profile.txt}`

## Dependencies
- Drupal private file system configured (`private://`)

## Definition of Done
- [ ] Spike branch `spike/proto-005-cert-pdf` pushed
- [ ] PROTO-005 file populated with measurements + decision
- [ ] C01-architecture-decision.md §Technology Decisions updated (mPDF or dompdf, whichever wins)
- [ ] If async needed → propose ADR-013 (async certificate pipeline) + create new unit for `CertificateGenerationWorker`
