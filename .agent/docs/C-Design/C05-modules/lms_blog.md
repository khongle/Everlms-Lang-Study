# C05 Module - lms_blog

**Name:** Blog · **Phase:** 8 · **Epic:** [E08](../../D-Development/D02-epics/E08-community.md) · **Status:** done

## Responsibility
Owns the blog article entity (`lms_article`) for marketing/educational content with SEO; AI-assisted drafting via lms_ai. ~6 routes.

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
- AI drafting via [lms_ai](lms_ai.md) ([INT-005](../C06-apis/INT-005-openai.md)).

## Backing pages
| Label | URL |
| ----- | --- |
| Blog listing | `/blog` |
| Article detail | `/blog/{article}` |

## Cross references
- Entity [C04](../C04-entities/lms_article.md); tags `blog_tags`.

## Dependencies
- lms_base, lms_ai.

## Implementation notes
- Distinct from core `node` blog; uses custom `lms_article`. SEO fields; translatable.
