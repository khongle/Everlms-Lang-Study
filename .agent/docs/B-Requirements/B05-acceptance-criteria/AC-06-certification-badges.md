---
feature: F-006
status: DRAFT
---

# B05 — Acceptance Criteria for F-006 — Certification & Badges

> Feature source: `../B03-features/06-certification-badges.md`
> Implementing modules: `lms_certificate`, `lms_badge`
> Implementation maturity: `lms_certificate` = **fully as-built** (event subscriber, PDF generation, verification, revocation). `lms_badge` = **minimal stub** (entity definitions only — zero award logic).

---

## AC-059: Certificate is auto-issued when a quiz is passed

**Priority:** Must
**Given** a `lms_course` has `field_certificate_template` set to a published `lms_certificate_template` entity
**And** the course contains a lesson that has a linked `lms_quiz` via `field_quiz`
**When** a student passes that quiz (score >= `field_threshold`) and `QuizPassedEvent` is dispatched
**Then** `CertificateEventListener::onQuizPassed()` traverses Quiz → Lesson (via `field_quiz`) → Chapter (via `field_lessons`) → Course (via `field_chapters`) to locate the course
**And** `CertificateManager::checkAndIssueCertificate()` checks that no existing certificate for this user + course exists (no duplicate issue)
**And** a `lms_user_certificate` entity is created with a unique 10-character uppercase certificate code in `field_certificate_number`
**And** a PDF is generated via `CertificateGenerator::generate()` using mPDF and stored as a managed file at `public://certificates/issued/Certificate_{code}.pdf`

**Status:** as-built — the full event subscriber → manager → generator chain is implemented. Requires `mpdf/mpdf` Composer package; generator returns NULL gracefully if mPDF is absent (logs error).

---

## AC-060: Passing a quiz twice does not issue a duplicate certificate

**Priority:** Must
**Given** a `lms_user_certificate` entity already exists for a user + course pair
**When** the student passes the same course's quiz again and `CertificateEventListener::onQuizPassed()` fires
**Then** `CertificateManager::checkAndIssueCertificate()` detects the existing certificate via an entity query and returns without creating a second entity
**And** the existing PDF file is not modified or deleted

**Status:** as-built — `checkAndIssueCertificate()` performs a duplicate check before creating.

---

## AC-061: Certificate falls back to the first active template if no course-specific template is set

**Priority:** Must
**Given** a `lms_course` has no value in `field_certificate_template`
**When** `CertificateManager::checkAndIssueCertificate()` runs after a quiz pass
**Then** the manager falls back to the first active `lms_certificate_template` entity (status=1) found in the storage
**And** a certificate is still issued using that fallback template

**Status:** as-built — fallback logic is implemented in `checkAndIssueCertificate()`.

---

## AC-062: Certificate PDF contains required fields rendered from template tokens

**Priority:** Must
**Given** a `lms_certificate_template` has `field_body` containing tokens `[student_name]`, `[course_name]`, `[issue_date]` and has `field_background`, `field_logo`, `field_signature`, and `field_seal` images set
**When** `CertificateGenerator::generate()` renders the `certificate_pdf` theme and calls mPDF
**Then** the generated PDF contains the student's display name, the course name, the issue date, and the unique certificate code
**And** `template_preprocess_certificate_pdf()` resolves file URIs to real server paths so mPDF can embed the background, logo, signature, and seal images

**Status:** as-built — token replacement, file path resolution, and mPDF rendering are all implemented.

---

## AC-063: Public certificate verification returns valid certificate details

**Priority:** Must
**Given** a `lms_user_certificate` entity exists with `field_certificate_number = 'ABCDEF1234'` and `field_revoked=false`
**When** any visitor (anonymous or authenticated) submits the code `ABCDEF1234` in the form at `/certificate/verify`
**Then** `VerificationForm::submitForm()` queries `lms_user_certificate` by `field_certificate_number`, finds the entity, and rebuilds the form to display the `certificate_verification_result` theme showing the student name, course name, issue date, and a `valid` status indicator
**And** the HTTP response is 200

**Status:** as-built — `VerificationForm` is fully implemented. The verify route is public (`_access: TRUE`).

---

## AC-064: Verification of a revoked certificate shows revoked status

