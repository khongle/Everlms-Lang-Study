# C04 Entity - lms_user_certificate

**Display name:** User Certificate (issued)
**Owning module(s):** [lms_certificate](../C05-modules/lms_certificate.md)
**Mode:** as-built (Content Entity)

## Purpose
An issued certificate instance for a learner-course, with the rendered PDF, a unique number, validity, and revocation state.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | recipient |
| field_template | ref → lms_certificate_template |
| field_course | ref → lms_course |
| field_quiz_result | ref → lms_quiz_result (the passing attempt) |
| field_certificate_number | unique verification code |
| field_pdf_file | rendered PDF (file) |
| field_issued_at, field_expiry_date | validity window |
| field_revoked, field_revoke_reason | revocation |

## Relationships
- N→1 `user`, `lms_course`, `lms_certificate_template`, `lms_quiz_result`.

## Editorial/data management
Created by the `QuizPassedEvent` subscriber (idempotent — one per learner-course). ~24 live certificates. Managers may revoke. See [WF-003](../C12-workflows/WF-003-certificate-issuance.md).
