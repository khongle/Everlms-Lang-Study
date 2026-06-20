# F-007 - Certificate issuance

**Status:** built
**Primary module:** [lms_certificate](../../C-Design/C05-modules/lms_certificate.md)
**Epic:** [E05 Certification](../../D-Development/D02-epics/E05-certification.md)
**Related workflows:** [WF-003 Certificate issuance](../B04-business-workflows/WF-003-certificate-issuance.md)
**Acceptance:** [B05 F-007](../B05-acceptance-criteria/F-007-certificate-issuance.md)
**Source:** `web/modules/custom/lms_certificate/`

## Requirement
On course completion / quiz pass, the system auto-issues a certificate: an event subscriber on `QuizPassedEvent` (or course completion) renders a `lms_certificate_template` to PDF (mPDF) and stores a `lms_user_certificate` record with a unique code. Learners download their certificate; editors manage templates without code.

## Business value
Certificates are the tangible outcome learners pay for — proof of completion for compliance/occupational-safety contexts; template editing keeps branding in editors' hands.

## Scope notes
- `lms_certificate_template` (config-like content entity) holds the editable layout; `lms_user_certificate` is the issued instance.
- Issuance must be **idempotent** (no duplicate certs on re-pass) — reliability NFR.
- `lms:mock-certs` and `scripts/regenerate_all_certs.php` support bulk (re)generation.

## Cross references
- Design: [C04 lms_certificate_template](../../C-Design/C04-entities/lms_certificate_template.md), [C04 lms_user_certificate](../../C-Design/C04-entities/lms_user_certificate.md), [C12 WF-003](../../C-Design/C12-workflows/WF-003-certificate-issuance.md)
