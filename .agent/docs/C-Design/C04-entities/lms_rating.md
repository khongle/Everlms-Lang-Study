# C04 Entity - lms_rating

**Display name:** Rating / Review
**Owning module(s):** [lms_rating](../C05-modules/lms_rating.md)
**Mode:** as-built (Content Entity)

## Purpose
A learner's star rating + optional written review for a course; aggregated for catalog display.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid | entity keys |
| uid | reviewer |
| field_course | ref → lms_course |
| field_score | star score (1–5) |
| field_review | formatted review text |
| field_approved | moderation flag |
| field_is_featured | featured-review flag |
| created | timestamp |

## Relationships
- N→1 `user`, N→1 `lms_course`. Aggregate cached on `lms_course.field_rating`.

## Editorial/data management
Submitted by enrolled learners; moderated (approve/feature) by staff. ~79 live ratings. See [F-014](../../B-Requirements/B03-features/F-014-ratings-and-reviews.md).
