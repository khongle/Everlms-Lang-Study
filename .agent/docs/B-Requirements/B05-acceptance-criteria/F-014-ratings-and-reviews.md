# F-014 Acceptance Criteria - Ratings & reviews

## Core criteria
1. **Given** an enrolled learner, **when** they submit a 1–5 star rating (optionally with text), **then** a `lms_rating` is stored.
2. **Given** existing ratings, **when** a course is viewed, **then** the aggregate rating is displayed on the card and detail page.
3. **Given** a non-enrolled user, **when** they attempt to rate, **then** rating is not permitted (or limited per policy).
4. **Given** a user who already rated, **when** they rate again, **then** their rating is updated, not duplicated.

## Evidence expected
- Module: [lms_rating](../../C-Design/C05-modules/lms_rating.md).
- Test: rating-create + aggregate-display test; one-per-user constraint test (E03).
