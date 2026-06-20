# F-007 Acceptance Criteria - Certificate issuance

## Core criteria
1. **Given** a learner passes a course's final quiz, **when** `QuizPassedEvent` fires, **then** a `lms_user_certificate` is created and a PDF rendered from the course's `lms_certificate_template`.
2. **Given** an already-issued certificate, **when** the learner re-passes, **then** **no duplicate** certificate is created (idempotent).
3. **Given** an issued certificate, **when** the learner opens their certificates, **then** they can download the PDF showing their name, course, date, and unique code.
4. **Given** an editor changes a certificate template, **when** new certificates issue, **then** they use the updated layout (no code change).

## Evidence expected
- Module: [lms_certificate](../../C-Design/C05-modules/lms_certificate.md); mPDF rendering.
- Test: issuance-on-pass kernel test; idempotency test; template-edit smoke; `lms:mock-certs` for fixtures (E03).
