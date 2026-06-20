---
module: lms_certificate
service: certificate
route-groups: [Manage, Admin, Public]
status: DONE
documentation-mode: as-built
---

# C05 — Module: LMS Certificate

**Directory:** `web/modules/custom/lms_certificate/`
**Responsibility:** Manages certificate templates and auto-issues PDF certificates to learners when they pass a quiz, via an event subscriber on `QuizPassedEvent`.

---

## Route Bindings

| Route Group | Prefix | Controller / Form | Paths |
|---|---|---|---|
| Public | `/certificate` | `VerificationForm` | `/certificate/verify` |
| Manage (Workspace) | `/workspace/certificates` | `CertificateAdminController` | dashboard, templates CRUD, regenerate |
| Admin | `/admin/lms/certificates` | `RevokeCertificateForm` | `/{lms_user_certificate}/revoke` |

All `/workspace/*` routes carry `_admin_route: TRUE`.

---

## Public Interface

| Export | Type | Description |
|---|---|---|
| `lms_certificate.generator` | Service (`CertificateGenerator`) | Renders `certificate_pdf` theme to HTML and produces a PDF file via mPDF; returns a managed `FileInterface` or NULL if mPDF is absent |
| `lms_certificate.manager` | Service (`CertificateManager`) | Orchestrates eligibility check, code generation, generator call, and `lms_user_certificate` entity creation |
| `lms_certificate.event_listener` | EventSubscriber (`CertificateEventListener`) | Subscribes to `lms_quiz.passed`; traverses Quiz → Lesson → Chapter → Course then delegates to CertificateManager |

---

## Internal Structure

```
web/modules/custom/lms_certificate/
+-- lms_certificate.info.yml        # deps: lms_base, lms_quiz; lifecycle: stable
+-- lms_certificate.routing.yml     # 8 routes (see Route Bindings)
+-- lms_certificate.services.yml    # 3 services (generator, manager, event_listener)
+-- lms_certificate.permissions.yml # 5 permissions (see Permissions)
+-- lms_certificate.module          # hook_theme, hook_entity_operation, template_preprocess_certificate_pdf
+-- lms_certificate.install         # update_10001 (add fields to template + user_cert), update_10002 (add field_seal)
+-- src/
|   +-- Entity/
|   |   +-- CertificateTemplate.php    # lms_certificate_template content entity
|   |   +-- UserCertificate.php        # lms_user_certificate content entity (EntityOwnerTrait)
|   +-- Service/
|   |   +-- CertificateGenerator.php   # Render + mPDF → managed file
|   |   +-- CertificateManager.php     # Orchestration: check duplicate, find template, issue
|   +-- EventSubscriber/
|   |   +-- CertificateEventListener.php  # event_subscriber tag; conditional on QuizPassedEvent class_exists
|   +-- Controller/
|   |   +-- CertificateAdminController.php  # adminDashboard, adminTemplates, templateAddForm/EditForm/Preview, regenerateCertificate
|   +-- Form/
|   |   +-- RevokeCertificateForm.php        # ConfirmFormBase; sets field_revoked + field_revoke_reason
|   |   +-- VerificationForm.php             # FormBase; lookup by field_certificate_number; rebuilds on submit
|   |   +-- CertificateTemplateForm.php      # (standard ContentEntityForm via entity handler, no custom class)
|   +-- ListBuilder/
|       +-- CertificateTemplateListBuilder.php
+-- templates/
|   +-- certificate-pdf.html.twig
|   +-- certificate-verification-result.html.twig
|   +-- lms-admin-certificates.html.twig
|   +-- lms-admin-certificate-templates.html.twig
+-- config/
    +-- install/   # default config shipped with module
    +-- schema/    # config schema
```

---

## Entities Owned

### `lms_certificate_template` (ContentEntity)

Base table: `lms_certificate_template`

| Field | Type | Notes |
|---|---|---|
| id | serial | entity key |
| uuid | uuid | entity key |
| name | string (255) | required; label key |
| field_body | text_long | tokens: `[student_name]`, `[course_name]`, `[issue_date]` |
| field_design | list_string | classic / modern / elegant / minimal; default=classic |
| field_background | image | dir: `certificates/backgrounds` |
| field_signature | image | dir: `certificates/signatures` |
| field_logo | image | dir: `certificates/logos` |
| field_seal | image | dir: `certificates/seals` (added by update_10002) |
| field_orientation | list_string | landscape / portrait; default=landscape |
| field_layout | string_long | Raw HTML/Twig structure used as inline Twig template |
| field_color_scheme | string | |
| status | boolean | active/inactive; default=TRUE |

Handlers: `CertificateTemplateListBuilder`, `EntityViewBuilder`, `EntityViewsData`, `CertificateTemplateForm`, `ContentEntityDeleteForm`, `AdminHtmlRouteProvider`, `EntityAccessControlHandler`.
Links: canonical=`/manage/certificates/{id}`, add-form, edit-form, delete-form, collection.

