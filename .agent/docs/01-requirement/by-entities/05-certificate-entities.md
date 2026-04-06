# Certificate Entities

> Certificate template + issued user certificates.

---

## Certificate Template (`lms_certificate`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Tên template |
| `field_background` | image | Ảnh nền certificate |
| `field_logo` | image | Logo tổ chức |
| `field_signature` | image | Chữ ký |
| `field_orientation` | list_string | `portrait` / `landscape` |
| `field_layout` | text_long | Layout config (JSON) |
| `field_color_scheme` | string | Color hex for accent |
| `status` | boolean | Active / Inactive |

### Available Tokens
| Token | Value |
|-------|-------|
| `{student_name}` | Tên học viên |
| `{course_name}` | Tên khoá học |
| `{completion_date}` | Ngày hoàn thành |
| `{certificate_id}` | Mã chứng chỉ |
| `{score}` | Điểm (nếu có quiz) |
| `{instructor_name}` | Tên giảng viên |

---

## User Certificate (`lms_user_certificate`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_certificate` | entity_reference | Template |
| `field_holder` | entity_reference (user) | Người nhận |
| `field_course` | entity_reference | Course |
| `field_quiz_result` | entity_reference | Quiz result (nếu có) |
| `field_certificate_id` | string | Mã chứng chỉ unique (e.g. `ELMS-DS-78491-SA`) |
| `field_pdf_link` | file | PDF file đã generate |
| `field_issued_date` | timestamp | Ngày cấp |
| `field_expiry_date` | timestamp | Ngày hết hạn (null = lifetime) |
| `field_revoked` | boolean | Đã thu hồi |
| `field_revoke_reason` | string | Lý do thu hồi |

### Business Rules
- `field_certificate_id` auto-generated unique, format: `ELMS-{PREFIX}-{RANDOM}-{INITIALS}`
- PDF generated bằng DOMPDF/mPDF, lưu vào file system
- Public verification page: `/verify/{certificate_id}` → show details nếu valid
- `field_revoked = true` → verification page shows "Certificate Revoked"
