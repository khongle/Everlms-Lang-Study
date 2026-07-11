# C05 Module - lms_blog

**Name:** Blog · **Phase:** 8 · **Epic:** [E08](../../D-Development/D02-epics/E08-community.md) · **Status:** done

## Responsibility
Sở hữu entity blog article (`lms_article`) cho nội dung marketing/giáo dục có SEO; soạn thảo có hỗ trợ AI qua lms_ai. ~6 route.

## Owned / related entities
- [lms_article](../C04-entities/lms_article.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-016](../../B-Requirements/B03-features/F-016-blog-and-faq-content.md) | Blog & FAQ content |

## Business workflows (B04)
| Workflow | Title |
| -------- | ----- |
| [WF-006](../../B-Requirements/B04-business-workflows/WF-006-course-authoring-and-publishing.md) | Course authoring & publishing (editorial) |

## Integrations / APIs (B06 / C06)
- Soạn thảo bằng AI qua [lms_ai](lms_ai.md) ([INT-005](../C06-apis/INT-005-openai.md)).

## Backing pages
| Label | URL |
| ----- | --- |
| Blog listing | `/blog` |
| Article detail | `/blog/{article}` |

## Cross references
- Entity [C04](../C04-entities/lms_article.md); tag `blog_tags`.

## Dependencies
- lms_base, lms_ai.

## Implementation notes
- Khác với blog `node` của core; dùng `lms_article` tùy biến riêng. Có field SEO; hỗ trợ translatable (đa ngôn ngữ).
