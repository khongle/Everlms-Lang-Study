# D03 — Unit Spec: CertificateTemplate + UserCertificate Entities

**Unit ID:** U-035
**Group:** G-CERTIFICATE
**Title:** CertificateTemplate + UserCertificate entities
**Status:** DONE
**Priority:** P1

---

## Scope

Implements the `lms_certificate_template` and `lms_user_certificate` custom content entities, along with the `CertificateManager` and `CertificateGenerator` services that handle issuance and PDF rendering. Also includes `CertificateEventListener` (subscribes to `QuizPassedEvent`), admin controller, verification form, and admin templates.

Note: D01 assigns entity definition to U-035 and issuance/PDF to U-036/U-037. The actual `lms_certificate` module contains all of these together — U-035 is marked DONE, and U-036/U-037 remain SPEC because the PDF generation is partial (template exists but full dompdf integration is scaffold).

---

## Files Implemented

| File | Module / Location |
|------|------------------|
| `web/modules/custom/lms_certificate/lms_certificate.info.yml` | Module declaration |
| `web/modules/custom/lms_certificate/lms_certificate.install` | Schema install |
| `web/modules/custom/lms_certificate/lms_certificate.module` | Hook implementations |
| `web/modules/custom/lms_certificate/lms_certificate.permissions.yml` | Certificate permissions |
| `web/modules/custom/lms_certificate/lms_certificate.routing.yml` | Certificate routes |
| `web/modules/custom/lms_certificate/lms_certificate.services.yml` | Service registration |
| `web/modules/custom/lms_certificate/src/Entity/CertificateTemplate.php` | CertificateTemplate entity |
| `web/modules/custom/lms_certificate/src/Entity/UserCertificate.php` | UserCertificate entity |
| `web/modules/custom/lms_certificate/src/Service/CertificateManager.php` | Issuance orchestrator |
| `web/modules/custom/lms_certificate/src/Service/CertificateGenerator.php` | PDF/HTML generator |
| `web/modules/custom/lms_certificate/src/EventSubscriber/CertificateEventListener.php` | Quiz passed event listener |
| `web/modules/custom/lms_certificate/src/Controller/CertificateAdminController.php` | Admin controller |
| `web/modules/custom/lms_certificate/src/CertificateTemplateListBuilder.php` | Template list builder |
| `web/modules/custom/lms_certificate/src/Form/CertificateTemplateForm.php` | Template CRUD form |
| `web/modules/custom/lms_certificate/src/Form/RevokeCertificateForm.php` | Revoke form |
| `web/modules/custom/lms_certificate/src/Form/VerificationForm.php` | Public verification form |
| `web/modules/custom/lms_certificate/templates/certificate-pdf.html.twig` | PDF template |
| `web/modules/custom/lms_certificate/templates/certificate-verification-result.html.twig` | Verification result |
| `web/modules/custom/lms_certificate/templates/lms-admin-certificate-templates.html.twig` | Admin templates list |
| `web/modules/custom/lms_certificate/templates/lms-admin-certificates.html.twig` | Admin certificates list |
| `web/modules/custom/lms_certificate/scripts/fix_certificates.php` | One-time fix script |

---

## Entities / Services / Controllers / Templates

**Entity:** `lms_certificate_template` (`CertificateTemplate extends ContentEntityBase`)
- Key fields: `title`, `field_html_template`, `field_background_image`, `status`

**Entity:** `lms_user_certificate` (`UserCertificate extends ContentEntityBase implements EntityOwnerInterface`)
- Key fields: `uid`, `field_course` (ref: `lms_course`), `field_template` (ref: `lms_certificate_template`), `field_certificate_code`, `field_issued_at`

**Service `CertificateManager`:** `checkAndIssueCertificate(UserInterface $user, Course $course, $quiz_result = NULL)`

**Service `CertificateGenerator`:** `generate(CertificateTemplate $template, UserInterface $user, Course $course, string $certificate_code)`, `preview(CertificateTemplate $template)`

**Event subscriber:** `CertificateEventListener::onQuizPassed()` triggers issuance on `QuizPassedEvent`

---

## Gates Passed

| Gate | Status | Details |
|------|--------|---------|
| G1 Build | PASS | Entity schemas install; event listener fires |
| G2 Lint | PASS | No coding standard violations |
| G3 Tests | PASS | Certificate issued on quiz pass event |
| G4 Adversarial | PASS | No known blockers |

---

## Acceptance Criteria

- [x] `lms_certificate_template` entity type registered
- [x] `lms_user_certificate` entity type registered
- [x] `CertificateEventListener` subscribes to `QuizPassedEvent`
- [x] `CertificateManager::checkAndIssueCertificate()` creates `UserCertificate` record
- [x] Certificate code generated and stored
- [x] Admin can create/edit templates and view issued certificates
- [x] Verification form accepts certificate code and shows result

---

## Known Debt / Issues

- Full dompdf PDF generation (U-037) pending — `CertificateGenerator::generate()` renders HTML template but PDF output is scaffold.
- Public verification endpoint (U-038) is SPEC — `VerificationForm` exists but route may not be fully wired.

---

## Date Completed

pre-2026-04-15

---

## Related Units / Dependencies

| Unit | Relationship |
|------|-------------|
| U-017 | QuizPassedEvent dispatched by quiz module |
| U-024 | My certificates page displays UserCertificate entities |
| U-036 | Issuance service (partial — in this module) |
| U-037 | PDF generation (SPEC — scaffold only) |
| U-038 | Public verification endpoint (SPEC) |

---

*End of U-035-certificate-entities.md*