### `lms_user_certificate` (ContentEntity — EntityOwnerInterface)

Base table: `lms_user_certificate`

| Field | Type | Notes |
|---|---|---|
| id | serial | entity key |
| uuid | uuid | entity key |
| uid | entity_reference (user) | owner (EntityOwnerTrait) |
| field_template | entity_reference | required; target: lms_certificate_template |
| field_quiz_result | entity_reference | target: lms_quiz_result |
| field_course | entity_reference | target: lms_course |
| field_certificate_number | string (32) | unique 10-char uppercase code (MD5 substring) |
| field_pdf_file | file | dir: `certificates/issued`, ext: pdf |
| field_issued_at | created | timestamp |
| field_expiry_date | datetime | date-only; **RISK: stored as `datetime` field — see known issues** |
| field_revoked | boolean | default=FALSE; added by update_10001 |
| field_revoke_reason | string (255) | added by update_10001 |

No form handler, no route_provider, no links defined — managed entirely programmatically.

---

## Permissions

| Machine Name | Title |
|---|---|
| `manage certificate templates` | Manage certificate templates |
| `issue any certificate` | Issue any certificate |
| `issue own course certificate` | Issue own course certificate |
| `revoke any certificate` | Revoke any certificate |
| `view any certificate` | View any certificate |

---

## Key Flows

### Auto-issuance (as-built)

1. `lms_quiz` dispatches `QuizPassedEvent` when a learner passes.
2. `CertificateEventListener::onQuizPassed()` extracts the Quiz entity from the event.
3. Traverses: Quiz → `field_quiz` (Lesson) → `field_lessons` (Chapter) → `field_chapters` (Course).
4. Calls `CertificateManager::checkAndIssueCertificate(user, course, quiz_result)`.
5. Manager checks for existing certificate (no double-issue), resolves template via `field_certificate_template` on the course or falls back to first active template.
6. Generates 10-char uppercase code from MD5. Calls `CertificateGenerator::generate()`.
7. Generator renders `certificate_pdf` theme → HTML, instantiates `\Mpdf\Mpdf` with A4 + orientation, writes `public://certificates/issued/Certificate_{code}.pdf`, returns managed `FileInterface`.
8. Manager saves `lms_user_certificate` entity.

**Note:** Entire flow is synchronous within the event subscriber — no queue buffering.

### PDF Preview (admin)

`CertificateAdminController::templatePreview()` → `CertificateGenerator::preview()`: same flow but uses mock user/course, returns raw PDF binary (not saved to disk), served as inline PDF response.

### Verification (public)

`/certificate/verify` form: user submits code → `VerificationForm` looks up `lms_user_certificate` by `field_certificate_number` → renders `certificate_verification_result` theme in a form rebuild; shows revoked status if applicable.

---

## Hook Implementations

| Hook | What it does |
|---|---|
| `hook_theme()` | Registers `certificate_pdf`, `certificate_verification_result`, `lms_admin_certificates`, `lms_admin_certificate_templates` |
| `hook_entity_operation()` | Adds "Revoke" operation to `lms_user_certificate` list for users with `revoke any certificate` |
| `template_preprocess_certificate_pdf()` | Resolves image field URIs (background, logo, signature, seal) to server real-paths so mPDF can read from disk |

---

## Dependencies

| Dependency | Type | Why |
|---|---|---|
| `lms_base` | Internal | Base entity traits, shared services |
| `lms_quiz` | Internal | Provides `QuizPassedEvent`; event subscriber conditionally registers on `class_exists` check |
| `mpdf/mpdf` | External (Composer) | PDF generation; code gracefully degrades (logs error, returns NULL) if library absent |

---

## Templates

| Template | Purpose |
|---|---|
| `certificate-pdf.html.twig` | PDF layout; receives `template`, `user`, `course`, `code`, `date`; image paths pre-processed by preprocess hook |
| `certificate-verification-result.html.twig` | Public verification result display |
| `lms-admin-certificates.html.twig` | Admin dashboard: stats (total, revoked, recent 7d), list of 50 most recent |
| `lms-admin-certificate-templates.html.twig` | Template list with edit/preview links |

---

## Known Issues / Debt

| Ref | Severity | Description |
|---|---|---|
| RISK | Medium | `field_expiry_date` on `lms_user_certificate` is stored as a `datetime` field — original intent was a plain date/int; check if date-only range comparisons are affected |
| DEBT | Low | Issuance is synchronous in the event subscriber; heavy PDF generation blocks the request that triggered the quiz pass. Consider queuing. |
| DEBT | Low | `template_preprocess_certificate_pdf()` uses static `\Drupal::service()` instead of injected services. |
| DEBT | Low | `CertificateGenerator::generate()` calls `$file_system->realpath()` for image paths — fails if files are on S3/CDN rather than local disk. |
| SECURITY | Critical | API keys and secrets (if any) stored in committed config YAML — «secret in config — see security risk». |
