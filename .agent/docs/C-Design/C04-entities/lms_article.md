# C04 Entity - lms_article

**Display name:** Blog Article
**Owning module(s):** [lms_blog](../C05-modules/lms_blog.md)
**Mode:** as-built (Content Entity, translatable)

## Purpose
Một blog article marketing/giáo dục có SEO metadata. Công khai, do editor quản lý (khác với blog `node` core).

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, langcode, default_langcode | keys + translation |
| title | article title |
| content | formatted body |
| thumbnail | hero image (target_id + alt/display/description) |
| seo_title, seo_description | SEO metadata |
| status, created, changed | publish + timestamps |
| field_blog_tags (via node-style tags) | tags taxonomy (see note) |

## Relationships
- Được gắn tag qua `blog_tags` taxonomy. `lms_blog` phụ thuộc vào `lms_ai` (soạn thảo có hỗ trợ AI).

## Validation rules
Nguồn: `web/modules/custom/lms_blog/src/Entity/LmsArticle.php`.
- `title`: bắt buộc, translatable, tối đa 255 ký tự.
- `seo_title`: tối đa 60 ký tự; `seo_description`: tối đa 160 ký tự (giới hạn theo chuẩn SEO snippet, không bắt buộc).
- `thumbnail`: file field, giới hạn phần mở rộng `png jpg jpeg webp` (`setSetting('file_extensions', ...)`).
- `content`: không bắt buộc (khác với `lms_faq.field_answer` là required) — bài viết có thể lưu nháp không có nội dung.
- Không có `addConstraint()` tuỳ biến nào trên entity này.

## Data invariants
- `status` mặc định TRUE (published-by-default) — khác hẳn `lms_course.status` (mặc định FALSE, draft-first). Article xuất bản ngay khi tạo trừ khi editor tắt thủ công.
- Entity translatable ở `title`/`content`; `seo_title`/`seo_description` KHÔNG được đánh dấu `setTranslatable(TRUE)` — nghĩa là SEO metadata dùng chung cho mọi ngôn ngữ, có thể là gap nếu cần SEO đa ngôn ngữ.

## Calculations & derived values
- `changed` tự cập nhật theo hành vi mặc định của field type `changed` khi entity save (built-in Drupal core), không phải logic nghiệp vụ tuỳ biến.
- Không có trường nào khác được tính toán/dẫn xuất (không có word count, reading time, view count...).

## Editorial/data management
Content editor viết article qua article form; các field SEO có thể chỉnh sửa; hỗ trợ translatable. Toàn bộ nội dung nằm trong field. Xem [F-016](../../B-Requirements/B03-features/F-016-blog-and-faq-content.md).
