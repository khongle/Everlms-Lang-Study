# S04-data-flow - Data Flow

Data movement per workflow, mirroring [B04](../../B-Requirements/B04-business-workflows/_index.md)/[C12](../../C-Design/C12-workflows/README.md) by `WF-NNN`. Representative high-data-movement flows have files; all 10 map below (the rest follow their C12 state model with no cross-service data movement beyond the DB).

| WF | Data path | File |
| -- | --------- | ---- |
| WF-001 Enrollment & learning | user → lms_user_course/lms_user_lesson (DB) | [WF-001](WF-001-enrollment-and-learning.md) |
| WF-002 Quiz grading | attempt → quiz_result/responses (DB) | C12 |
| WF-003 Certificate issuance | event → mPDF → user_certificate + file | [WF-003](WF-003-certificate-issuance.md) |
| WF-004 Order checkout | order → gateway (ext) → entitlement (DB) | [WF-004](WF-004-order-checkout-and-payment.md) |
| WF-005 Membership | order → user_membership (DB) | C12 |
| WF-006 Authoring | editor → course/chapter/lesson (DB) | C12 |
| WF-007 Registration | form/OTP → user + token | C12 |
| WF-008 Forum | post → topic/reply (DB) + notification | C12 |
| WF-009 Coupon | code → order discount (DB) | C12 |
| WF-010 AI generation | prompt → ext provider → draft → entities | C12 |
