# C05 Module - lms_faq

**Name:** FAQ · **Phase:** 8 · **Epic:** [E08](../../D-Development/D02-epics/E08-community.md) · **Status:** done

## Responsibility
Sở hữu các FAQ entry, có phân loại (category) và tùy chọn giới hạn theo course (course-scoped); hiển thị dạng accordion công khai.

## Owned / related entities
- [lms_faq](../C04-entities/lms_faq.md)

## Requirements features (B03)
| Feature | Title |
| ------- | ----- |
| [F-016](../../B-Requirements/B03-features/F-016-blog-and-faq-content.md) | Blog & FAQ content |

## Business workflows (B04)
- —

## Integrations / APIs (B06 / C06)
- —

## Backing pages
| Label | URL |
| ----- | --- |
| FAQ | `/faq` |

## Cross references
- Entity [C04](../C04-entities/lms_faq.md); danh mục `faq_category`.

## Dependencies
- lms_base.

## Implementation notes
- `field_scope` global vs course-scoped; sắp xếp theo `weight`; hỗ trợ translatable (đa ngôn ngữ).
