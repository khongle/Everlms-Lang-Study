# lms_certificate — Certificate Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_certificate` |
| Name | LMS Certificate |
| Layer | **Certificate** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base`, `lms_quiz` |

## Mô tả

Module quản lý chứng chỉ học tập: tạo template, auto-issue khi user pass quiz (qua EventSubscriber lắng nghe `QuizPassedEvent`), generate PDF, verify và revoke. Module có 2 entity, 2 service, 1 EventSubscriber.

---

## Provided Entities

### `lms_certificate_template` (Content Entity)
Template chứng chỉ: name, background image, logo, signature image, seal image, body HTML.

### `lms_user_certificate` (Content Entity)
Chứng chỉ đã cấp: uid, course ref, certificate_code (unique UUID), issued_at, status (active/revoked).

---

## Provided Routes

| Route ID | Path | Controller/Form | Access |
|----------|------|----------------|--------|
| `lms_certificate.verify` | `/certificate/verify` | `VerificationForm` | public |
| `lms_certificate.revoke` | `/admin/lms/certificates/{lms_user_certificate}/revoke` | `RevokeCertificateForm` | `revoke any certificate` |
| `lms_certificate.regenerate` | `/workspace/certificates/{lms_user_certificate}/regenerate` | `CertificateAdminController::regenerateCertificate` | `administer lms_course` |
| `lms_certificate.admin_dashboard` | `/workspace/certificates` | `CertificateAdminController::adminDashboard` | `administer lms_course` |
| `lms_certificate.admin_templates` | `/workspace/certificate-templates` | `CertificateAdminController::adminTemplates` | `administer lms_course` |
| `lms_certificate.admin_template.add` | `/workspace/certificate-templates/add` | `CertificateAdminController::templateAddForm` | `administer lms_course` |
| `lms_certificate.admin_template.edit` | `/workspace/certificate-templates/{lms_certificate_template}/edit` | `CertificateAdminController::templateEditForm` | `administer lms_course` |
| `lms_certificate.admin_template.preview` | `/workspace/certificate-templates/{lms_certificate_template}/preview` | `CertificateAdminController::templatePreview` | `administer lms_course` |

---

## Provided Services

| Service ID | Class | Purpose |
|-----------|-------|---------|
| `lms_certificate.generator` | `CertificateGenerator` | Render template → generate PDF file, inject renderer + file_system |
| `lms_certificate.manager` | `CertificateManager` | Orchestrate certificate issuance: check eligibility, call generator, save entity |
| `lms_certificate.event_listener` | `CertificateEventListener` | EventSubscriber lắng nghe `QuizPassedEvent` → gọi manager |

---

## Provided Plugins

Không có plugin type.

---

## Hook Implementations

| Hook | Purpose |
|------|---------|
| `hook_theme()` | 4 templates: certificate_pdf, certificate_verification_result, lms_admin_certificates, lms_admin_certificate_templates |
| `hook_entity_operation()` | Thêm operation "Revoke" cho `lms_user_certificate` nếu user có `revoke any certificate` permission |
| `template_preprocess_certificate_pdf` | Resolve file URIs (background, logo, signature, seal) thành realpath cho PDF renderer |

---

## Templates & Cache Strategy

| Template | Purpose |
|----------|---------|
| `certificate-pdf` | Render HTML cho PDF generation (không dùng cho web display thông thường) |
| `certificate-verification-result` | Hiển thị kết quả verify certificate |
| `lms-admin-certificates` | Admin dashboard danh sách certificates |
| `lms-admin-certificate-templates` | Admin list templates |

PDF generation dùng `renderer` service + file system — output là file tĩnh lưu ở `public://certificates/`.

---

## Related Units

- **C03** § Certificate Domain — entities CertificateTemplate, UserCertificate
- **C05** `04-certificate-communication.md` — layer overview
- `lms_quiz.md` — event source `QuizPassedEvent`
- `lms_progress.md` — `/my-certificates` route dùng template lms_my_certificates

---

## Known Issues / Debt

- `CertificateGenerator` dùng `$file_system->realpath()` cho images — nếu files ở S3/CDN sẽ fail.
- Không có queue: issuance xảy ra synchronously trong EventSubscriber — slow nếu PDF generation nặng.
- Certificate code là UUID đơn giản, không có checksum layer cho verification.
- `preprocess_certificate_pdf` dùng static `\Drupal::service()` thay vì inject.
