# F-014 - Ratings & reviews

**Status:** built
**Primary module:** [lms_rating](../../C-Design/C05-modules/lms_rating.md)
**Epic:** [E08 Community](../../D-Development/D02-epics/E08-community.md)
**Related workflows:** [WF-001 Enrollment & learning](../B04-business-workflows/WF-001-enrollment-and-learning.md)
**Acceptance:** [B05 F-014](../B05-acceptance-criteria/F-014-ratings-and-reviews.md)
**Source:** `web/modules/custom/lms_rating/`

## Requirement
Enrolled learners rate a course (1–5 stars) and optionally leave a written review (`lms_rating`). Aggregate rating is shown on the catalog (F-001) and course detail to inform buyers.

## Business value
Social proof drives conversion; aggregate ratings help learners choose and give instructors feedback.

## Scope notes
- One rating entity per user-per-course (typically); aggregate computed for display.
- Eligibility usually requires enrollment/some progress.
- Surfaced in catalog cards and course detail; available via mobile API.

## Cross references
- Design: [C04 lms_rating](../../C-Design/C04-entities/lms_rating.md), [C05 lms_rating](../../C-Design/C05-modules/lms_rating.md)
- Related: F-001 Catalog
