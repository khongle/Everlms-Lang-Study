# C16 Sheet - 06 workflows

Workflow/state-machine solutions. Source: [C12](../C12-workflows/README.md).

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Learning | WF-001 | unique learner/course status field on `lms_user_course` | lms_progress | enroll/reuse→progress→complete | C12 WF-001 | uniqueness/rollup |
| Grading | WF-002 | factory + result entity | lms_quiz | strategy per type + pending/result-term source of truth + event | C12 WF-002 | grade/pending/event |
| Certification | WF-003 | EventSubscriber | lms_certificate | idempotent issue | C12 WF-003 | idempotency |
| Commerce | WF-004 | order status field + gateway/receipt form | lms_order/lms_payment | pending→processing→completed→entitlement | C12 WF-004 | state/receipt/⚠️R-002 |
| Membership | WF-005 | status field + dates + cron | lms_membership | active→expired | C12 WF-005 | entitlement |
| Authoring | WF-006 | publish status | lms_course | draft→published | C12 WF-006 | publish |
| Onboarding | WF-007 | core register + OTP service | user/lms_mobile_api | verify→active | C12 WF-007 | otp/role |
| Forum | WF-008 | status flags | lms_forum | open→resolved/locked | C12 WF-008 | moderation |
| Coupon | WF-009 | config-driven coupon service + discount fields on order | lms_order | validate→apply→persist | C12 WF-009 | valid/invalid/persist |
| AI gen | WF-010 | service + human review | lms_ai | prompt→draft→save | C12 WF-010 | failure-path |

> No Drupal core Workflow/Content Moderation module is used; states are status fields on entities.
