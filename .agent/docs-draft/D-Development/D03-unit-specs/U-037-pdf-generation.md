# D03 — Unit Spec: PDF Generation (dompdf)

**Unit ID:** U-037
**Group:** G-CERTIFICATE
**Title:** PDF generation (dompdf)
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned

---

## Scope

Generate a PDF certificate from a `UserCertificate` + `CertificateTemplate`, using dompdf library. Provide a download endpoint and a preview route for admins. Template is an HTML/Twig template with placeholders for user name, course name, date, and verification code.

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Service/CertificatePdfService.php` | Create | lms_certificate |
| `src/Controller/CertificateController.php` | Modify | lms_certificate |
| `templates/certificate-pdf.html.twig` | Create | lms_certificate |
| `composer.json` | Modify | root — add `dompdf/dompdf` |
| `lms_certificate.routing.yml` | Modify | lms_certificate |

---

## Interfaces / Services

- `CertificatePdfService::generate(UserCertificate $cert): string` — returns raw PDF bytes
- `CertificatePdfService::download(UserCertificate $cert): Response` — streams PDF download
- dompdf v2.x (PHP 8.3 compatible — verify before implement)
- Route: `GET /certificate/{id}/download` — user can only download own certificate
- Route: `GET /manage/certificate/{id}/preview` — admin preview

---

## Acceptance Criteria

- [ ] `/certificate/{id}/download` returns a valid PDF for the authenticated user's certificate
- [ ] PDF contains: user full name, course name, issue date formatted (DD/MM/YYYY), verification code
- [ ] PDF uses certificate template's HTML layout
- [ ] Non-owner accessing download route returns 403
- [ ] Admin can preview any certificate PDF at `/manage/certificate/{id}/preview`
- [ ] PDF file size < 1 MB for standard template

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | IDOR check on download route |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-035 | UserCertificate + CertificateTemplate entities |
| U-036 | Issuance service creates the UserCertificate being rendered |

---

**Feature Flag:** None
**Note:** Confirm `dompdf/dompdf ^2.0` PHP 8.3 compatibility via `composer require --dry-run` before starting.

*End of U-037-pdf-generation.md*
