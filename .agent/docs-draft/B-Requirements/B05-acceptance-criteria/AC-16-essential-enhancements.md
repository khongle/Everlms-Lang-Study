# AC-16 — Essential Enhancements

**Feature ID:** F-016
**Epic:** EPIC-C / EPIC-G — Cross-cutting Enhancements
**Epic Owner:** Product / Admin
**Feature Doc:** [B03 F-016 Essential Enhancements](../B03-features/16-essential-enhancements.md)

---

## Acceptance Criteria

### AC-ENH01 — Bulk operations for admin

**Priority:** Should
**Given** Admin ở Course/User management list
**When** Admin select multiple items và choose bulk action (publish, unpublish, delete)
**Then** Action applied to all selected items với confirmation dialog
**And** Success/failure count reported; partial success handled gracefully

### AC-ENH02 — Audit log for critical actions

**Priority:** Must
**Given** Critical actions: payment, certificate revoke, user role change, course publish
**When** Action performed by any actor
**Then** Audit log entry tạo: actor, action, target, timestamp, IP
**And** Audit log accessible bởi Admin; searchable và filterable

### AC-ENH03 — Scheduled content publishing

**Priority:** Could
**Given** Instructor set publish date = ngày mai 9:00 AM cho Course X
**When** Cron job chạy lúc 9:00 AM ngày mai
**Then** Course tự động publish; hiển thị trên catalog
**And** Email notification gửi đến instructor: "Course X is now live"

### AC-ENH04 — Multi-language content support

**Priority:** Should
**Given** Course có English và Vietnamese versions
**When** User với browser language = Vietnamese truy cập course
**Then** Vietnamese version hiển thị nếu available; fallback về English
**And** Language switcher available; preference stored

### AC-ENH05 — Sitemap & SEO

**Priority:** Must
**Given** System có 200 published courses
**When** Search engine crawler truy cập /sitemap.xml
**Then** Sitemap chứa URLs cho tất cả published courses, categories, static pages
**And** Course pages có: title tag, meta description, OG tags, canonical URL

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| Bulk delete 1000 courses | Batch processing; không timeout; progress indicator |
| Audit log storage grows large | Log rotation policy; archive sau 90 ngày |
| Scheduled publish khi server down | Retry khi cron next runs; không skip |
| Sitemap với 10K+ courses | Sitemap index với multiple sitemaps (50K URLs/file limit) |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Compliance | Audit logs retained ≥1 year |
| SEO | Core Web Vitals: LCP ≤2.5s, CLS ≤0.1, FID ≤100ms |
| Maintainability | Scheduled jobs idempotent; safe to retry |
