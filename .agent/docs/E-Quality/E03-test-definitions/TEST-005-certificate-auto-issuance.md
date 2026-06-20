---
id: TEST-005
type: Kernel
priority: P0 — Must
ac: AC-059, AC-060
feature: F-006 (Certification & Badges)
module: lms_certificate, lms_quiz
service: lms_certificate.generator (CertificateGenerator), lms_certificate.manager (CertificateManager), lms_certificate.event_listener (CertificateEventListener)
status: NOT_YET_AUTOMATED
---

# TEST-005 — Certificate Auto-Issuance

> Verifies the full `QuizPassedEvent` → `CertificateEventListener` → `CertificateManager` → `CertificateGenerator` chain. Tests entity traversal (Quiz→Lesson→Chapter→Course), duplicate-issuance guard, and PDF generation graceful degradation when mPDF is absent.

**Story:** [D03 lms_certificate](../../D-Development/D03-stories/lms_certificate.md)
**AC:** [AC-059 — Certificate auto-issued on quiz pass (B05 AC-06)](../../B-Requirements/B05-acceptance-criteria/AC-06-certification-badges.md) · [AC-060 — No duplicate certificates (B05 AC-06)](../../B-Requirements/B05-acceptance-criteria/AC-06-certification-badges.md)
**C12 Workflow:** [WF-003 Certificate Issuance](../../C-Design/C12-workflows/WF-003-certificate-issuance.md)
**C05 Module:** [lms_certificate](../../C-Design/C05-modules/lms_certificate.md) · [lms_quiz](../../C-Design/C05-modules/lms_quiz.md)

---

## TC-005-A: `CertificateEventListener` traverses Quiz → Lesson → Chapter → Course

**Layer:** Kernel
**Given:**
- A `lms_course` entity with `field_certificate_template` pointing to a published `lms_certificate_template`
- The course has one `lms_chapter` (via `field_chapters`) that has one `lms_lesson` (via `field_lessons`)
- The lesson has `field_quiz` referencing a `lms_quiz` entity
- A `lms_quiz_result` and `lms_quiz` are available via `QuizPassedEvent`

**When:** The Drupal event system dispatches `QuizPassedEvent::EVENT_NAME` (`lms_quiz.passed`)

**Then:**
- `CertificateEventListener::onQuizPassed()` fires (event subscriber registered in services.yml)
- The listener loads `field_quiz` references on `lms_lesson` entities to find the lesson containing the quiz
- Then loads `field_lessons` references on `lms_chapter` to find the chapter
- Then loads `field_chapters` references on `lms_course` to find the course
- `CertificateManager::checkAndIssueCertificate()` is called with the resolved course

### Test Data

| Input | Expected Output |
|-------|----------------|
| Quiz linked through lesson → chapter → course | `CertificateManager::checkAndIssueCertificate()` called once |
| Quiz not linked to any lesson | No certificate issued; listener exits gracefully |
| Course has no `field_certificate_template` | Falls back to first active `lms_certificate_template`; if none, no certificate |

---

## TC-005-B: Certificate is created with unique code, no duplicate on second pass

**Layer:** Kernel
**Given:**
- Entity traversal resolves the course correctly (TC-005-A preconditions)
- mPDF is NOT available (test environment — mock `class_exists('\Mpdf\Mpdf')` = FALSE)

**When:** `CertificateManager::checkAndIssueCertificate()` is called for the first time

**Then:**
- A `lms_user_certificate` entity is created in `lms_user_certificate` DB table
- `field_certificate_number` is a 10-character uppercase string (MD5 substr: e.g. `A3F9B2C1D7`)
- `field_template` references the resolved `lms_certificate_template`
- `field_course` references the resolved course
- `field_pdf_file` is NULL (mPDF absent → generator returns NULL, logged as error)
- Method returns the `lms_user_certificate` entity (not FALSE)

**When:** `checkAndIssueCertificate()` is called a second time for the same user + course

**Then:**
- No second `lms_user_certificate` entity is created
- `SELECT COUNT(*) FROM lms_user_certificate WHERE uid=X AND field_course_target_id=Y` = 1
- Method returns FALSE (existing cert detected)

### Test Data

| Input | Expected Output |
|-------|----------------|
| First call, no prior cert | 1 cert entity created; `field_certificate_number` = 10-char string |
| Second call (duplicate guard) | No new entity; COUNT = 1 |
| mPDF available (integration env) | `field_pdf_file` points to a file at `public://certificates/issued/Certificate_<code>.pdf` |

---

## TC-005-C: `CertificateGenerator::generate()` graceful degradation without mPDF

**Layer:** Kernel
**Given:** `class_exists('\Mpdf\Mpdf')` returns FALSE (mPDF not installed)

**When:** `CertificateGenerator::generate($template, $user, $course, $code)` is called

**Then:**
- Method returns NULL without throwing an exception
- A `\Psr\Log\LoggerInterface` error log entry is written mentioning mPDF
- No file is created at `public://certificates/issued/`

---

## TC-005-D: Certificate verification form finds valid certificate

**Layer:** Functional
**Given:** A `lms_user_certificate` entity exists with `field_certificate_number = 'ABCDE12345'`, `field_revoked = FALSE`

**When:** A visitor submits the verification form at `/certificate/verify` with code `ABCDE12345`

**Then:**
- `VerificationForm::submitForm()` loads the entity by `field_certificate_number`
- The `certificate_verification_result` theme renders with student name, course name, issue date
- `field_revoked = FALSE` is displayed as "valid"

**And when** code = `ABCDE12345` but `field_revoked = TRUE`:

**Then:** The result shows "revoked" status with `field_revoke_reason`

---

## Setup / Teardown

- Setup: Install `lms_base`, `lms_course`, `lms_chapter`, `lms_lesson`, `lms_question`, `lms_quiz`, `lms_certificate`; install entity schemas for all; install `quiz_results` taxonomy with `Passed` term; create a published `lms_certificate_template` entity; build course → chapter → lesson → quiz hierarchy; create student user
- Teardown: Delete all test entities; clean up any generated PDFs from `public://certificates/issued/`

## Notes

- `CertificateEventListener` source: `web/modules/custom/lms_certificate/src/EventSubscriber/CertificateEventListener.php`
- `CertificateManager` source: `web/modules/custom/lms_certificate/src/Service/CertificateManager.php`
- `CertificateGenerator` source: `web/modules/custom/lms_certificate/src/Service/CertificateGenerator.php`
- The event subscriber is registered conditionally: it only subscribes to `lms_quiz.passed` if `QuizPassedEvent` class exists. In Kernel tests, explicitly install both `lms_quiz` and `lms_certificate` modules to ensure the subscriber registers
- `field_expiry_date` on `lms_user_certificate` is a `datetime` field but the data type mismatch (`varchar` vs `int`) is a known issue — do not assert specific datetime parsing; assert non-null is sufficient
- Expected file: `web/modules/custom/lms_certificate/tests/src/Kernel/CertificateAutoIssuanceTest.php` (TC-005-A/B/C) + `web/modules/custom/lms_certificate/tests/src/Functional/CertificateVerificationTest.php` (TC-005-D)
