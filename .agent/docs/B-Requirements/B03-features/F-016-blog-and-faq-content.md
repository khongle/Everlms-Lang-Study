# F-016 - Blog & FAQ content

**Status:** built
**Primary module:** [lms_blog](../../C-Design/C05-modules/lms_blog.md), [lms_faq](../../C-Design/C05-modules/lms_faq.md)
**Epic:** [E08 Community](../../D-Development/D02-epics/E08-community.md)
**Related workflows:** [WF-006 Course authoring & publishing](../B04-business-workflows/WF-006-course-authoring-and-publishing.md)
**Acceptance:** [B05 F-016](../B05-acceptance-criteria/F-016-blog-and-faq-content.md)
**Source:** `web/modules/custom/lms_blog/`, `lms_faq/`

## Requirement
Editor publish **blog article** (`lms_article`, gắn tag qua `blog_tags`) cho nội dung marketing/giáo dục và duy trì một **FAQ** (`lms_faq`, phân loại qua `faq_category`). Cả hai đều public và do editor quản lý (không có copy hardcode).

## Business value
Content marketing thúc đẩy organic discovery (SEO) và FAQ giúp giảm câu hỏi gửi tới support; cả hai đều là bề mặt (surface) thuần editorial.

## Scope notes
- `lms_article` (blog) và `lms_faq` là hai content entity riêng biệt.
- Có trang listing + detail công khai; FAQ có thể render dạng accordion.
- Lưu ý: Drupal core cũng đi kèm `node.type` `blog`, `article`, `glossary`...; blog của LMS dùng custom entity `lms_article`.

## Cross references
- Design: [C04 lms_article](../../C-Design/C04-entities/lms_article.md), [C04 lms_faq](../../C-Design/C04-entities/lms_faq.md)
