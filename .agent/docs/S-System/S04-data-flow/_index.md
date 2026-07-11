# S04-data-flow - Data Flow

Data movement per workflow, mirroring [B04](../../B-Requirements/B04-business-workflows/_index.md)/[C12](../../C-Design/C12-workflows/README.md) by `WF-NNN`. Mọi WF đều có một file `.md` riêng (ASCII flow diagram) — không còn workflow nào chỉ trỏ ngược về C12.

| WF | Data path | File |
| -- | --------- | ---- |
| WF-001 Enrollment & learning | user → lms_user_course/lms_user_lesson (DB) | [WF-001](WF-001-enrollment-and-learning.md) |
| WF-002 Quiz grading | attempt → quiz_result/responses (DB) | [WF-002](WF-002-quiz-attempt-and-grading.md) |
| WF-003 Certificate issuance | event → mPDF → user_certificate + file | [WF-003](WF-003-certificate-issuance.md) |
| WF-004 Order checkout | order → gateway (ext) → entitlement (DB) | [WF-004](WF-004-order-checkout-and-payment.md) |
| WF-005 Membership | order → user_membership (DB) | [WF-005](WF-005-membership-subscription.md) |
| WF-006 Authoring | editor → course/chapter/lesson (DB) | [WF-006](WF-006-course-authoring-and-publishing.md) |
| WF-007 Registration | form/OTP → user + token | [WF-007](WF-007-user-registration-and-onboarding.md) |
| WF-008 Forum | post → topic/reply (DB) + notification | [WF-008](WF-008-forum-discussion.md) |
| WF-009 Coupon | code → order discount (DB) | [WF-009](WF-009-coupon-discount-redemption.md) |
| WF-010 AI generation | prompt → ext provider → draft → entities | [WF-010](WF-010-ai-assisted-content-generation.md) |
