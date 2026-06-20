# C06 — API: Certificate (Verify Endpoint, PDF Download, Issuance)

**Documentation mode:** as-built (all services, forms, PDF generation fully implemented; no JSON REST endpoint for verify — HTML form only; no PDF download route for learners)

**Module:** `lms_certificate`
**Entities:** [lms_certificate_template](../C04-entities/01-content-entities.md), [lms_user_certificate](../C04-entities/01-content-entities.md)
**Module spec:** [lms_certificate](../C05-modules/lms_certificate.md)
**Depends on:** `lms_quiz` (subscribes to `QuizPassedEvent`)

---

## API Overview

**Route Group:** Public / Admin
**Base paths:** `/certificate/verify`, `/workspace/certificate*`, `/admin/lms/certificates/*`
**Authentication:** Public for verify; `administer lms_course` for admin; `revoke any certificate` for revocation
**Consumers:** Public web (verify form), admin workspace, internal event system
**Response format:** HTML (Twig) for all routes; PDF inline response for template preview; no JSON REST endpoints in this module

**Key design note:** Certificate issuance is NOT triggered by an HTTP request. It is fully event-driven: `CertificateEventListener` subscribes to `lms_quiz.passed` (`QuizPassedEvent`) and calls `CertificateManager::checkAndIssueCertificate()`. The only public-facing API surface is the verification form at `/certificate/verify`.

---

## Endpoint: GET+POST `/certificate/verify`

**Route:** `lms_certificate.verify`
**Module:** `lms_certificate`
**Handler:** `Drupal\lms_certificate\Form\VerificationForm` (FormBase)
**Authentication:** `_access: TRUE` — fully public, anonymous
**Methods:** GET (renders form), POST (submits code)

### Request

**Form Fields:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `code` | text | Yes | Certificate number (10-char uppercase MD5 substring) — printed on PDF certificate |

### Response

**Success (200) — certificate found, active:**

HTML page rendered via `certificate_verification_result` theme:
```
Recipient name, Course name, Issue date, Certificate number, Status: Active
```

**Success (200) — certificate found, revoked:**
```
Status: Revoked, Revocation reason (if set)
```

**Success (200) — not found:**
```
Error: Certificate not found or invalid code
```

**Response Caching:** No caching (form rebuilds on submit with `#rebuild: TRUE`).

### Logic (as-built)

1. User enters `field_certificate_number` value (e.g., `A3F9KBZ2MX`).
2. `VerificationForm::submitForm()` queries `lms_user_certificate` by `field_certificate_number`.
3. If found: checks `field_revoked` boolean.
4. Renders `certificate_verification_result` theme in the same form render array (page rebuild, not redirect).

### Known Gaps

- No JSON REST endpoint (e.g., `GET /api/certificate/verify?code=XYZ`) for third-party badge embedding or programmatic verification.
- No rate limiting on the verify form — brute-force enumeration of certificate codes is possible.
- No CAPTCHA on the public form.

---

## Endpoint: GET `/workspace/certificate-templates/{id}/preview`

**Route:** `lms_certificate.admin_template.preview`
**Module:** `lms_certificate`
**Controller:** `CertificateAdminController::templatePreview`
**Authentication:** `_permission: administer lms_course`; `_admin_route: TRUE`
**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lms_certificate_template` | entity:lms_certificate_template | Template entity (upcasted) |

### Response

**Success (200):** Inline PDF binary response (`Content-Type: application/pdf`, `Content-Disposition: inline`).

**Logic (as-built):**
1. Calls `CertificateGenerator::preview(CertificateTemplate)`.
2. Generator: renders `certificate_pdf` Twig theme with mock user/course data → passes HTML to mPDF → returns raw PDF binary string (not saved to disk).
3. Controller returns binary as HTTP response inline.

**Dependency:** Requires `mpdf/mpdf` Composer package. If mPDF missing, generator logs error and returns NULL → controller returns 500.

---

## Endpoint: GET `/workspace/certificates/{id}/regenerate`

**Route:** `lms_certificate.regenerate`
**Module:** `lms_certificate`
**Controller:** `CertificateAdminController::regenerateCertificate`
**Authentication:** `_permission: administer lms_course`; `_admin_route: TRUE`
**Methods:** GET
**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lms_user_certificate` | entity:lms_user_certificate | Issued certificate (upcasted) |

