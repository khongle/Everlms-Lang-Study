# C04 Entity - lms_faq

**Display name:** FAQ
**Owning module(s):** [lms_faq](../C05-modules/lms_faq.md)
**Mode:** as-built (Content Entity, translatable)

## Purpose
A frequently-asked-question entry, categorised and optionally scoped to a course; rendered as a public FAQ (accordion).

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, langcode, default_langcode | keys + translation |
| question | the question |
| field_answer | formatted answer |
| field_scope | global vs course-scoped |
| field_course | ref → lms_course (when scoped) |
| field_category | `faq_category` taxonomy |
| weight | display order |

## Relationships
- Optional N→1 `lms_course`; categorised by `faq_category`.

## Editorial/data management
Content editors maintain FAQs via the FAQ form; ordered by `weight`; translatable. All copy in fields. See [F-016](../../B-Requirements/B03-features/F-016-blog-and-faq-content.md).
