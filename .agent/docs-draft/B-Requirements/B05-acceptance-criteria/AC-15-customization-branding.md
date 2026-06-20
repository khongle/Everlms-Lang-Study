# AC-15 — Customization & Branding

**Feature ID:** F-015
**Epic:** EPIC-A — Content Lifecycle (presentation layer)
**Epic Owner:** Admin
**Feature Doc:** [B03 F-015 Customization & Branding](../B03-features/15-customization-branding.md)

---

## Acceptance Criteria

### AC-BRAND01 — Theme customization via admin UI

**Priority:** Must
**Given** Admin ở Appearance / Branding settings
**When** Admin upload logo, set primary color, font family
**Then** Changes reflect site-wide trong ≤30 seconds (after cache clear)
**And** Logo hiển thị đúng trên header, favicon, OG image
**And** CSS custom properties cập nhật; không hardcoded hex màu trong CSS

### AC-BRAND02 — Homepage custom layout

**Priority:** Must
**Given** Admin cấu hình homepage blocks: hero, featured courses, testimonials
**When** Visitor truy cập homepage
**Then** Blocks hiển thị đúng thứ tự và content đã config
**And** Responsive layout hoạt động trên mobile

### AC-BRAND03 — Email template customization

**Priority:** Should
**Given** Admin edit email template cho "Certificate Issued"
**When** Certificate email gửi đi
**Then** Email dùng custom template với logo và branding colors
**And** Tokens (student name, course name) được replace đúng

### AC-BRAND04 — Custom domain support

**Priority:** Could
**Given** Organization muốn dùng custom domain (learn.acme.com)
**When** Domain configured (external DNS + server config)
**Then** LMS serve trên custom domain; SSL cert auto-provisioned hoặc manual upload
**And** Internal links không break

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| Logo file quá lớn (>5MB) | Resize tự động; warning nếu quá lớn |
| Invalid hex color input | Validation error; giữ previous color |
| Email template missing token | Render empty string; log warning |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| UX | Theme changes preview before apply |
| Performance | Custom CSS minified và cached |
