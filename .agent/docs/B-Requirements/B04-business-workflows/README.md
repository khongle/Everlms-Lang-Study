# B04 — Business Workflows Index

> Roll-up of all `WF-{NNN}.md` files in this layer. Source of truth is the per-workflow file.
> Scope: only the six flows materialized here (WF-001 through WF-005 plus index). Additional workflows (course creation, user registration, forum, coupon) are in the draft tree and not yet promoted.

| ID | Workflow | Status | Key Modules | Features Touched |
|----|----------|--------|-------------|-----------------|
| [WF-001](./WF-001-enrollment-learning.md) | Enrollment & Learning | APPROVED | lms_progress, lms_course | F-001, F-003, F-004 |
| [WF-002](./WF-002-quiz-grading.md) | Quiz Submit & Grading | APPROVED | lms_quiz, lms_question | F-002, F-003 |
| [WF-003](./WF-003-certificate-issuance.md) | Certificate Issuance | APPROVED | lms_certificate | F-005 |
| [WF-004](./WF-004-order-management.md) | Order & Checkout | APPROVED | lms_order, lms_payment, lms_stripe | F-004 |
| [WF-005](./WF-005-membership-subscription.md) | Membership Subscription | APPROVED | lms_membership, lms_order | F-004 |

---

## Cross-Workflow Integration Points (as-built)

| Trigger point | Source module | Consuming module | Mechanism |
|--------------|--------------|-----------------|-----------|
| Quiz passed | `lms_quiz` dispatches `QuizPassedEvent` | `lms_certificate` `CertificateEventListener` | Symfony event subscriber |
| Quiz passed | `lms_quiz` dispatches `QuizPassedEvent` | `lms_notification` `NotificationEventSubscriber` | Symfony event subscriber |
| Order completed | `lms_order.module` `hook_entity_insert/update` | Same module helper `_lms_order_process_completed_order()` | Drupal entity hook |
| Lesson accessed | `lms_progress` `UserPortalController::lessonView()` | Auto-enroll creates `lms_user_course` | Direct service call |
| Forum reply saved | `lms_forum` `ForumReplyForm::save()` | `lms_notification.manager::dispatch('forum_reply')` | Direct service call |

---

## Known Risk Summary (cross-workflow)

| Risk | Status | Affected Workflows |
|------|--------|--------------------|
| No webhook endpoints for Stripe, PayPal, ZaloPay | **stub / not implemented** | WF-004, WF-005 |
| `lms_paypal` module is dead code (wrong interface, no annotation) | **dead-code** | WF-004 |
| Stripe `PaymentIntent::create()` called on form BUILD (not submit) | **bug** | WF-004 |
| ZaloPay gateway uses hardcoded sandbox URL | **stub** | WF-004 |
| Essay grading leaves `QuizResult` in `pending` state with no instructor workflow route | **stub** | WF-002 |
| `lms_badge` has zero award logic | **stub** | WF-003 |
| Notification email/push channels are log-only stubs | **stub** | WF-001–WF-005 |
| Membership auto-renewal via webhook is deferred (logged, not implemented) | **planned** | WF-005 |

---

## Related Docs

- [B03 Features](../B03-features/) — workflows map back to feature IDs
- [B05 Acceptance Criteria](../B05-acceptance-criteria/) — testable criteria per workflow
- [B06 Integration Requirements](../B06-integration-requirements/) — external systems
