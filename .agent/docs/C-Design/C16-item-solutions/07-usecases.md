# C16 Sheet - 07 usecases

C13 usecase → implementation work. Source: [C13](../C13-usecases/README.md).

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Learn | UC-WF-001-001 | controller + progress service | lms_progress | enroll/reuse + player + complete | C13 WF-001 | enroll/progress/dedupe |
| Quiz | UC-WF-002-001 | quiz controller + factory | lms_quiz | take + grade + pending/final result | C13 WF-002 | grade/pending/event |
| Cert | UC-WF-003-001 | event subscriber + mPDF + verification form | lms_certificate | issue/download/verify idempotently | C13 WF-003 | course-link/idempotency/verify |
| Buy | UC-WF-004-001 | order/payment/receipt forms + manager order list | lms_order/lms_payment | checkout + gateway + processing receipt review | C13 WF-004 | paid→access/receipt/filter/action |
| Subscribe | UC-WF-005-001 | membership + order | lms_membership/lms_order | plan→current active membership→course access | C13 WF-005 | entitlement/renewal/no duplicates |
| Author | UC-WF-006-001 | entity forms + Inline Entity Form + public detail tabs | lms_course/lms_chapter/lms_lesson | login form -> create draft -> chapter -> lesson -> publish -> catalog/detail Curriculum tab | C13 WF-006 | role login/hierarchy/catalog |
| Register | UC-WF-007-001 | core user form + OTP API endpoints | user/lms_base/lms_mobile_api | /user/register active student + email instructions; /api/v1 user register/resend/verify -> token and /me role | C13 WF-007 | screenshots/API responses/DB roles |
| Forum | UC-WF-008-001 | forum entity forms + owner action + public teaser | lms_forum/lms_notification | /forum list, /forum/new topic, second-user reply, owner best answer, notification, anonymous fresh index | C13 WF-008 | screenshots/DB/cache |
| Coupon | UC-WF-009-001 | coupon service + discount fields | lms_order | validate/apply/persist | C13 WF-009 | valid/invalid/persist |
| AI | UC-WF-010-001 | AI service + private review draft | lms_ai | prompt/provider -> preview -> save draft course hierarchy -> edit/list verify | C13 WF-010 | screenshots/DB/failure |
