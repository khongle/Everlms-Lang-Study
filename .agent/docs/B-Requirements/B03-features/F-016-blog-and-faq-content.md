# F-016 - Blog & FAQ content

**Status:** built
**Primary module:** [lms_blog](../../C-Design/C05-modules/lms_blog.md), [lms_faq](../../C-Design/C05-modules/lms_faq.md)
**Epic:** [E08 Community](../../D-Development/D02-epics/E08-community.md)
**Related workflows:** [WF-006 Course authoring & publishing](../B04-business-workflows/WF-006-course-authoring-and-publishing.md)
**Acceptance:** [B05 F-016](../B05-acceptance-criteria/F-016-blog-and-faq-content.md)
**Source:** `web/modules/custom/lms_blog/`, `lms_faq/`

## Requirement
Editors publish **blog articles** (`lms_article`, tagged via `blog_tags`) for marketing/educational content and maintain a **FAQ** (`lms_faq`, categorised via `faq_category`). Both are public and editor-managed (no hardcoded copy).

## Business value
Content marketing drives organic discovery (SEO) and the FAQ deflects support questions; both are pure editorial surfaces.

## Scope notes
- `lms_article` (blog) and `lms_faq` are separate content entities.
- Public listing + detail pages; FAQ may render as accordions.
- Note: core also ships `node.type` `blog`, `article`, `glossary` etc.; the LMS blog uses the custom `lms_article` entity.

## Cross references
- Design: [C04 lms_article](../../C-Design/C04-entities/lms_article.md), [C04 lms_faq](../../C-Design/C04-entities/lms_faq.md)
