---
usecase_id: UC-003-01
workflow: WF-003-certificate-issuance
type: happy
---

# UC-003-01 — Quiz Pass → Auto-Issue Certificate

**One-line purpose:** When a student passes a quiz, the `CertificateEventListener` traverses the content tree to locate the course, finds or falls back to a certificate template, generates a PDF via mPDF, persists a `lms_user_certificate` entity, and the student can download or verify the certificate.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Has just passed a quiz (via UC-002-01) |
| `QuizController::submitQuiz()` | Dispatches `QuizPassedEvent` after persisting the result |
| `CertificateEventListener::onQuizPassed()` | Handles the event; orchestrates course lookup and issuance |
| `CertificateManager::checkAndIssueCertificate()` | Deduplication check + template selection + generator call |
| `CertificateGenerator::generate()` | Renders PDF via mPDF |
| mPDF (`mpdf/mpdf`) | Third-party PDF library |

---

## Preconditions

- Student is authenticated and has just submitted a quiz.
- `QuizPassedEvent` has been dispatched (result term = `Passed`).
- `lms_certificate` module is installed and `CertificateEventListener` is registered as an event subscriber.
- The passed quiz is linked from a `lms_lesson.field_quiz`.
- That lesson belongs to a `lms_chapter` (via `field_lessons`) which belongs to a `lms_course` (via `field_chapters`).
- `mpdf/mpdf` Composer package is installed.
- An active `lms_certificate_template` entity exists (either course-specific or a fallback).

---

## Trigger

`QuizPassedEvent` dispatched during `QuizController::submitQuiz()` in the same HTTP request as the quiz submission.

---

## Main Flow

| # | Given / When / Then |
|---|---------------------|
| 1 | **Given** `QuizPassedEvent` is dispatched with the `QuizResult`, `Quiz`, and `User` |
| 2 | **When** `CertificateEventListener::onQuizPassed($event)` fires |
| 3 | **Then** the listener calls `$event->getQuiz()` to get the quiz entity |
| 4 | **Then** the listener queries `lms_lesson` storage for lessons where `field_quiz = {quiz_id}` to find the associated lesson |
| 5 | **Then** the listener queries `lms_chapter` storage for chapters where `field_lessons` contains the lesson |
| 6 | **Then** the listener queries `lms_course` storage for courses where `field_chapters` contains the chapter |
| 7 | **When** a course is found |
| 8 | **Then** `CertificateManager::checkAndIssueCertificate($user, $course, $quiz_result)` is called |
| 9 | **When** `checkAndIssueCertificate()` queries `lms_user_certificate` storage for existing `uid+field_course` pair |
| 10 | **Then** no existing certificate is found (happy path) |
| 11 | **Then** manager checks `$course->field_certificate_template`; finds a linked `lms_certificate_template` entity |
| 12 | **Then** a 10-character uppercase certificate code is generated: `strtoupper(substr(md5(uniqid()), 0, 10))` |
| 13 | **When** `CertificateGenerator::generate($template, $user, $course, $code)` is called |
| 14 | **Then** `certificate_pdf` theme is rendered to HTML string (tokens `[student_name]`, `[course_name]`, `[issue_date]` replaced); `template_preprocess_certificate_pdf()` resolves image file URIs to real server paths for mPDF |
| 15 | **Then** `new \Mpdf\Mpdf(['orientation' => $template->field_orientation])` creates PDF; `WriteHTML($html)` produces the output |
| 16 | **Then** PDF binary is saved as Drupal managed file at `public://certificates/issued/Certificate_{code}.pdf`; file entity saved with status permanent |
| 17 | **Then** a `lms_user_certificate` entity is created with: `uid={student}`, `field_template={template}`, `field_quiz_result={result}`, `field_course={course}`, `field_certificate_number={code}`, `field_pdf_file={file entity}`, `field_issued_at={now}`, `field_revoked=false` |
| 18 | **Then** `lms_user_certificate` entity is saved |
| 19 | **Then** event handler returns; student is redirected to `/quiz-result/{id}` (redirect happens after event dispatch in `submitQuiz()`) |
| 20 | **Then** student can visit `/my-certificates` (route `lms_progress.certificates`) to see and download the certificate |

---

## Entities Mutated

| Entity | Field | Change |
|--------|-------|--------|
| `lms_user_certificate` | all fields | created with uid, template, quiz_result, course, code, pdf_file, issued_at |
| Drupal file entity | `uri`, `status` | `public://certificates/issued/Certificate_{code}.pdf`, permanent |

---

## Observability Hooks

| Signal | Mechanism | Details |
|--------|-----------|---------|
| Event subscription | Symfony event dispatcher | `CertificateEventListener` registered as `event_subscriber` in `lms_certificate.services.yml` |
| mPDF missing | `\Drupal::logger('lms_certificate')->error(...)` | `CertificateGenerator::generate()` logs and returns NULL if `class_exists('\Mpdf\Mpdf')` is false |
| Certificate saved | No explicit success log | Entity save is silent; failure would throw an exception |
| PDF file on disk | `public://certificates/issued/` | Visible in Drupal file system / admin |
| Student certificate list | `/my-certificates` | Rendered by `UserPortalController::myCertificates()` |

---

## Acceptance Link

| B05 AC | E03 Test (planned) |
|--------|--------------------|
| [AC-059](../../../B-Requirements/B05-acceptance-criteria/AC-06-certification-badges.md#ac-059-certificate-is-auto-issued-when-a-quiz-is-passed) — Auto-issue on quiz pass | TEST-059 |
| [AC-062](../../../B-Requirements/B05-acceptance-criteria/AC-06-certification-badges.md#ac-062-certificate-pdf-contains-required-fields-rendered-from-template-tokens) — PDF contains tokens and images | TEST-062 |

---

## Notes

- **Chain traversal fragility:** The course lookup relies on `field_quiz` (Lesson), `field_lessons` (Chapter), `field_chapters` (Course) all being correctly populated. If any link is missing, `$course` is NULL and no certificate is issued — silently (no log in the listener for this case).
- **mPDF dependency:** If `mpdf/mpdf` is not installed, `CertificateGenerator::generate()` logs an error and returns NULL. `CertificateManager` receives NULL from the generator and must handle it gracefully (check implementation; if it saves `field_pdf_file=null` the entity is still created without a PDF file).
- **`field_expiry_date` type issue:** `lms_user_certificate.field_expiry_date` is defined as `datetime` (date-only display) but was previously noted as varchar in the known issues registry — verify actual DB column type before using expiry in any query.
- The event subscription registration is conditional: `CertificateEventListener` only registers if `\Drupal\lms_quiz\Event\QuizPassedEvent` class exists — safe for installations without `lms_quiz`.
