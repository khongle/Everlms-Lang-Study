# D03 — Unit Spec: Public Certificate Verification Endpoint

**Unit ID:** U-038
**Group:** G-CERTIFICATE
**Title:** Public verification endpoint
**Status:** SPEC
**Priority:** P1
**Assigned:** unassigned

---

## Scope

Public (no authentication required) endpoint to verify a certificate by its `verification_code`. Returns certificate details: holder name, course name, issue date. Used for third-party verification (e.g., LinkedIn, employers).

---

## Files to Touch

| File | Action | Module |
|------|--------|--------|
| `src/Controller/CertificateVerifyController.php` | Create | lms_certificate |
| `templates/certificate-verify.html.twig` | Create | lms_certificate |
| `lms_certificate.routing.yml` | Modify | lms_certificate |

---

## Interfaces / Services

- Route: `GET /certificate/verify/{code}` — public, no auth
- Looks up `UserCertificate` by `verification_code` field
- Returns: holder name (first name + last name only), course name, issue date
- Returns 404 with "Certificate not found" message for invalid code
- No PII beyond name + course (no email, user ID exposed)

---

## Acceptance Criteria

- [ ] `/certificate/verify/{valid-code}` returns HTML page with certificate details
- [ ] `/certificate/verify/{invalid-code}` returns 404 with user-friendly message
- [ ] Page is accessible without login (anonymous user)
- [ ] No email, user ID, or internal IDs exposed in the response
- [ ] Page is cacheable (Drupal page cache enabled for this route)
- [ ] Optional: JSON response available via `Accept: application/json` header

---

## Gate Results

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PENDING | — |
| G2 Lint | PENDING | — |
| G3 Tests | PENDING | — |
| G4 Adversarial | PENDING | Enumeration attack via sequential codes? Use UUID. |

---

## Dependencies

| Unit | Relationship |
|------|-------------|
| U-035 | UserCertificate entity with `verification_code` field |
| U-036 | Issuance service sets `verification_code` at creation |

---

**Feature Flag:** None

*End of U-038-certificate-verification.md*
