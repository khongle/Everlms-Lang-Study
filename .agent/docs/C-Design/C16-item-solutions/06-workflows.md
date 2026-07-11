# C16 Sheet - 06 workflows

Giải pháp cho workflow/state-machine. Nguồn: [C12](../C12-workflows/README.md).

| Concept | Item | Drupal primitive to use | Owner module | Implementation pattern | Source evidence | Tests/checks |
| ------- | ---- | ----------------------- | ------------ | ---------------------- | --------------- | ------------ |
| Learning | WF-001 | unique learner/course status field on `lms_user_course` | lms_progress | enroll/reuse→progress→complete | C12 WF-001 | uniqueness/rollup |
| Grading | WF-002 | factory + result entity | lms_quiz | strategy per type + pending/result-term source of truth + event | C12 WF-002 | grade/pending/event |
| Certification | WF-003 | EventSubscriber + quiz course reference | lms_certificate/lms_quiz | resolve course→issue PDF→verify; idempotent by learner/course | C12 WF-003 | course-link/idempotency/verify |
| Commerce | WF-004 | order status field + gateway/receipt form | lms_order/lms_payment | pending→processing queue→manager completed→entitlement | C12 WF-004 | state/receipt/filter/action/⚠️R-002 |
| Membership | WF-005 | status field + start/end dates | lms_membership/lms_order | active-current→extend/reactivate→expired | C12 WF-005 | entitlement/idempotency |
| Authoring | WF-006 | entity form + Inline Entity Form + publish status | lms_course/lms_chapter/lms_lesson | form-login authoring; draft course -> nested chapter/lesson -> publish -> catalog visibility; author list is own-scoped | C12 WF-006 | hierarchy/own-edit/catalog |
| Onboarding | WF-007 | core register + OTP service + expirable key-value token store | user/lms_base/lms_mobile_api | web verify-mail registration assigns student; mobile register creates blocked student -> resend OTP -> verify activates -> bearer token -> /me | C12 WF-007 | web role/mobile token/duplicate email |
| Forum | WF-008 | topic/reply entities + notification + cache tags | lms_forum/lms_notification | form-login users; topic -> reply -> owner notification -> best answer -> resolved; anonymous detail teaser and fresh public index | C12 WF-008 | CRUD/notification/cache |
| Coupon | WF-009 | config-driven coupon service + discount fields on order | lms_order | validate→apply→persist | C12 WF-009 | valid/invalid/persist |
| AI gen | WF-010 | provider plugin + private draft + entity hierarchy save | lms_ai | form-login prompt/provider -> draft preview -> save unpublished course/chapter/lesson/quiz -> queue lessons; unsupported provider fails with no save | C12 WF-010 | provider/hierarchy/failure-path |

> Không dùng module Workflow/Content Moderation của Drupal core; các state là status field trên entity.
