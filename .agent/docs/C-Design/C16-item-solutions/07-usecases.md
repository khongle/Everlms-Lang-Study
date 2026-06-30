# C16 Sheet - 07 usecases

C13 usecase → implementation work. Source: [C13](../C13-usecases/README.md).

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Learn | UC-WF-001-001 | controller + progress service | lms_progress | enroll/reuse + player + complete | C13 WF-001 | enroll/progress/dedupe |
| Quiz | UC-WF-002-001 | quiz controller + factory | lms_quiz | take + grade + pending/final result | C13 WF-002 | grade/pending/event |
| Cert | UC-WF-003-001 | event subscriber + mPDF + verification form | lms_certificate | issue/download/verify idempotently | C13 WF-003 | course-link/idempotency/verify |
| Buy | UC-WF-004-001 | order/payment/receipt forms + manager order list | lms_order/lms_payment | checkout + gateway + processing receipt review | C13 WF-004 | paid→access/receipt/filter/action |
| Subscribe | UC-WF-005-001 | membership + order | lms_membership/lms_order | plan→current active membership→course access | C13 WF-005 | entitlement/renewal/no duplicates |
| Author | UC-WF-006-001 | entity forms | lms_course | curriculum + publish | C13 WF-006 | hierarchy |
| Register | UC-WF-007-001 | core + OTP service | user/lms_mobile_api | verify→active | C13 WF-007 | otp/token |
| Forum | UC-WF-008-001 | forum forms | lms_forum | topic/reply | C13 WF-008 | CRUD |
| Coupon | UC-WF-009-001 | coupon service + discount fields | lms_order | validate/apply/persist | C13 WF-009 | valid/invalid/persist |
| AI | UC-WF-010-001 | AI service + review | lms_ai | prompt→save | C13 WF-010 | failure-path |
