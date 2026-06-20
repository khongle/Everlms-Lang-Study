# C12-workflows - Index

Design view of the 10 business workflows (state model, permissions, notifications), mirroring [B04](../../B-Requirements/B04-business-workflows/_index.md) by `WF-NNN-<slug>`. B04 = "what business flow & why"; C12 = "what states, who transitions, what fires". Canonical states: `draft, submitted, in_review, approved_or_completed, rejected_or_returned, archived` (subset per workflow).

| ID | Workflow | Modules |
| -- | -------- | ------- |
| [WF-001](WF-001-enrollment-and-learning.md) | Enrollment & learning | lms_progress, lms_course, lms_lesson |
| [WF-002](WF-002-quiz-attempt-and-grading.md) | Quiz attempt & grading | lms_quiz, lms_question |
| [WF-003](WF-003-certificate-issuance.md) | Certificate issuance | lms_certificate |
| [WF-004](WF-004-order-checkout-and-payment.md) | Order checkout & payment | lms_order, lms_payment |
| [WF-005](WF-005-membership-subscription.md) | Membership subscription | lms_membership |
| [WF-006](WF-006-course-authoring-and-publishing.md) | Course authoring & publishing | lms_course, lms_ai |
| [WF-007](WF-007-user-registration-and-onboarding.md) | User registration & onboarding | user, lms_mobile_api |
| [WF-008](WF-008-forum-discussion.md) | Forum discussion | lms_forum |
| [WF-009](WF-009-coupon-discount-redemption.md) | Coupon / discount redemption | lms_order |
| [WF-010](WF-010-ai-assisted-content-generation.md) | AI-assisted content generation | lms_ai |
