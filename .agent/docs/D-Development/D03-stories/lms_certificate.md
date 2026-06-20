# D03 — Story Spec: lms_certificate

**Story ID:** lms_certificate
**Story type:** module
**Title:** Certificate Templates, Auto-Issuance on Quiz Pass, and PDF Generation
**Primary source:** `C-Design/C05-modules/lms_certificate.md`
**Owning module(s):** lms_certificate
**Service(s):** lms_certificate.generator (CertificateGenerator), lms_certificate.manager (CertificateManager), lms_certificate.event_listener (CertificateEventListener)
**Epic:** E-06 (Certificate & Badge System)
**Contributes to Feature(s):** F-009 (Certification), F-010 (Badges)
**Design source(s):** `../C05-modules/lms_certificate.md`
**Round:** N/A
**Driver:** SA directive
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_certificate`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 3
**Splitting strategy:**
- S1: Entities (CertificateTemplate, UserCertificate), permissions, routes, services.yml, list builder
- S2: CertificateGenerator (mPDF), CertificateManager (issuance logic), CertificateEventListener (QuizPassedEvent), preprocess hook
- S3: CertificateAdminController, RevokeCertificateForm, VerificationForm, templates, install updates (update_10001, update_10002)

## Scope (from primary source)

Provides the full certificate lifecycle in EverLMS: template management, auto-issuance on quiz pass, PDF generation via mPDF, public verification, and admin revocation/regeneration. When a quiz is passed (`QuizPassedEvent`), `CertificateEventListener` traverses Quiz→Lesson→Chapter→Course and calls `CertificateManager::checkAndIssueCertificate()`. The manager selects the best-matching template (course-specific or first active), generates a unique 10-char code, calls `CertificateGenerator::generate()` to produce a PDF via mPDF, and saves a `lms_user_certificate` entity.

**Known issue (as-built):** `lms_user_certificate.field_expiry_date` is stored as `datetime` (date-only), but the field definition was originally created as `varchar` before `update_10001` changed it to datetime. There is a risk of type mismatch on installations that were not cleanly updated. See [E02 issues: field_expiry_date varchar vs datetime].

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_certificate.md` | Owning module design |
| `../../B-Requirements/B03-features/06-certification-badges.md` | Certification feature requirements |
| `../../B-Requirements/B05-acceptance-criteria/` | User-facing AC |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Admin can create/edit certificate templates with background, logo, seal, signature, orientation | B03-features/06-certification-badges | Acceptance |
| Certificate auto-issued on quiz pass | B03-features/06-certification-badges | Acceptance |
| No duplicate certificate issued for same user+course | B03-features/06-certification-badges | Acceptance |
| PDF generated via mPDF with A4 layout and template orientation | B03-features/06-certification-badges | Acceptance |
| Public certificate verification by unique code | B03-features/06-certification-badges | Acceptance |
| Admin can revoke certificates with a reason | B03-features/06-certification-badges | Acceptance |
| Admin can regenerate certificates | B03-features/06-certification-badges | Acceptance |
| Template preview via inline PDF | B03-features/06-certification-badges | Acceptance |
| Graceful degradation if mPDF not installed (log error, return NULL) | B03-features/06-certification-badges | NFR |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_certificate/lms_certificate.info.yml` | Create | Module declaration | — | S1 |
| `web/modules/custom/lms_certificate/lms_certificate.permissions.yml` | Create | 5 permissions (manage templates, issue, revoke, view any) | — | S1 |
| `web/modules/custom/lms_certificate/lms_certificate.routing.yml` | Create | verify, revoke, regenerate, admin dashboard + templates routes | — | S1 |
| `web/modules/custom/lms_certificate/lms_certificate.services.yml` | Create | generator, manager, event_listener (tagged event_subscriber) | — | S1 |
| `web/modules/custom/lms_certificate/lms_certificate.module` | Create | hook_theme, hook_entity_operation, template_preprocess_certificate_pdf | — | S2 |
| `web/modules/custom/lms_certificate/lms_certificate.install` | Create | update_10001 (logo/orientation/layout/color_scheme/expiry_date/revoked), update_10002 (seal) | — | S3 |
| `web/modules/custom/lms_certificate/src/Entity/CertificateTemplate.php` | Create | CertificateTemplate entity | — | S1 |
| `web/modules/custom/lms_certificate/src/Entity/UserCertificate.php` | Create | UserCertificate entity (EntityOwnerInterface) | — | S1 |
| `web/modules/custom/lms_certificate/src/CertificateTemplateListBuilder.php` | Create | Admin template list builder | — | S1 |
| `web/modules/custom/lms_certificate/src/Service/CertificateGenerator.php` | Create | mPDF render + managed file save | — | S2 |
| `web/modules/custom/lms_certificate/src/Service/CertificateManager.php` | Create | Duplicate check, template selection, code generation, entity save | — | S2 |
| `web/modules/custom/lms_certificate/src/EventSubscriber/CertificateEventListener.php` | Create | QuizPassedEvent subscriber; course traversal | — | S2 |
| `web/modules/custom/lms_certificate/src/Controller/CertificateAdminController.php` | Create | adminDashboard, adminTemplates, templateAdd/Edit/Preview, regenerate | — | S3 |
| `web/modules/custom/lms_certificate/src/Form/RevokeCertificateForm.php` | Create | ConfirmForm with revoke_reason | — | S3 |
| `web/modules/custom/lms_certificate/src/Form/VerificationForm.php` | Create | Public certificate code lookup | — | S3 |
| `web/modules/custom/lms_certificate/src/Form/CertificateTemplateForm.php` | Create | Standard ContentEntityForm via handler | — | S1 |
| `web/modules/custom/lms_certificate/templates/certificate-pdf.html.twig` | Create | PDF layout (student_name, course_name, issue_date tokens) | — | S3 |
| `web/modules/custom/lms_certificate/templates/certificate-verification-result.html.twig` | Create | Public verification result | — | S3 |
| `web/modules/custom/lms_certificate/templates/lms-admin-certificates.html.twig` | Create | Admin dashboard with stats | — | S3 |
| `web/modules/custom/lms_certificate/templates/lms-admin-certificate-templates.html.twig` | Create | Admin template list | — | S3 |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Intra-module | info.yml |
| lms_quiz (QuizPassedEvent) | Intra-module | info.yml; CertificateEventListener subscriber |
| mpdf/mpdf (Composer) | External | CertificateGenerator::generate() |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| EventSubscriber with conditional class_exists guard | CertificateEventListener | As-built |
| EntityOwnerTrait | UserCertificate | As-built |
| Graceful degradation on missing Composer dep | CertificateGenerator | As-built (NULL return if !class_exists Mpdf) |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Revision / moderation | Translation | Cache invalidation | Smoke evidence |
|---|---|---|---|---|---|---|---|---|
| `/workspace/certificate-templates` | Template list + preview | lms_certificate_template entity | `/workspace/certificate-templates/add` | administer lms_course | None | None | Default entity | As-built |
| `/workspace/certificates` | Issued certificate list + stats | lms_user_certificate entity | (programmatic issuance) | administer lms_course | None | None | Default entity | As-built |
| `/certificate/verify` | Public code lookup | lms_user_certificate by field_certificate_number | Same page (form rebuild) | _access: TRUE | None | None | Uncached form | As-built |

**Hardcoded content exceptions:** `template_preprocess_certificate_pdf()` resolves file URIs to real server paths — this is a mPDF requirement, not hardcoded content.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-CERT-001 | Integration | QuizPassedEvent triggers certificate issuance for passing student | Auto-issue AC |
| T-CERT-002 | Integration | Second quiz pass does not create duplicate certificate for same user+course | De-dup AC |
| T-CERT-003 | Integration | CertificateGenerator::generate() produces a managed PDF file at public://certificates/issued/ | PDF AC |
| T-CERT-004 | Integration | CertificateGenerator gracefully returns NULL if mPDF missing (no exception) | NFR degradation AC |
| T-CERT-005 | Integration | Public /certificate/verify shows certificate details for valid code | Verification AC |
| T-CERT-006 | Integration | /certificate/verify shows revoked status for revoked certificate | Revoke AC |
| T-CERT-007 | Unit | RevokeCertificateForm sets field_revoked=1 and field_revoke_reason on submit | Revoke AC |
| T-CERT-008 | Integration | Regenerate after revoke is blocked | Regenerate gate AC |
| T-CERT-009 | Unit | field_expiry_date is datetime type on clean install (update_10001 applied) | Schema type AC |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Module installs; routes resolve; PDF generated | — | — |
| G2 Lint | PASS | No blocking violations | — | — |
| G3 Tests | PASS | Auto-issuance, PDF, and verification all smoke-tested | — | — |
| G4 Adversarial | PASS | field_expiry_date type mismatch logged as E02; no critical blockers | — | — |

## Notes

- **field_expiry_date type risk (E02):** Originally created as varchar; `update_10001` changed to datetime. On installations where `update_10001` was not applied cleanly, the field may be varchar with incompatible data. Recommendation: add a data migration step in a future update hook.
- **mPDF dependency:** `mpdf/mpdf` must be installed via Composer. `CertificateGenerator::generate()` gracefully returns NULL with a logged error if the class is missing. No exception propagates to the user.
- **Google service account RSA key:** If the certificate template uses Google Slides for design, the `lms_ai` module's Google service account key is in committed config — `«secret in config — see security risk»`.
- **Event subscriber guard:** `CertificateEventListener` wraps its subscription in `if (class_exists(QuizPassedEvent::class))` — safe decoupling from lms_quiz if that module is disabled.
- **field_layout raw Twig:** `lms_certificate_template.field_layout` stores a full HTML/Twig structure input by the admin. This is rendered server-side by mPDF. No Twig sandbox is applied — admin-only field but represents an XSS surface if editor roles gain access.