### Response

**Success (200):** Redirect to `lms_certificate.admin_dashboard` with success message.
**Error (403):** If certificate has `field_revoked = TRUE` — regeneration blocked.

### Logic (as-built)

1. Loads `lms_user_certificate` entity.
2. Blocks if `field_revoked = TRUE` (returns error message, redirects away).
3. Loads linked `lms_certificate_template` via `field_template`.
4. Calls `CertificateGenerator::generate()` with existing `field_certificate_number` code.
5. Generator: renders `certificate_pdf` theme → mPDF → saves managed file at `public://certificates/issued/Certificate_{code}.pdf`.
6. Updates `lms_user_certificate.field_pdf_file` with new file entity.
7. Redirects to admin dashboard with success message.

**Known Gap:** No learner-facing PDF download route. Learners can see their certificates listed at `/my-certificates` (HTML page rendered by `UserPortalController::myCertificates`), but there is no route to download the PDF. The PDF file is stored at `public://certificates/issued/` — accessible directly by file URL if known, but no canonical download endpoint is defined.

---

## Endpoint: GET+POST `/admin/lms/certificates/{id}/revoke`

**Route:** `lms_certificate.revoke`
**Module:** `lms_certificate`
**Handler:** `Drupal\lms_certificate\Form\RevokeCertificateForm` (ConfirmFormBase)
**Authentication:** `_permission: revoke any certificate`
**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lms_user_certificate` | entity:lms_user_certificate | Upcasted |

### Request (POST — confirm form)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `revoke_reason` | textarea | No | Reason stored in `field_revoke_reason` |

### Response

**Success (200 → redirect):** Sets `field_revoked = TRUE` and `field_revoke_reason` on entity; redirects to admin list.

---

## Certificate Issuance Flow (event-driven — no HTTP endpoint)

Certificate issuance is internal and triggered by the event system, not by any HTTP request.

```
QuizController::submitQuiz() (web) OR MobileLearningController::submitQuiz() (mobile)
  → GradingStrategyFactory grades answers
  → Score ≥ field_threshold → QuizPassedEvent dispatched
    → CertificateEventListener::onQuizPassed()
      → Traverses Quiz → Lesson (field_quiz) → Chapter (field_lessons) → Course (field_chapters)
        → CertificateManager::checkAndIssueCertificate(user, course, quiz_result)
          → Check: existing lms_user_certificate for uid+course? (no double-issue)
          → Find template: course field_certificate_template || first active lms_certificate_template
          → Generate 10-char code: strtoupper(substr(md5(uniqid()), 0, 10))
          → CertificateGenerator::generate(template, user, course, code)
            → Render certificate_pdf Twig theme
            → mPDF: A4 + field_orientation, saves public://certificates/issued/Certificate_{code}.pdf
          → Create and save lms_user_certificate entity
