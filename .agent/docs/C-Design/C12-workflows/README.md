# C12 — Workflow Design

**Status**: Drafted 2026-06-20 from B04 business workflows, facts bases (modules-assessment, modules-progress-comms, modules-cert-integration, modules-commerce, modules-content, modules-foundation, modules-mobile), and live code scan. WF-006–WF-009 added 2026-06-20.

## Purpose

Design-level workflow specifications that bridge [B04 business workflows](../../B-Requirements/B04-business-workflows/) to the concrete implementation surfaces: entities, routes, controllers, services, events, and cache tags. Each WF file is the authoritative design contract the implementer follows — it absorbs B04 ambiguities, records drift resolutions, and identifies known bugs and stubs so they are not relitigated.

## Current Design Posture

Drift and design decisions that deviate from or clarify the B04 layer:

| Area | C12 stance | Source |
|---|---|---|
| Course creation — no rollback on AI failure | AI-path partial failures leave orphaned draft entities; no saga/compensation pattern | `lms_ai/CourseBuilderService`; facts: modules-content |
| Mobile auth — custom bearer token | NOT OAuth2/JWT; custom `keyvalue.expirable` token (7-day TTL, no refresh). `oauth_client_id`/`oauth_client_secret` in settings.yml are unused artifacts | `lms_mobile_api/MobileAuthController`; facts: modules-mobile |
| Mobile auth header conflict | `X-Authorization` checked before `Authorization` to work around Basic Auth server gates | `getAuthenticatedUser()` in all 3 mobile controllers |
| Social/OAuth login | Not implemented — draft B04 WF-007 reference removed | No OAuth module installed |
| Forum — active controller | `LmsForumController` is the real controller; `RouteSubscriber` overrides canonical route | `lms_forum/Routing/RouteSubscriber.php` |
| Forum — dead `ForumController` | `ForumController::viewTopic()` starts with `throw new \Exception("HARD DEBUG!")` — not routed but HTTP 500 risk | `lms_forum/src/Controller/ForumController.php` |
| Coupon flow — entirely target-state | Only `field_coupon_code` and `field_discount` scaffold exist on `lms_order`; `CheckoutForm` never reads or sets them; no `lms_coupon` entity exists | `lms_order/src/Entity/Order.php`, `lms_order/src/Form/CheckoutForm.php` |
| Stripe PaymentIntent timing | Created in `buildForm()` on every page load — known bug; target-state is `submitForm()` only | Facts: modules-commerce; B04/WF-004 |
| PayPal gateway (lms_paypal) | `lms_paypal\PayPalGateway` is dead code — wrong interface, no `@PaymentGateway` annotation, never discovered | Facts: modules-commerce |
| Webhook handling | No webhook routes exist in any module; Stripe, PayPal, ZaloPay cannot receive async events | Facts: modules-commerce |
| Essay grading | `EssayStrategy` always returns `pending_review=TRUE`; `QuizPassedEvent` blocked; no instructor grading route exists | Facts: modules-assessment |
| Auto-renewal | `field_auto_renew=TRUE` stored but cron defers to webhook that doesn't exist; auto-renewal is effectively unimplemented | Facts: modules-commerce |
| Certificate notification | `CertificateEventListener` issues no notification; `NotificationEventSubscriber` sends `quiz_result` type on `QuizPassedEvent` only | Facts: modules-cert-integration, modules-progress-comms |
| Badge award logic | `lms_badge` has entities only; zero award logic anywhere — stub | Facts: modules-cert-integration |
| `field_start_time` accuracy | `QuizResult.field_start_time` hardcoded to `request_time - 60`; not a real elapsed timer | Facts: modules-assessment |
| `field_expiry_date` type | `lms_user_certificate.field_expiry_date` declared as `datetime` but stored as `varchar` in DB — data integrity bug | Facts: modules-cert-integration |
| lms_notification channels | In-app: REAL. Email: logs-only stub (TODO in source). Push: logs-only stub. | Facts: modules-progress-comms |
| Auto-enroll on lesson access | `lessonView()` creates `lms_user_course` if none found — free courses can bypass checkout | Facts: modules-progress-comms |

## Workflow Index

| ID | Workflow | Main Actors | Primary Modules |
|---|---|---|---|
| [WF-001](WF-001-enrollment-learning.md) | Enrollment & Learning | Student, System | lms_progress, lms_order |
| [WF-002](WF-002-quiz-grading.md) | Quiz Submit & Grading | Student, System | lms_quiz, lms_question |
| [WF-003](WF-003-certificate-issuance.md) | Certificate Issuance | System, Student, Admin | lms_certificate, lms_quiz |
| [WF-004](WF-004-order-checkout.md) | Order & Checkout | Student, System, Gateways | lms_order, lms_payment, lms_stripe |
| [WF-005](WF-005-membership-subscription.md) | Membership Subscription | Student, System, Cron, Admin | lms_membership, lms_order |
| [WF-006](WF-006-course-creation.md) | Course Creation (Manual + AI) | Admin, Manager, System, AI Provider | lms_course, lms_chapter, lms_lesson, lms_ai, lms_zoom |
| [WF-007](WF-007-user-registration.md) | User Registration and Login | Visitor, System | lms_base, lms_mobile_api |
| [WF-008](WF-008-forum-flow.md) | Forum Topic and Reply Flow | Authenticated users, Admin, System | lms_forum, lms_notification |
| [WF-009](WF-009-coupon-flow.md) | Coupon Creation and Redemption (target-state) | Admin, Student, System | lms_order, lms_coupon (not yet built) |

