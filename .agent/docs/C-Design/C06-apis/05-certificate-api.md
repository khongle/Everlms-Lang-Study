# C06-05 — Certificate API

> Module: `lms_certificate`.
> Hai entity: `lms_certificate_template` (template PDF) và `lms_user_certificate` (chứng chỉ đã cấp).
> Public verification là route duy nhất anonymous có thể access.

---

## Route Table

| Route ID | Path | Method | Controller / Form | Auth | Response |
|----------|------|--------|--------------------|------|----------|
| `lms_certificate.verify` | `/certificate/verify` | GET/POST | `VerificationForm` (Form API) | anon | HTML/Form |
| `lms_certificate.revoke` | `/admin/lms/certificates/{lms_user_certificate}/revoke` | GET/POST | `RevokeCertificateForm` | perm:revoke any certificate | HTML/Form |
| `lms_certificate.regenerate` | `/workspace/certificates/{lms_user_certificate}/regenerate` | GET | `CertificateAdminController::regenerateCertificate` | perm:administer lms_course | HTML/redirect |
| `lms_certificate.admin_dashboard` | `/workspace/certificates` | GET | `CertificateAdminController::adminDashboard` | perm:administer lms_course | HTML |
| `lms_certificate.admin_templates` | `/workspace/certificate-templates` | GET | `CertificateAdminController::adminTemplates` | perm:administer lms_course | HTML |
| `lms_certificate.admin_template.add` | `/workspace/certificate-templates/add` | GET | `CertificateAdminController::templateAddForm` | perm:administer lms_course | HTML/Form |
| `lms_certificate.admin_template.edit` | `/workspace/certificate-templates/{lms_certificate_template}/edit` | GET | `CertificateAdminController::templateEditForm` | perm:administer lms_course | HTML/Form |
| `lms_certificate.admin_template.preview` | `/workspace/certificate-templates/{lms_certificate_template}/preview` | GET | `CertificateAdminController::templatePreview` | perm:administer lms_course | HTML/PDF |

---

## Route Chi Tiết

### GET + POST `/certificate/verify`

**Auth:** `_access: TRUE` (fully public — anonymous)
**Controller:** `Drupal\lms_certificate\Form\VerificationForm`

**Form flow:**
1. User nhập verification code.
2. Form submit → query `lms_user_certificate` by `verification_code` field.
3. Nếu tìm thấy và status = Active → hiển thị thông tin certificate.
4. Nếu Revoked → hiển thị trạng thái revoked.
5. Nếu không tìm thấy → hiển thị lỗi.

**Request (POST form):**
| Field | Type | Mô tả |
|-------|------|-------|
| `verification_code` | string | Mã xác thực in trên chứng chỉ |

**Response:** HTML page với thông tin:
```
Recipient name, Course name, Issue date, Status (Active/Revoked), Issuer
```

**Gap:** Không có JSON API cho verify (e.g., để embed badge third-party). Nếu cần `/api/certificate/verify?code=XYZ` → cần bổ sung.

---

### GET/POST `/admin/lms/certificates/{lms_user_certificate}/revoke`

**Auth:** `perm:revoke any certificate`
**Controller:** `RevokeCertificateForm`
**Path param:** `lms_user_certificate` — entity upcast

**Business logic:**
1. Hiển thị confirm form.
2. Khi confirm → set `status = Revoked` trên entity.
3. Log revocation + timestamp + revoker.

---

### GET `/workspace/certificates/{lms_user_certificate}/regenerate`

**Auth:** `perm:administer lms_course`
**Controller:** `CertificateAdminController::regenerateCertificate`

**Business logic:**
1. Load `lms_user_certificate` entity.
2. Load linked `lms_certificate_template`.
3. Render PDF dùng dompdf.
4. Save PDF file vào private file system.
5. Update `pdf_file` field trên entity.
6. Redirect về admin dashboard với success message.

---

### GET `/workspace/certificate-templates/{id}/preview`

**Auth:** `perm:administer lms_course`
**Controller:** `CertificateAdminController::templatePreview`
**Response:** HTML hoặc inline PDF (dùng dompdf render)

---

## Certificate Issuance Flow (background — không có API route)

Certificate được cấp tự động qua Event System — không phải HTTP endpoint:

```
QuizCompletedEvent fired (quiz submit)
  → CertificateSubscriber::onQuizCompleted
    → Check: quiz pass_threshold met?
    → Check: course completion 100%?
    → Generate PDF via dompdf
    → Create lms_user_certificate entity
    → Fire CertificateIssuedEvent
      → NotificationSubscriber sends notification
```

> **Gap:** Không có API endpoint để trigger issuance thủ công (admin grant). Cần xem AdminDashboard có form grant không.

---

## Gap Summary

| Thiếu | Mô tả |
|-------|-------|
| `GET /api/certificate/verify` | JSON endpoint cho third-party verification |
| Admin manual grant | Không có route cấp chứng chỉ thủ công cho student |
| PDF download route | Không thấy route `GET /certificates/{id}/download` trong routing.yml |
| Student certificate detail | `/my-certificates` ở lms_progress, không có `/certificates/{id}` riêng |

---

## Known Issues

| Ref | Mô tả |
|-----|-------|
| RISK-003 | AI generation thiếu rollback — nếu certificate generation fail giữa chừng, không có compensation |
| — | Verification form không rate-limit → brute-force code guessing possible |
| — | PDF file stored in private filesystem — download route chưa rõ |

---

*Module path: `web/modules/custom/lms_certificate/`*