```

**Event subscription is conditional:** `CertificateEventListener` only registers subscription if `QuizPassedEvent` class exists (graceful module dependency).

**Graceful degradation:** If `mpdf/mpdf` is not installed, `CertificateGenerator::generate()` logs an error and returns NULL. The `lms_user_certificate` entity is NOT created in that case.

---

## PDF Template System

`lms_certificate_template` entity fields that control PDF rendering:

| Field | Type | Options | Effect |
|-------|------|---------|--------|
| `field_design` | list_string | classic, modern, elegant, minimal | Selects CSS preset in `certificate_pdf` template |
| `field_orientation` | list_string | landscape, portrait | Passed to mPDF page format |
| `field_background` | image | — | Background image; path resolved to server real-path by `template_preprocess_certificate_pdf()` |
| `field_logo` | image | — | Logo image |
| `field_signature` | image | — | Signature image |
| `field_seal` | image | — | Seal/stamp image |
| `field_body` | text_long | — | Body text with tokens: `[student_name]`, `[course_name]`, `[issue_date]` |
| `field_layout` | string_long | HTML/Twig | Raw Twig template string — full layout override |
| `field_color_scheme` | string | — | Color scheme identifier used in template |

**`template_preprocess_certificate_pdf()`:** Resolves all image field URIs to server real-paths so mPDF can read them from disk (mPDF cannot follow Drupal stream wrappers).

---

## Certificate Entity Fields Reference

`lms_user_certificate` fields relevant to API consumers:

| Field | Type | Notes |
|-------|------|-------|
| `field_certificate_number` | string (max 32) | Public verification code — unique |
| `field_pdf_file` | file (pdf) | `public://certificates/issued/Certificate_{code}.pdf` |
| `field_issued_at` | created timestamp | Auto-set on creation |
| `field_expiry_date` | datetime (date-only) | Added by `lms_certificate_update_10001`; stored as datetime field but labeled date-only |
| `field_revoked` | boolean | FALSE by default |
| `field_revoke_reason` | string (max 255) | Set by `RevokeCertificateForm` |
| `field_template` | entity_reference | → `lms_certificate_template` |
| `field_quiz_result` | entity_reference | → `lms_quiz_result` |
| `field_course` | entity_reference | → `lms_course` |

**Known bug:** `field_expiry_date` is defined as `datetime` type but the install code and the DOC-BRIEF note that the underlying column is `varchar` not `int` — this is a schema mismatch risk for date comparisons.

---

## Permissions Reference

| Permission | Title | Used By |
|-----------|-------|---------|
| `manage certificate templates` | Manage certificate templates | Template CRUD |
| `issue any certificate` | Issue any certificate | Manual issuance (no route currently) |
| `issue own course certificate` | Issue own course certificate | (no route currently) |
| `revoke any certificate` | Revoke any certificate | `lms_certificate.revoke` route |
| `view any certificate` | View any certificate | Access control handler |

---

## API Route Summary

| Method | Path | Route Name | Auth | Response | Status |
|--------|------|-----------|------|----------|--------|
| GET+POST | `/certificate/verify` | `lms_certificate.verify` | public | HTML | as-built |
| GET | `/workspace/certificate-templates/{id}/preview` | `lms_certificate.admin_template.preview` | administer lms_course | PDF inline | as-built |
| GET | `/workspace/certificates/{id}/regenerate` | `lms_certificate.regenerate` | administer lms_course | redirect | as-built |
| GET+POST | `/admin/lms/certificates/{id}/revoke` | `lms_certificate.revoke` | revoke any certificate | HTML/redirect | as-built |

---

## Known Issues

| Ref | Severity | Description |
|-----|----------|-------------|
| No learner PDF download route | High | No `GET /certificates/{id}/download` endpoint. PDF files are at `public://` but no canonical learner download URL is defined. |
| No JSON verify endpoint | Medium | `/certificate/verify` is HTML form only. Third-party badge verification or API consumers cannot verify programmatically. |
| No rate limit on verify form | Medium | Brute-force enumeration of 10-char alphanumeric codes is possible. |
| `field_expiry_date` type mismatch | Medium | Declared as `datetime` field but underlying storage is varchar — date comparisons may behave incorrectly. |
| No manual admin grant route | Low | `issue any certificate` permission exists but no route to grant a certificate manually (only event-driven issuance). |
| mPDF soft dependency | Low | No `mpdf/mpdf` in `lms_certificate.info.yml` dependencies — if package is missing, issuance fails silently (logs error, returns NULL, no certificate entity created). |