## Legacy or Deferred Workflows

| Workflow | Status | Reason |
|---|---|---|
| Instructor essay grading | Deferred — not implemented | No instructor-facing grading route in lms_quiz; EssayStrategy always blocks QuizPassedEvent |
| Webhook payment confirmation | Planned — not implemented | No webhook routes exist in any module; all async payment events are lost |
| Auto-renewal billing | Planned — not implemented | Cron identifies renewal candidates but defers to webhook; no charge occurs |
| Badge award | Stub — not implemented | lms_badge has entities only; no criteria evaluator, no award trigger |
| Push notification delivery | Stub — not implemented | NotificationManager::dispatch() for push channel logs only |
| Coupon feature (lms_coupon entity, validation service, CRUD) | Deferred — not implemented | Only field scaffold on lms_order; no entity, no service, no checkout integration |
| Forum moderation / report queue | Deferred — not implemented | B04 draft described moderator flag/report queue; absent in lms_forum |
| Social / OAuth login | Removed from scope — not implemented | No OAuth module; unused settings.yml keys are config artifacts |

## Shared Workflow Rules

- **Authentication gate**: all learner routes require `_user_is_logged_in: TRUE`. Anonymous requests are redirected to Drupal login.
- **Transactional entities are private by default**: `lms_order`, `lms_user_course`, `lms_user_membership`, `lms_quiz_result`, `lms_user_certificate` are owner-filtered; admin access requires explicit permission.
- **State transitions emit domain events**: `QuizPassedEvent` is the primary cross-module signal. `_lms_order_process_completed_order()` is the enrollment trigger. Subscribe to these; do not poll tables.
- **Email is best-effort**: `hook_mail` delivery failure logs an error but does not roll back the transactional entity. In-app notification is created regardless.
- **No webhook infrastructure**: design decisions that assume Stripe/PayPal/ZaloPay async callbacks are blocked. All payment verification is synchronous.
- **Cache invalidation**: `lms_notification_list` tag governs the notification bell block. `lms_wishlist:{uid}` governs wishlist caches. Per-lesson notes use `lms_note:{lesson_id}:{uid}`.
- **Secrets**: all third-party API keys (Stripe, OpenAI, Gemini, Zoom, PayPal, Google) are stored in committed config YAML — no Key module. This is a «secret in config — see security risk» pattern that must not be documented with actual values.

## Traceability

| B04 Workflow | C12 File | Drift / Notes |
|---|---|---|
| [WF-001 Enrollment & Learning](../../B-Requirements/B04-business-workflows/WF-001-enrollment-learning.md) | [WF-001](WF-001-enrollment-learning.md) | Auto-enroll on `lessonView()` added; email-only enrollment notification (push stub) clarified |
| [WF-002 Quiz Submit & Grading](../../B-Requirements/B04-business-workflows/WF-002-quiz-grading.md) | [WF-002](WF-002-quiz-grading.md) | Grading factory is static PHP, not Drupal Plugin; essay → Pending blocks QuizPassedEvent |
| [WF-003 Certificate Issuance](../../B-Requirements/B04-business-workflows/WF-003-certificate-issuance.md) | [WF-003](WF-003-certificate-issuance.md) | Traversal path Quiz→Lesson→Chapter→Course mapped; field_expiry_date type bug documented |
| [WF-004 Order Management](../../B-Requirements/B04-business-workflows/WF-004-order-management.md) | [WF-004](WF-004-order-checkout.md) | PaymentIntent-on-buildForm bug documented; lms_paypal dead code clarified; no webhooks |
| [WF-005 Membership Subscription](../../B-Requirements/B04-business-workflows/WF-005-membership-subscription.md) | [WF-005](WF-005-membership-subscription.md) | Auto-renewal stub documented; cancelled status not set by any code; trial "one per user" gap |
| [WF-006 Course Creation](../../B-Requirements/B04-business-workflows/WF-006-course-creation.md) | [WF-006](WF-006-course-creation.md) | B04 text matches code; AI path fully as-built; no rollback on partial AI failure documented |
| [WF-007 User Registration and Login](../../B-Requirements/B04-business-workflows/WF-007-user-registration.md) | [WF-007](WF-007-user-registration.md) | Social login section removed (not implemented); OTP + custom bearer token flow documented as-built |
| [WF-008 Forum Flow](../../B-Requirements/B04-business-workflows/WF-008-forum-flow.md) | [WF-008](WF-008-forum-flow.md) | Moderation queue removed (not implemented); dead ForumController and debug messages documented as risks |
| [WF-009 Coupon Flow](../../B-Requirements/B04-business-workflows/WF-009-coupon-flow.md) | [WF-009](WF-009-coupon-flow.md) | Entirely target-state; only data scaffold confirmed; CheckoutForm verified to not set coupon fields |

## Related

- Requirements: [B04 business workflows](../../B-Requirements/B04-business-workflows/), [B05 acceptance criteria](../../B-Requirements/B05-acceptance-criteria/)
- Design: [C02 roles](../C02-user-roles.md), [C04 entities](../C04-entities/), [C05 modules](../C05-modules/), [C07 sitemap](../C07-sitemap.md), [C11 pages](../C11-pages-components/)
- Operations: H02 runbooks (planned, layer H — not yet materialized), H05 feature flags (planned, layer H — not yet materialized)
