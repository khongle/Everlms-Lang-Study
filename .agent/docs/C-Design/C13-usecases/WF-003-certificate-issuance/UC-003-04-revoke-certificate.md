---
usecase_id: UC-003-04
workflow: WF-003-certificate-issuance
type: permission
---

# UC-003-04 — Admin Revokes Certificate → Verification Shows Revoked Status

**One-line purpose:** An admin with the `revoke any certificate` permission revokes a student's certificate by providing a reason; subsequent public verification of the certificate number returns a `revoked` status instead of `valid`.

---

## Actors

| Actor | Role |
|-------|------|
| Admin | Holds `revoke any certificate` permission |
| `RevokeCertificateForm` | ConfirmFormBase; sets `field_revoked=1` and `field_revoke_reason` |
| `hook_entity_operation()` | Adds "Revoke" operation link to certificate list if user has permission |
| Student / Verifier | Public visitor who checks the certificate via `/certificate/verify` |
| `VerificationForm` | Looks up certificate by `field_certificate_number`; returns revoked status |

---

## Preconditions

- A `lms_user_certificate` entity exists with `field_revoked=false`.
- The acting user has `revoke any certificate` permission (defined in `lms_certificate.permissions.yml`).
- The admin navigates to `/admin/lms/certificates/{lms_user_certificate}/revoke`.

---

## Trigger

Admin submits `RevokeCertificateForm` at `/admin/lms/certificates/{id}/revoke`.

---

## Main Flow

| # | Given / When / Then |
|---|---------------------|
| 1 | **Given** admin navigates to `/admin/lms/certificates/{lms_user_certificate}/revoke` |
| 2 | **When** `RevokeCertificateForm` (ConfirmFormBase) renders |
| 3 | **Then** form shows a confirmation question and a `revoke_reason` textarea |
| 4 | **When** admin enters a reason and confirms |
| 5 | **Then** `RevokeCertificateForm::submitForm()` sets `$certificate->field_revoked = 1` and `$certificate->field_revoke_reason = $reason` |
| 6 | **Then** the `lms_user_certificate` entity is saved |
| 7 | **Then** admin is redirected (to admin certificate dashboard or the certificate entity) |
| 8 | **When** any visitor (authenticated or anonymous) submits certificate code at `GET /certificate/verify` with the revoked certificate's number |
| 9 | **When** `VerificationForm::submitForm()` runs |
| 10 | **Then** `lms_user_certificate` storage is queried by `field_certificate_number = {code}` |
| 11 | **Then** the entity is found; `field_revoked = true` is detected |
| 12 | **Then** `certificate_verification_result` theme is rendered with `valid=false` and revoked status indicator |
| 13 | **Then** the student's certificate number is no longer considered valid by any verification query |

---

## Entities Mutated

| Entity | Field | Change |
|--------|-------|--------|
| `lms_user_certificate` | `field_revoked` | `false` → `true` (1) |
| `lms_user_certificate` | `field_revoke_reason` | set to admin's entered text |

---

## Observability Hooks

| Signal | Mechanism | Details |
|--------|-----------|---------|
| Revoke operation visible | `hook_entity_operation()` | "Revoke" link shown in certificate entity list for users with `revoke any certificate` |
| Revocation persisted | Entity save | No explicit log on revoke; entity delta visible in entity revision (if revisions were enabled — `«unverified»` whether `lms_user_certificate` is revisionable) |
| Regeneration blocked | `CertificateAdminController::regenerateCertificate()` | Returns early if `field_revoked=true` — prevents regenerating a revoked cert |
| Public verification result | `/certificate/verify` | Revoked status shown via `certificate_verification_result` theme |

---

## Acceptance Link

| B05 AC | E03 Test (planned) |
|--------|--------------------|
| [AC-064](../../../B-Requirements/B05-acceptance-criteria/AC-06-certification-badges.md#ac-064-verification-of-a-revoked-certificate-shows-revoked-status) — Revoked cert shows revoked status at verify page | TEST-064 |

---

## Notes

- **PDF not deleted on revocation:** Revoking a certificate only sets `field_revoked=1` on the entity. The PDF file at `public://certificates/issued/Certificate_{code}.pdf` remains on disk and is technically still accessible via its direct URL if anyone knows the path. There is no mechanism to delete or invalidate the PDF file as part of revocation.
- **Regeneration guard:** `CertificateAdminController::regenerateCertificate()` blocks re-generation of a revoked certificate, preventing the revoked status from being circumvented via the admin UI.
- **Permission:** `revoke any certificate` is a named permission in `lms_certificate.permissions.yml` — distinct from `administer lms_course`. It must be explicitly granted to the intended role(s) in Drupal's permission configuration.
- **`field_expiry_date` type risk:** `lms_user_certificate.field_expiry_date` is defined as `datetime` in the entity but was noted as `varchar` in the DB column (known issue in the risk register). Queries filtering on expiry date may behave unexpectedly until the field type inconsistency is resolved.
