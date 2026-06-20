# Feature Guide: Nhận Certificate

**Feature ID:** FEAT-06 (certification-badges)
**Available since:** v0.1.0
**User roles:** Student đã hoàn thành khóa eligible.
**Prerequisites:** `UserCourse.status = completed` + course có `CertificateTemplate` gắn.

## What It Does

Khi học viên hoàn thành khóa học đủ điều kiện, system tự động issue `UserCertificate` với mã unique và link verify public. Certificate có thể download PDF và chia sẻ.

## How It Works

1. **Trigger:** `UserCourse.status` → `completed`.
2. System check course có `certificate_template_ref` không.
3. Nếu có → create `UserCertificate` (unique code `CERT-YYYY-XXXXXXXX`, issued_at = now).
4. Render PDF server-side qua dompdf với template (name, course, date, issuer signature).
5. Gửi email confirmation + attach PDF.
6. User nhận notification (in-app + email).

## How to Use

### Xem certificate

1. `/my-learning` → tab **Certificates**.
2. List tất cả certificates đã earn.
3. Card hiển thị: course, issued date, verification code.

### Download PDF

- Click **Download** → PDF (A4/Letter tùy template).

### Verify public

- URL public: `/verify/{cert_code}` — **không cần login**.
- Public page hiển thị: tên user, course, ngày cấp, trạng thái (valid / revoked).
- Dùng cho CV / portfolio sharing.

### Share

- **Copy verify link** button.
- **Share to LinkedIn** (deep link tích hợp LinkedIn Add to Profile).

### Revoke (admin only)

- Admin có thể revoke certificate (lý do: plagiarism, policy violation).
- Sau revoke, verify page hiển thị status = **Revoked** với timestamp + reason.

## Configuration Options (instructor / admin set)

| Option | Default | Description |
|--------|---------|-------------|
| `template_id` | default | Template chọn theo course |
| `passing_threshold` | 100% progress | % tối thiểu để eligible (có thể custom cho khóa yêu cầu quiz pass) |
| `require_quiz_pass` | false | Thêm điều kiện pass quiz cuối |
| `valid_duration_days` | null | Nếu set, certificate expire sau N ngày |

## Tips & Best Practices

- Lưu PDF về local ngay sau nhận — template có thể thay đổi theo thời gian nhưng PDF giữ nguyên.
- Verification code không thay đổi suốt đời certificate (trừ khi revoke).
- Nếu PDF font/layout sai, báo admin regenerate — xem U05 §Certificate issues.

## Known Limitations

- PDF generate đồng bộ trong request — khóa có > 50 chứng chỉ pending có thể chậm (sẽ queue trong v0.2.0).
- Template custom font cần admin upload trước — xem U08 §Certificate Templates.

## Related

- Feature spec: `B-Requirements/B03-features/06-certification-badges.md`
- Entity: `C-Design/C04-entities/05-certificate-entities.md` (CertificateTemplate, UserCertificate)
- Workflow: `B-Requirements/B04-business-workflows/WF-003-certificate-issuance.md`
- Runbook: `H-Operations/H02-runbooks/RUN-XXX-certificate-regenerate.md` (admin-facing)
