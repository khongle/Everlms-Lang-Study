---
id: WF-003
name: Certificate Issuance
status: APPROVED
features: [F-005]
---

# WF-003 — Certificate Issuance

End-to-end flow from a `QuizPassedEvent` through entity traversal, PDF generation via mPDF, `lms_user_certificate` creation, and student-facing verification. Triggered automatically; no student action required.

## Trigger

`lms_quiz.passed` event dispatched by `QuizController::submitQuiz()` when a quiz result term = `Passed`.

## Actors

| Actor | Role |
|-------|------|
| System | Event subscriber, PDF generator, file manager |
| Student | Passive recipient; views certificate in profile |
| Admin | Optional: revoke certificate, regenerate PDF, manage templates |

## Preconditions

- `QuizPassedEvent` was dispatched (result term = `Passed`; no pending essay responses).
- At least one active `lms_certificate_template` entity exists (`status=1`).
- `mpdf/mpdf` Composer package is installed.

## Happy Path

| Step | Actor | Action | System Response | Modules / Code | Feature |
|------|-------|--------|----------------|----------------|---------|
| 1 | System | `QuizPassedEvent` fired | `CertificateEventListener::onQuizPassed($event)` is invoked (registered in `lms_certificate.services.yml` as `event_subscriber`). Subscription is conditional: only registered if `QuizPassedEvent` class exists. | `lms_certificate/src/EventSubscriber/CertificateEventListener.php` | F-005 |
| 2 | System | Entity traversal to find the Course | Starting from the `lms_quiz` entity obtained from the event, traverses: `lms_quiz` → `lms_lesson` (via `field_quiz`) → `lms_chapter` (via `field_lessons`) → `lms_course` (via `field_chapters`). | `CertificateEventListener::onQuizPassed()` | F-005 |
| 3 | System | `CertificateManager::checkAndIssueCertificate()` | Checks whether a `lms_user_certificate` already exists for this user+course pair (prevents double-issue). If a certificate already exists, returns the existing entity without re-issuing. | `lms_certificate/src/Service/CertificateManager.php` | F-005 |
| 4 | System | Find certificate template | Checks `lms_course.field_certificate_template` for a course-specific template. Falls back to first active `lms_certificate_template` entity if no course-specific template is set. | `CertificateManager::checkAndIssueCertificate()` | F-005 |
| 5 | System | Generate unique certificate code | 10-character uppercase substring of an MD5 hash. | `CertificateManager` | F-005 |
| 6 | System | `CertificateGenerator::generate()` | Renders `certificate_pdf` Twig theme to HTML (tokens: `[student_name]`, `[course_name]`, `[issue_date]`). `template_preprocess_certificate_pdf()` resolves file URIs (background, logo, signature, seal images) to server real-paths so mPDF can read them. Instantiates `\Mpdf\Mpdf` with A4 + orientation from `field_orientation`. Produces PDF binary. | `lms_certificate/src/Service/CertificateGenerator.php` | F-005 |
| 7 | System | Save PDF as managed file | Saved to `public://certificates/issued/Certificate_{code}.pdf` as a Drupal managed file entity. Returns `FileInterface`. | `CertificateGenerator::generate()` | F-005 |
| 8 | System | Persist `lms_user_certificate` | Entity saved with: `uid`, `field_template`, `field_quiz_result`, `field_course`, `field_certificate_number` (unique code), `field_pdf_file`, `field_issued_at`. | `CertificateManager::checkAndIssueCertificate()` | F-005 |
| 9 | Student | Views `/my-certificates` | `UserPortalController::myCertificates()` loads all `lms_user_certificate` for user; renders `lms_my_certificates` theme with certificate list and download links. | `lms_progress` | F-005 |
| 10 | Student / Public | Verifies certificate at `/certificate/verify` | `VerificationForm` accepts certificate number; queries by `field_certificate_number`; renders `certificate_verification_result` theme. Shows revoked status if `field_revoked=TRUE`. | `lms_certificate` | F-005 |

## Error Paths

