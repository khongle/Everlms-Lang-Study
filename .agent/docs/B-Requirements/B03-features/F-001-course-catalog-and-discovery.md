# F-001 - Course catalog & discovery

**Status:** built
**Primary module:** [lms_course](../../C-Design/C05-modules/lms_course.md)
**Epic:** [E02 Course catalog](../../D-Development/D02-epics/E02-course-catalog.md)
**Related workflows:** [WF-001 Enrollment & learning](../B04-business-workflows/WF-001-enrollment-and-learning.md)
**Acceptance:** [B05 F-001](../B05-acceptance-criteria/F-001-course-catalog-and-discovery.md)
**Source:** `web/modules/custom/lms_course/`

## Requirement
Người dùng ẩn danh và đã đăng nhập có thể duyệt catalog course công khai, filter/search theo category, level, language và tag (taxonomy vocabulary `course_category`, `course_level`, `course_language`, `course_tags`), và mở trang course detail/landing hiển thị title, summary, instructor, price, rating và curriculum outline.

## Business value
Catalog là điểm đầu phễu (top of the funnel) — discovery và marketing cho course trả phí/miễn phí thúc đẩy enrollment và doanh thu. Catalog phải do editor quản lý và thân thiện với SEO.

## Scope notes
- Course là một custom content entity (`lms_course`) không có bundle; curriculum được tổ chức qua `field_chapters` → chapters → lessons.
- Listing/filtering dựa trên Views và/hoặc entity query; trang detail là một course route/controller.
- Pricing và việc gating "free vs paid" được đọc ở đây nhưng được enforce tại F-004/F-010.
- Ngoài phạm vi: authoring (F-002), cơ chế enrollment (F-004).

## Cross references
- Design: [C05 lms_course](../../C-Design/C05-modules/lms_course.md), [C04 lms_course entity](../../C-Design/C04-entities/lms_course.md), [C07 sitemap](../../C-Design/C07-sitemap/_index.md)
- Pages: catalog list + course detail in [C11 pages](../../C-Design/C11-pages-components/pages/_index.md)
