# D02 — Group Spec: G-CERTIFICATE

**Group ID:** G-CERTIFICATE
**Group Name:** Certificate System
**Order:** 6
**Units in Scope:** U-035 – U-038
**Dependencies:** G-ASSESSMENT, G-PROGRESS
**Estimated Sprints:** 1
**Status:** PENDING (1 DONE, 3 SPEC)

---

## Purpose

Phát hành certificate khi user hoàn thành course: CertificateTemplate + UserCertificate entities, auto-issuance service triggered by course completion, PDF generation via dompdf, và public verification endpoint.

---

## Units

| Unit ID | Name | Status | Gate | Priority |
|---------|------|--------|------|----------|
| U-035 | CertificateTemplate + UserCertificate entities | DONE | G4 pass | P0 |
| U-036 | Certificate issuance service (auto trigger) | SPEC | — | P1 |
| U-037 | PDF generation (dompdf) | SPEC | — | P1 |
| U-038 | Public verification endpoint | SPEC | — | P1 |

---

## Build Order Within Group

1. U-035 — Entities (DONE)
2. U-036 — Issuance service (depends on U-035, G-PROGRESS U-022)
3. U-037 — PDF generation (depends on U-035, U-036)
4. U-038 — Verification endpoint (depends on U-035)

---

## Key Modules

- `lms_certificate` — U-035, U-036, U-037, U-038

---

## Exit Criteria

- [x] CertificateTemplate + UserCertificate entities
- [ ] Auto-issuance triggered on course completion event (U-036)
- [ ] PDF generation with template rendering via dompdf (U-037)
- [ ] Public `/certificate/verify/{code}` endpoint (U-038)

---

## Key Risks

- dompdf dependency: must be declared in composer.json, verify PHP 8.3 compatibility.
- Verification code must be unique + non-guessable (UUID v4 or HMAC-based).
- Issuance must be idempotent: duplicate events should not issue duplicate certificates.

---

*End of 06-G-CERTIFICATE.md*