**Priority:** Must
**Given** a `lms_user_certificate` entity has `field_revoked=true`
**When** any visitor submits the certificate code at `/certificate/verify`
**Then** `VerificationForm::submitForm()` finds the entity and the `certificate_verification_result` theme displays a "this certificate has been revoked" indicator
**And** the HTTP response is 200 (not a redirect or 410)

**Status:** as-built — `VerificationForm` checks `field_revoked` and passes the revoked status to the theme.

---

## AC-065: Admin can revoke a certificate with a reason

**Priority:** Must
**Given** a user with `revoke any certificate` permission accesses `/admin/lms/certificates/{lms_user_certificate}/revoke`
**When** the user enters a revoke reason and confirms the `RevokeCertificateForm`
**Then** `RevokeCertificateForm::submitForm()` sets `field_revoked=true` and `field_revoke_reason` on the `lms_user_certificate` entity and saves
**And** subsequent calls to `/certificate/verify` for that certificate code return the revoked status (AC-064)

**Status:** as-built — `RevokeCertificateForm` is a `ConfirmFormBase` with a `revoke_reason` textarea; fully implemented.

---

## AC-066: Admin can regenerate a non-revoked certificate PDF

**Priority:** Must
**Given** a user with `administer lms_course` permission accesses `/workspace/certificates/{lms_user_certificate}/regenerate`
**When** `CertificateAdminController::regenerateCertificate()` runs
**Then** the method blocks regeneration if `field_revoked=true` (returns an error or redirect)
**And** for non-revoked certificates, `CertificateGenerator::generate()` is called with the existing `field_certificate_number` to regenerate the PDF file

**Status:** as-built — `regenerateCertificate()` checks the revoked flag before re-generating.

---

## AC-067: Admin certificate dashboard and template management work

**Priority:** Must
**Given** a user with `administer lms_course` permission accesses `/workspace/certificates`
**When** `CertificateAdminController::adminDashboard()` runs
**Then** the response includes total certificate count, revoked count, and the count of certificates issued in the last 7 days, plus a list of up to 50 recent certificates
**And** at `/workspace/certificate-templates`, `adminTemplates()` lists all templates with edit and preview operation links
**And** `templatePreview()` at `/workspace/certificate-templates/{lms_certificate_template}/preview` generates a PDF using mock data and returns it as an inline PDF response

**Status:** as-built — all three controller methods are fully implemented.

---

## AC-068: Badge award logic — blocked — not implemented

**Priority:** Must
**Given** a `lms_badge` entity exists with `field_criteria` JSON describing an award condition (e.g. `{"type":"courses_completed","count":5}`)
**When** a student meets the described condition (e.g. completes 5 courses)
**Then** a `lms_user_badge` entity is created for that student, and the student is notified of the award

**Status:** **blocked — not implemented.** `lms_badge` module is a minimal stub: only entity definitions (`lms_badge` and `lms_user_badge`) exist. There is no service, cron job, event subscriber, or any code that evaluates `field_criteria` or creates `lms_user_badge` entities. Badge admin CRUD is available via auto-generated routes at `/manage/badges` but no badge is ever awarded automatically. This feature cannot be tested until award logic is implemented.

---

## Local traceability (F-006)

| AC ID | Test Case | Implementing Story / Module | Status |
|-------|-----------|-----------------------------|--------|
| AC-059 | T-059 | cert-auto-issue / lms_certificate | PENDING |
| AC-060 | T-060 | cert-no-duplicate / lms_certificate | PENDING |
| AC-061 | T-061 | cert-template-fallback / lms_certificate | PENDING |
| AC-062 | T-062 | cert-pdf-tokens / lms_certificate | PENDING |
| AC-063 | T-063 | cert-verify-valid / lms_certificate | PENDING |
| AC-064 | T-064 | cert-verify-revoked / lms_certificate | PENDING |
| AC-065 | T-065 | cert-revoke / lms_certificate | PENDING |
| AC-066 | T-066 | cert-regenerate / lms_certificate | PENDING |
| AC-067 | T-067 | cert-admin-dashboard / lms_certificate | PENDING |
| AC-068 | T-068 | badge-award / lms_badge | PENDING |

> Cross-feature traceability lives in `E05-traceability.md`.
