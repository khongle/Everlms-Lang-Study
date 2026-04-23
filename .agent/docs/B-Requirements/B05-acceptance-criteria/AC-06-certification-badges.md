# AC-06 — Certification & Badges

**Feature ID:** F-006 (Certification & Badges)
**Epic:** EPIC-E — Certificate & Verification
**Epic Owner:** System / Student
**Related Workflow:** [WF-003 — Certificate Issuance](../B04-business-workflows/WF-003-certificate-issuance.md)
**Feature Doc:** [B03 F-006 Certification & Badges](../B03-features/06-certification-badges.md)

---

## Acceptance Criteria

### AC-E01 — Auto issuance khi đạt điều kiện

**Priority:** Must
**Given** Quiz có CertificateTemplate linked và student vừa pass quiz
**When** GradingStrategy dispatch event `quiz_passed`
**Then** UserCertificate record tạo trong ≤60 giây
**And** PDF generated và stored; student nhận email với attachment

### AC-E02 — Verify endpoint public

**Priority:** Must
**Given** UserCertificate tồn tại với verify_token = "ABC123"
**When** Bất kỳ ai (không cần login) truy cập `/certificate/verify/ABC123`
**Then** Page hiển thị: student name, course name, issue date, status=valid
**And** HTTP response 200; meta tags đủ cho social share

### AC-E03 — Certificate PDF đúng template

**Priority:** Must
**Given** CertificateTemplate được cấu hình với logo, layout, tokens
**When** PDF generation triggered
**Then** PDF chứa: student full name, course name, completion date, unique certificate ID
**And** Logo và branding của tổ chức hiển thị đúng; font không bị fallback

### AC-E04 — Admin revoke certificate

**Priority:** Must
**Given** Admin quyết định revoke UserCertificate ID = 123
**When** Admin click Revoke trong admin interface
**Then** UserCertificate.status = revoked; timestamp ghi nhận
**And** Verify endpoint trả "This certificate has been revoked" với HTTP 200

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| Student pass quiz 2 lần (retry) | Chỉ 1 UserCertificate; không issue duplicate |
| CertificateTemplate bị xóa sau khi issue | Existing PDFs vẫn valid; verify link vẫn work |
| Email bounce khi gửi certificate | Certificate record vẫn tạo; email retry theo policy |
| PDF font không available trên server | Fallback font; log warning; PDF vẫn generate |
| Certificate với tên student có ký tự đặc biệt (tiếng Việt) | PDF render đúng Unicode |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Performance | PDF generation ≤10s |
| Availability | Verify endpoint uptime 99.9% |
| Security | Verify token không guessable (UUID v4) |
| Data retention | PDF files retained ≥5 năm |
