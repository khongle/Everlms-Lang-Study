# B04-business-workflows - Workflow Index

10 business workflows (`WF-NNN`), one file each, mirrored by [C12-workflows/](../../C-Design/C12-workflows/README.md), the [C13-usecases/](../../C-Design/C13-usecases/README.md) per-WF folder, and [S04-data-flow/](../../S-System/S04-data-flow/) — same number, same slug.

| ID | Workflow | Modules | Related features |
| -- | -------- | ------- | ---------------- |
| [WF-001](WF-001-enrollment-and-learning.md) | Enrollment & learning | lms_progress, lms_course, lms_lesson, lms_order | F-001, F-003, F-004, F-005, F-014 |
| [WF-002](WF-002-quiz-attempt-and-grading.md) | Quiz attempt & grading | lms_quiz, lms_question, lms_progress | F-006, F-008 |
| [WF-003](WF-003-certificate-issuance.md) | Certificate issuance | lms_certificate, lms_quiz, lms_notification | F-007 |
| [WF-004](WF-004-order-checkout-and-payment.md) | Order checkout & payment | lms_order, lms_payment, lms_stripe | F-010, F-011, F-009 |
| [WF-005](WF-005-membership-subscription.md) | Membership subscription | lms_membership, lms_order, lms_payment | F-012 |
| [WF-006](WF-006-course-authoring-and-publishing.md) | Course authoring & publishing | lms_course, lms_chapter, lms_lesson, lms_ai | F-002, F-016, F-018 |
| [WF-007](WF-007-user-registration-and-onboarding.md) | User registration & onboarding | user, lms_mobile_api, lms_notification | F-021, F-019 |
| [WF-008](WF-008-forum-discussion.md) | Forum discussion | lms_forum, lms_notification | F-013, F-017 |
| [WF-009](WF-009-coupon-discount-redemption.md) | Coupon / discount redemption | lms_order, lms_payment | F-010 |
| [WF-010](WF-010-ai-assisted-content-generation.md) | AI-assisted content generation | lms_ai, lms_course, lms_quiz | F-018, F-002, F-006 |

> Count (10) is data-derived. Each WF has a C12 design mirror, a C13 usecase folder, and an S04 data-flow file with the identical `WF-NNN-<slug>`.
