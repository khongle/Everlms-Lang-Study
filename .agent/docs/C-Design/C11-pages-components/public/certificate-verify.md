---
page: certificate-verify
mode: as-built
owner_module: lms_certificate
route_group: public
adequacy: NOT_RUN
adequacy_run: null
---

# C11 — Page: Certificate Verification

**Canonical URL:** `/certificate/verify`
**Route name:** `lms_certificate.verify`
**Solution:** Drupal FormBase (`Form\VerificationForm`) served as a page. On submit, queries `lms_user_certificate` by `field_certificate_number`; renders `certificate_verification_result` theme in the same form rebuild (no redirect). GET shows empty form; POST shows result inline.
**Owner module:** `lms_certificate` (`web/modules/custom/lms_certificate/src/Form/VerificationForm.php`)
**Primary source:** `lms_user_certificate` entity (looked up by `field_certificate_number`)
**Editable source:** `lms_user_certificate` — created programmatically on `lms_quiz.passed` event; not directly editor-managed content. Template fields (background, logo, signature, seal) managed via `lms_certificate_template` entity
**Editor role:** N/A (read-only verification page; certificates are auto-issued)
**Editor management UI:** `/workspace/certificates` (admin list) · `/workspace/certificate-templates` (template management)
**Revision / moderation:** Not revisionable
**Forms:** `VerificationForm` (Drupal FormBase, GET/POST)
**Theme template:** `lms_certificate/templates/certificate-verification-result.html.twig` (result section) · page rendered via standard `page.html.twig`
**Access:** Public (anonymous) — `_access: TRUE`
**Cache:** No-cache (form page, POST handler, per-user result)
**Translation:** Monolingual EN
**Related entities:** [`lms_user_certificate`](../../C04-entities/), [`lms_certificate_template`](../../C04-entities/)
**Prototype reference:** [`../../C10-prototypes/html/public/certificate-verify.html`](../../C10-prototypes/html/public/certificate-verify.html)
**Notes:** Certificate number format is 10-char uppercase MD5 substring (e.g. `EV-2026-A8F3K2` in prototype). `VerificationForm` looks up `lms_user_certificate` where `field_certificate_number = {input}`; renders `certificate_verification_result` theme with found entity data including `field_revoked` status. Revoked certificates are shown with revoke reason. `field_expiry_date` is stored as varchar (not int) — known bug (see E02 issues). The `Download PDF` / `Share certificate` CTAs in prototype are target-state; PDF download requires separate route not yet confirmed on the public surface.

---

## Components (visual order, top-to-bottom)

> Global chrome (header / primary nav / footer) is defined in [`../_common.md`](../_common.md) and not repeated here.

### 1. Page Hero

- **Scope:** Page-specific
- **Implementation mechanism:** Static section in page template or `certificate-verification-result.html.twig`
- **Plugin / Module:** `lms_certificate`
- **Source content type / Entity:** Static template
- **Backed by fields / config keys:** Static strings in template ("Certificate Verification", subtitle)
- **Editable source:** Static — requires template edit
- **Editor role / permission:** Developer only
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** Interface translation (if i18n enabled)
- **Edit smoke:** N/A
- **View mode / Projection:** Marketing hero (centred text)
- **Region / Placement:** Top of page
- **Notes:** Prototype shows full-width hero with title "Certificate Verification" and subtext. Currently static in template
- **How to implement (EN):** Render static hero section at top of verification page template with page title and subtitle
- **How to implement (VI):** Render section hero tĩnh với tiêu đề trang và phụ đề ở đầu template trang xác minh
- **Solution Architect Request:** —

### 2. Certificate Code Input Form

- **Scope:** Page-specific
- **Implementation mechanism:** Drupal FormBase (`VerificationForm::buildForm()`); renders `form_element` for certificate_code text input + submit button
- **Plugin / Module:** `lms_certificate` — `Form\VerificationForm`
- **Source content type / Entity:** User input only (no entity pre-populated)
- **Backed by fields / config keys:** Form field: `certificate_code` (textfield, required, trimmed, uppercase coercion in handler)
- **Editable source:** N/A (user input form)
- **Editor role / permission:** N/A (public)
- **Editor management UI:** N/A
- **Revision / moderation:** N/A
- **Translation source:** Interface translation
- **Edit smoke:** Submit valid code → result renders below; submit invalid code → "not found" message
- **View mode / Projection:** Form card (centred on page per prototype)
- **Region / Placement:** Centre of page, below hero
- **Notes:** Prototype shows monospace input field (`EV-2026-A8F3K2` placeholder), uppercase styling, large "Verify Certificate" submit button. Form rebuilds on submit (no redirect); result displayed in same page below the form
- **How to implement (EN):** Implement as Drupal `FormBase::buildForm()` with one textfield (certificate_code) + submit; `submitForm()` queries lms_user_certificate by field_certificate_number; rebuild form and pass result to render array
- **How to implement (VI):** Triển khai dạng `FormBase::buildForm()` với một trường text (certificate_code) + nút submit; `submitForm()` truy vấn lms_user_certificate theo field_certificate_number; rebuild form và truyền kết quả vào render array
- **Solution Architect Request:** —

### 3. Verification Result Panel

