# AC-09 — Analytics & Reporting

**Feature ID:** F-009
**Epic:** EPIC-G — Analytics & Reports
**Epic Owner:** Admin / Instructor
**Feature Doc:** [B03 F-009 Analytics & Reporting](../B03-features/09-analytics-reporting.md)

---

## Acceptance Criteria

### AC-G01 — Report filters

**Priority:** Must
**Given** Admin/Instructor ở Reports page
**When** User apply filters: date range, user, course
**Then** Report data filter đúng theo tất cả applied filters
**And** Filter UI responsive; URL reflect filter state (shareable link)

### AC-G02 — Report accuracy

**Priority:** Must
**Given** Report hiển thị enrollment count = 150 cho Course X
**When** Admin cross-check với raw UserCourse records
**Then** Số liệu khớp với data nguồn; không có off-by-one hoặc stale cache
**And** Aggregation logic documented và auditable

### AC-G03 — Report performance

**Priority:** Must
**Given** Dataset trung bình (10K students, 100 courses, 500K progress records)
**When** Admin load report page với default filters
**Then** Page tải xong trong ≤3 giây (p95)
**And** Nếu query lâu hơn: hiển thị loading state, không timeout với blank page

### AC-G04 — Export CSV/PDF

**Priority:** Should
**Given** Report đang hiển thị với filters applied
**When** User click "Export CSV" hoặc "Export PDF"
**Then** File download bắt đầu trong ≤5s; data trong file khớp với screen
**And** CSV encoding UTF-8 BOM cho Excel compatibility; PDF có header + pagination

### AC-G05 — Instructor analytics

**Priority:** Should
**Given** Instructor xem analytics cho own courses
**When** Instructor truy cập "My Course Analytics"
**Then** Thấy enrollment trend, completion rate, quiz score distribution per course
**And** Không thấy data của courses từ instructor khác

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| Report với date range = ngày hôm nay | Realtime data hoặc max 5-minute cache |
| Export 100K+ rows | Background job + notify khi sẵn sàng; không timeout browser |
| Instructor xem admin reports | 403; chỉ thấy own course data |
| Zero data kết quả | Empty state message + suggestion |
| Report query slow (>3s) | Timeout with error message; log query for optimization |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Performance | Report queries cached (5-15 min TTL) |
| Performance | DB indexes trên enrollment, progress, order tables |
| Security | Instructor chỉ thấy own data; admin thấy all |
| Data freshness | Dashboard stats max 5 minutes stale |
