# WF-003 - Certificate issuance

## Business flow
When a learner completes a course / passes its final quiz, a `QuizPassedEvent` (or course-completion) subscriber checks eligibility, selects the course's `lms_certificate_template`, renders it to PDF via mPDF, and creates a `lms_user_certificate` record with a unique verification code. Issuance is **idempotent** — a learner who re-passes does not get a duplicate certificate. The learner is notified (WF-via F-017) and can download the PDF; editors can re-render via `lms:mock-certs` / `scripts/regenerate_all_certs.php`.

## Participating modules
[lms_certificate](../../C-Design/C05-modules/lms_certificate.md), [lms_quiz](../../C-Design/C05-modules/lms_quiz.md), [lms_notification](../../C-Design/C05-modules/lms_notification.md)

## Related features (B03)
[F-007](../B03-features/F-007-certificate-issuance.md)

## Trigger
`QuizPassedEvent` / course completion.

## Outcome
A single `lms_user_certificate` PDF issued per learner-per-course; learner notified.

## Design link
[C12 WF-003](../../C-Design/C12-workflows/WF-003-certificate-issuance.md)
