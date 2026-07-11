# F-016 Acceptance Criteria - Blog & FAQ content

## Core criteria
1. **Given** một editor, **when** họ publish một blog article (`lms_article`, `blog_tags`), **then** article xuất hiện trên blog listing công khai và trang detail.
2. **Given** một editor, **when** họ thêm một FAQ entry (`lms_faq`, `faq_category`), **then** entry xuất hiện trong FAQ đã phân category.
3. **Given** nội dung blog/FAQ, **when** được render, **then** toàn bộ copy đến từ entity field (không hardcode text).
4. **Given** một article chưa publish, **when** visitor duyệt, **then** article không được hiển thị.

## Evidence expected
- Modules: [lms_blog](../../C-Design/C05-modules/lms_blog.md), [lms_faq](../../C-Design/C05-modules/lms_faq.md).
- Test: publish/visibility test; editor-usability smoke (E03).