- **Scope:** Page-specific
- **Implementation mechanism:** `certificate_verification_result` theme hook; template `certificate-verification-result.html.twig`; rendered inside form rebuild response
- **Plugin / Module:** `lms_certificate` — `Form\VerificationForm::submitForm()` + theme
- **Source content type / Entity:** `lms_user_certificate` (found by certificate_number)
- **Backed by fields / config keys:** `field_certificate_number`, `uid` (owner → user name), `field_course` (course name), `field_issued_at` (timestamp), `field_expiry_date` (varchar — known bug), `field_revoked`, `field_revoke_reason`, `field_template` (certificate template)
- **Editable source:** `lms_user_certificate` — auto-issued; admin can revoke at `/admin/lms/certificates/{id}/revoke`
- **Editor role / permission:** `administrator` / `revoke any certificate`
- **Editor management UI:** `/workspace/certificates` (admin list) · `/admin/lms/certificates/{id}/revoke`
- **Revision / moderation:** Not revisionable
- **Translation source:** N/A (display only)
- **Edit smoke:** Verify valid certificate code → result panel shows learner name, course, issue date, certificate number
- **View mode / Projection:** Verification result card (green = valid, red = revoked / not found)
- **Region / Placement:** Below input form, visible after submit
- **Notes:** Three states: (1) valid — shows learner name, course, issue date, certificate number with "Download PDF" and "Share" CTAs; (2) revoked — shows revoked status and reason; (3) not found — shows error message. `field_expiry_date` is varchar (not int) — expiry display may have formatting issues (see E02 issues). PDF download CTA in prototype is target-state — no public PDF download route confirmed on `/certificate/verify`
- **How to implement (EN):** Template receives `certificate`, `user`, `course` variables; check `field_revoked`; render appropriate state; use preprocess hook `template_preprocess_certificate_pdf` pattern for image path resolution if needed
- **How to implement (VI):** Template nhận biến `certificate`, `user`, `course`; kiểm tra `field_revoked`; render trạng thái tương ứng; dùng preprocess hook để resolve đường dẫn ảnh nếu cần
- **Solution Architect Request:** —

### 4. Certificate FAQ Strip

- **Scope:** Page-specific
- **Implementation mechanism:** Static accordion in prototype; possibly from `lms_faq` (global scope, faq_category = certificates)
- **Plugin / Module:** `lms_certificate` or `lms_course`
- **Source content type / Entity:** `lms_faq` (global scope) or static template
- **Backed by fields / config keys:** `question`, `field_answer`
- **Editable source:** `lms_faq` entity
- **Editor role / permission:** `manager` / `administer lms_course`
- **Editor management UI:** `/workspace/content/faqs`
- **Revision / moderation:** Not revisionable
- **Translation source:** N/A
- **Edit smoke:** N/A
- **View mode / Projection:** Accordion
- **Region / Placement:** Bottom of page (per prototype)
- **Notes:** Prototype shows "Common questions about certificates" accordion. Not confirmed in `VerificationForm` implementation. May be absent or target-state
- **How to implement (EN):** Load lms_faq where field_category=certificates/verify; render as accordion below result panel
- **How to implement (VI):** Tải lms_faq với field_category=certificates; render dạng accordion bên dưới panel kết quả
- **Solution Architect Request:** —

---

## Visible Content Inventory

| Component | Visible content | Source entity/config/View | Fields / keys | Editor UI | Role / permission | Revision/moderation | Translation | Cache tags/contexts | Smoke evidence |
|---|---|---|---|---|---|---|---|---|---|
| Page Hero | Title "Certificate Verification", subtitle | Static template | — | N/A (template) | Developer | N/A | Interface translation | N/A | Template edit → rebuild |
| Code Input Form | Certificate code field, submit button, validation messages | User input / Drupal Form API | certificate_code (user input) | N/A | N/A (public) | N/A | Interface translation | No-cache | Submit valid code → result |
| Verification Result Panel | Learner name, course, issue date, certificate number, validity status, revoke reason | `lms_user_certificate` | field_certificate_number, uid, field_course, field_issued_at, field_expiry_date, field_revoked, field_revoke_reason | `/workspace/certificates` / `/admin/lms/certificates/{id}/revoke` | administrator / revoke any certificate | Not revisionable | EN | No-cache | Submit valid code → panel shows |
| Certificate FAQ Strip | Q+A pairs | `lms_faq` (global) or static | question, field_answer | `/workspace/content/faqs` | manager | Not revisionable | EN | `lms_faq_list` | Add FAQ → appears |

## Hardcoded Content Exceptions

| Visible value | Why not editor-managed | Exception authority | Expiry / revisit |
|---|---|---|---|
| Page title "Certificate Verification" | Static in template | `lms_certificate` module template | Move to theme settings or block if needs editing |
| "Download PDF" / "Share" CTAs in result | Target-state UX; no public download route yet | Prototype design | Implement when public PDF route added |

## Implementation status

| Component | Status | Story | Notes |
|---|---|---|---|
| Page Hero | DONE | — | Static section in template |
| Code Input Form | DONE | — | VerificationForm::buildForm() implemented |
| Verification Result Panel | DONE | — | VerificationForm::submitForm() + certificate_verification_result theme; field_expiry_date varchar bug (see E02) |
| Download PDF CTA | PLANNED | — | No public download route confirmed; admin can regenerate at /workspace/certificates/{id}/regenerate |
| Certificate FAQ Strip | «unverified» | — | Not confirmed in VerificationForm implementation |
