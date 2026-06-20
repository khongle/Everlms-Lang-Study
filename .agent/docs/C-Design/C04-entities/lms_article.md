# C04 Entity - lms_article

**Display name:** Blog Article
**Owning module(s):** [lms_blog](../C05-modules/lms_blog.md)
**Mode:** as-built (Content Entity, translatable)

## Purpose
A marketing/educational blog article with SEO metadata. Public, editor-managed (distinct from core `node` blog).

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
- Tagged via `blog_tags` taxonomy. `lms_blog` depends on `lms_ai` (AI-assisted drafting).

## Editorial/data management
Content editors author articles via the article form; SEO fields editable; translatable. All copy in fields. See [F-016](../../B-Requirements/B03-features/F-016-blog-and-faq-content.md).