| Step | Condition | System Response | Recovery |
|------|-----------|----------------|----------|
| 1-E1 | `QuizPassedEvent` not dispatched (essay pending) | `CertificateEventListener` never fires | Instructor grades essay; event must be re-dispatched manually (no automated re-trigger — **planned/stub**) |
| 2-E1 | Quiz not linked to any lesson (`field_quiz` empty on all lessons) | Traversal returns NULL course; `checkAndIssueCertificate()` not called; certificate silently not issued | Admin must ensure quiz is linked to a lesson |
| 3-E1 | Duplicate: certificate already issued | `CertificateManager` returns existing entity; no new entity created | No action needed |
| 4-E1 | No active `lms_certificate_template` | `CertificateManager` cannot find a template; returns `false`; no certificate issued | Admin creates and activates a template |
| 6-E1 | `mpdf/mpdf` not installed | `CertificateGenerator::generate()` checks `class_exists('\Mpdf\Mpdf')`; logs error and returns `NULL` gracefully | Install `mpdf/mpdf` via Composer |
| 6-E2 | Image file path not resolvable | `template_preprocess_certificate_pdf()` provides real-paths; if file missing, mPDF may skip image | Admin re-uploads template images |
| 7-E1 | File system write fails | Generator logs error, returns `NULL`; `UserCertificate` not saved | Check `public://certificates/issued/` directory permissions |

## Admin Operations (as-built)

| Operation | Route | Access |
|-----------|-------|--------|
| View all certificates | `/workspace/certificates` | `administer lms_course` |
| Revoke a certificate | `/admin/lms/certificates/{id}/revoke` | `revoke any certificate` |
| Regenerate PDF | `/workspace/certificates/{id}/regenerate` | `administer lms_course` |
| Manage templates | `/workspace/certificate-templates` | `administer lms_course` |
| Preview template PDF | `/workspace/certificate-templates/{id}/preview` | `administer lms_course` |

## Success Criteria

- `lms_user_certificate` entity persists with `field_certificate_number`, `field_pdf_file`, `field_course`, `field_quiz_result`.
- PDF file exists at `public://certificates/issued/Certificate_{code}.pdf`.
- Student can download PDF from `/my-certificates`.
- Public verification at `/certificate/verify` returns valid result for the issued code.
- Duplicate invocation produces no second certificate (idempotent).

## Business Rules

- **Event-driven only**: certificate issuance is triggered exclusively by `QuizPassedEvent`. No scheduled or manual trigger exists in the current implementation.
- **Deduplication**: `CertificateManager` queries `lms_user_certificate` by `uid` + `field_course` before issuing. One certificate per student per course.
- **Template priority**: course-specific `field_certificate_template` on the `lms_course` entity takes precedence; falls back to the first active global template.
- **Token support**: `field_body` in the template supports `[student_name]`, `[course_name]`, `[issue_date]`. Custom layout via `field_layout` (raw HTML/Twig).
- **Revocation**: admin can revoke via `RevokeCertificateForm`; sets `field_revoked=TRUE` + `field_revoke_reason`. Public verify page shows revoked status.
- **`field_expiry_date` type bug**: `lms_user_certificate.field_expiry_date` is stored as `datetime` in the entity definition but the DB column is `varchar` (not `int`/timestamp) — **bug, as-built**.
- **Badge awards**: `lms_badge` module has entity definitions but **no award logic** — badges are not issued on certificate issuance or any other trigger. Fully a stub.
- **No notification on issue**: `CertificateEventListener` does not dispatch a notification. The `lms_notification` `NotificationEventSubscriber` sends a `quiz_result` notification on `QuizPassedEvent`, but a dedicated `certificate` notification type is not dispatched automatically.

## Related Docs

- [WF-002 — Quiz Submit & Grading](./WF-002-quiz-grading.md) — source of `QuizPassedEvent`
- [WF-001 — Enrollment & Learning](./WF-001-enrollment-learning.md) — enrollment prerequisite
- Source: `web/modules/custom/lms_certificate/src/EventSubscriber/CertificateEventListener.php`
- Source: `web/modules/custom/lms_certificate/src/Service/CertificateManager.php`
- Source: `web/modules/custom/lms_certificate/src/Service/CertificateGenerator.php`
- Source: `web/modules/custom/lms_certificate/lms_certificate.module` (`template_preprocess_certificate_pdf`)
