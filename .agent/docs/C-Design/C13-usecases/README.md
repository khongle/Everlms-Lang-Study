# C13-usecases - Workflow Usecase Index

One subfolder per [C12 workflow](../C12-workflows/README.md) (same WF slug), each with a README + ≥1 usecase (`UC-WF-NNN-NNN`). Usecases decompose a workflow into implementable units that D03 stories close against.

## Workflow folders
| Workflow | Folder | Usecases | Modules | C12 |
| -------- | ------ | -------- | ------- | --- |
| WF-001 Enrollment & learning | [WF-001-enrollment-and-learning/](WF-001-enrollment-and-learning/) | UC-001 | lms_progress | [C12](../C12-workflows/WF-001-enrollment-and-learning.md) |
| WF-002 Quiz attempt & grading | [WF-002-quiz-attempt-and-grading/](WF-002-quiz-attempt-and-grading/) | UC-001 | lms_quiz | [C12](../C12-workflows/WF-002-quiz-attempt-and-grading.md) |
| WF-003 Certificate issuance | [WF-003-certificate-issuance/](WF-003-certificate-issuance/) | UC-001 | lms_certificate | [C12](../C12-workflows/WF-003-certificate-issuance.md) |
| WF-004 Order checkout & payment | [WF-004-order-checkout-and-payment/](WF-004-order-checkout-and-payment/) | UC-001 | lms_order | [C12](../C12-workflows/WF-004-order-checkout-and-payment.md) |
| WF-005 Membership subscription | [WF-005-membership-subscription/](WF-005-membership-subscription/) | UC-001 | lms_membership | [C12](../C12-workflows/WF-005-membership-subscription.md) |
| WF-006 Course authoring | [WF-006-course-authoring-and-publishing/](WF-006-course-authoring-and-publishing/) | UC-001 | lms_course | [C12](../C12-workflows/WF-006-course-authoring-and-publishing.md) |
| WF-007 User registration | [WF-007-user-registration-and-onboarding/](WF-007-user-registration-and-onboarding/) | UC-001 | lms_mobile_api | [C12](../C12-workflows/WF-007-user-registration-and-onboarding.md) |
| WF-008 Forum discussion | [WF-008-forum-discussion/](WF-008-forum-discussion/) | UC-001 | lms_forum | [C12](../C12-workflows/WF-008-forum-discussion.md) |
| WF-009 Coupon redemption | [WF-009-coupon-discount-redemption/](WF-009-coupon-discount-redemption/) | UC-001 | lms_order | [C12](../C12-workflows/WF-009-coupon-discount-redemption.md) |
| WF-010 AI content generation | [WF-010-ai-assisted-content-generation/](WF-010-ai-assisted-content-generation/) | UC-001 | lms_ai | [C12](../C12-workflows/WF-010-ai-assisted-content-generation.md) |

## Authoring contract
- Folder per C12 workflow (same slug); each has a README + `UC-WF-NNN-NNN-<slug>.md`.
- Each UC names ≥1 entry page (C11 `P###`), actors, main success scenario, and acceptance/test contract.
