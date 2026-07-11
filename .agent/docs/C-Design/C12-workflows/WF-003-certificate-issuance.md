# C12 Workflow - WF-003 Certificate issuance

## Business source
[B04 WF-003](../../B-Requirements/B04-business-workflows/WF-003-certificate-issuance.md)

## Modules
[lms_certificate](../C05-modules/lms_certificate.md), [lms_quiz](../C05-modules/lms_quiz.md), [lms_notification](../C05-modules/lms_notification.md)

## Related features (B03)
[F-007](../../B-Requirements/B03-features/F-007-certificate-issuance.md)

## State model
`lms_user_certificate`:
| State | Meaning |
| ----- | ------- |
| approved_or_completed (issued) | rendered + stored (idempotent) |
| rejected_or_returned (revoked) | `field_revoked` set with reason |
| archived (expired) | past `field_expiry_date` |

## Issuance invariant
- `QuizPassedEvent` must resolve the owning course before issuing a certificate.
- Primary course source is `lms_quiz.field_course`.
- Legacy fallback is `lms_lesson.field_quiz → lms_chapter.field_lessons → lms_course.field_chapters`.
- Issuance is idempotent by learner/course: a learner can have multiple passed attempts but only one active `lms_user_certificate` for the course.
- Public verification resolves by `field_certificate_number` and must show revoked certificates as invalid.

## Permission model
- Initiator: system (event subscriber on `QuizPassedEvent`/completion).
- Admin: manager can revoke/regenerate.

## As-built issuance, verification & revoke (round-2)
- Issuance fires from `QuizPassedEvent` (`CertificateEventListener`), resolves the course from `lms_quiz.field_course` (legacy fallback via lesson→chapter→course), renders a PDF (mPDF) into `field_pdf_file`, and stores `field_certificate_number`. Verified: grading result 98 issued cert `FDB2B9BA40` with a PDF, idempotent (single row per learner/course).
- **Public verifier** `/certificate/verify` (`VerificationForm`, field `certificate_code`) resolves by `field_certificate_number`, shows recipient full name, course, code, issue date, and marks `field_revoked` certificates as **invalid**.
- **Revoke** `/workspace/certificates/{id}/revoke` (`RevokeCertificateForm`, `administer lms_course`) sets `field_revoked` + reason and returns to `/workspace/certificates`. **Regenerate** re-renders the PDF.
- Learner list `/my-certificates` shows each certificate with number, issue date, and a PDF download.
- Names everywhere use `field_first_name`/`field_last_name` via `lms_base_user_format_name_alter()` (not the login username).

## Notifications
Issuance notification + download link to learner (role-protected). Idempotent: re-pass does not duplicate.

## Round-2 QA evidence
Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/003-certificate-issuance/`. Issues fixed: `WF-003-001..004` (name, revoke access, revoke 500, stat label).
