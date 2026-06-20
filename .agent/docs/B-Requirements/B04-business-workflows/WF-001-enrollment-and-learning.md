# WF-001 - Enrollment & learning

## Business flow
A user discovers a course in the catalog (F-001), obtains access (free enroll, paid order WF-004, or active membership WF-005), then works through chapters/lessons. Each lesson view records progress (`lms_user_lesson`); the course rollup (`lms_user_course`) tracks percent complete and last-accessed lesson so the learner can resume. Completing all required lessons and passing the final quiz (WF-002) marks the course complete, which triggers certificate issuance (WF-003). The learner may rate the course (F-014) and take notes (F-015).

## Participating modules
[lms_course](../../C-Design/C05-modules/lms_course.md), [lms_chapter](../../C-Design/C05-modules/lms_chapter.md), [lms_lesson](../../C-Design/C05-modules/lms_lesson.md), [lms_progress](../../C-Design/C05-modules/lms_progress.md), [lms_order](../../C-Design/C05-modules/lms_order.md), [lms_rating](../../C-Design/C05-modules/lms_rating.md)

## Related features (B03)
[F-001](../B03-features/F-001-course-catalog-and-discovery.md), [F-003](../B03-features/F-003-lesson-delivery-and-media.md), [F-004](../B03-features/F-004-enrollment-and-content-gating.md), [F-005](../B03-features/F-005-progress-tracking-and-resume.md), [F-014](../B03-features/F-014-ratings-and-reviews.md)

## Trigger
User clicks "Enroll"/"Start learning" on a course they are entitled to access.

## Outcome
`lms_user_course` enrollment exists; lesson progress accrues; on completion the course is marked done and certificate eligibility is set.

## Design link
[C12 WF-001](../../C-Design/C12-workflows/WF-001-enrollment-and-learning.md)
