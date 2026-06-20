# WF-003 — Certificate Issuance — Use Case Folder

**Workflow:** WF-003 Certificate Issuance  
**Source workflow doc:** [`../../C12-workflows/`](../../C12-workflows/)  
**Business workflow:** [`../../../../B-Requirements/B04-business-workflows/WF-003-certificate-issuance.md`](../../../B-Requirements/B04-business-workflows/WF-003-certificate-issuance.md)  
**Implementing modules:** `lms_certificate`, `lms_quiz` (event origin)  

---

## Use cases in this folder

| File | ID | Type | Summary |
|------|----|------|---------|
| [UC-003-01-auto-issue-on-pass.md](UC-003-01-auto-issue-on-pass.md) | UC-003-01 | happy | Quiz pass triggers event subscriber → course found via traversal → PDF issued |
| [UC-003-02-duplicate-prevented.md](UC-003-02-duplicate-prevented.md) | UC-003-02 | edge | Student passes same quiz again — existing certificate prevents re-issue |
| [UC-003-03-no-template-fallback.md](UC-003-03-no-template-fallback.md) | UC-003-03 | edge | Course has no specific certificate template — system falls back to first active template |
| [UC-003-04-revoke-certificate.md](UC-003-04-revoke-certificate.md) | UC-003-04 | permission | Admin revokes a certificate; public verification now shows revoked status |

---

## Architecture notes

| Component | Implementation |
|-----------|---------------|
| Event trigger | `lms_quiz.passed` (`QuizPassedEvent`) dispatched by `QuizController::submitQuiz()` |
| Event listener | `CertificateEventListener::onQuizPassed()` in `lms_certificate` |
| Course traversal | Quiz → `field_quiz` on Lesson → `field_lessons` on Chapter → `field_chapters` on Course |
| PDF generation | `CertificateGenerator::generate()` using `mpdf/mpdf` (graceful NULL if missing) |
| Storage | `public://certificates/issued/Certificate_{code}.pdf` as Drupal managed file |
| Revocation | `RevokeCertificateForm` sets `field_revoked=1` + `field_revoke_reason`; no PDF deletion |
| Verification | Public route `/certificate/verify` (`_access: TRUE`) |
| Dependency | `mpdf/mpdf` must be installed via Composer; generator logs error and returns NULL if absent |
