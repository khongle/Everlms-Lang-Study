# F-016 Acceptance Criteria - Blog & FAQ content

## Core criteria
1. **Given** an editor, **when** they publish a blog article (`lms_article`, `blog_tags`), **then** it appears on the public blog listing and detail page.
2. **Given** an editor, **when** they add an FAQ entry (`lms_faq`, `faq_category`), **then** it appears in the categorised FAQ.
3. **Given** blog/FAQ content, **when** rendered, **then** all copy comes from entity fields (no hardcoded text).
4. **Given** an unpublished article, **when** a visitor browses, **then** it is not shown.

## Evidence expected
- Modules: [lms_blog](../../C-Design/C05-modules/lms_blog.md), [lms_faq](../../C-Design/C05-modules/lms_faq.md).
- Test: publish/visibility test; editor-usability smoke (E03).
