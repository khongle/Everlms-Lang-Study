# Task: Public Content Pages (Blog, FAQ, About, Pricing)

**ID:** TASK-046
**Unit:** — (cross-unit: U-055, U-056, existing controllers)
**Group:** G-UI-POLISH (extension)
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Polish các public content pages có trong sitemap nhưng chưa có UI task riêng: Blog listing/detail, FAQ page, About page, Pricing page.

## Context
- Sitemap C07: `/blog`, `/blog/{slug}`, `/faq`, `/about`, `/pricing`
- `lms_blog` module exists (entity + controller + templates)
- `lms_faq` module exists (entity only, no controller)
- `lms_course` has `PageController::about()` và `PageController::pricing()`
- `lms_membership` has pricing admin routes
- **Cần polish UI cho tất cả pages trên**

## Acceptance Criteria
- [ ] Blog listing: cards, pagination, categories/tags filter
- [ ] Blog detail: content, author, date, related posts, comments
- [ ] FAQ page: accordion, search, categorized
- [ ] About page: team, mission, stats
- [ ] Pricing page: membership plan comparison table, CTA buttons
- [ ] Responsive layout cho tất cả
- [ ] SEO metadata

## Technical Notes
- Modules: `lms_blog`, `lms_faq`, `lms_course`
- FAQ cần thêm controller + route (hiện chỉ có entity)
- About/Pricing controllers exist nhưng cần template polish
- SCSS: `_blog.scss`, `_faq.scss`, `_about.scss`, `_pricing.scss`

## Dependencies
- None (entities exist)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] All 5 sitemap page types functional
- [ ] Mobile responsive verified
