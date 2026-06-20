# D02 — Epic Spec: Commerce & Payment

**Epic ID:** E-05  
**Maps to draft:** G-COMMERCE  
**Features contributed to:** F-040 (Order Management), F-041 (Membership), F-042 (Payment Gateways), F-043 (Checkout), F-044 (Wishlist), F-045 (Bundle)  
**Owner:** SA (primary) + Agent_Implementer (executes Stories); CP4 signed off by SA  
**Services affected:** Commerce Service (lms_order, lms_membership, lms_payment, lms_stripe, lms_paypal, lms_wishlist, lms_bundle)  
**Dependencies:** E-01 (Foundation), E-02 (Content — lms_course entity for enrollment targets)  
**Estimated Duration:** 2 weeks  
**Status:** PARTIAL — entities and plugin host complete (as-built); checkout flow partially implemented; webhook handling, coupon system, and bundle checkout are target-state/planned

---

## Purpose

Deliver the complete commerce layer: Order and OrderItem entities, MembershipPlan and UserMembership entities, the PaymentGatewayManager plugin host with four gateway plugins (Stripe real PaymentIntent, BankTransfer manual, PayPal stub, ZaloPay stub), a Checkout flow with Stripe Elements and bank-transfer receipt upload, auto-enrollment on order completion, membership lifecycle management (activate/cancel/cron expiry), wishlist, and course bundle data model. Several features are target-state (webhook signature verification, coupon system, bundle checkout) and must be completed to reach production-readiness.

---

## Stories

| Story ID | Title | Type | Primary source | Owning module(s) | Status | Dependencies | Priority |
|----------|-------|------|----------------|-----------------|--------|--------------|----------|
| S-05-01 | lms_order — Order + OrderItem entities | module | modules-commerce.md; [C05-modules/lms_order.md](../../C-Design/C05-modules/lms_order.md) | lms_order | as-built | E-02 | Must |
| S-05-02 | lms_membership — MembershipPlan + UserMembership entities + lifecycle | module | modules-commerce.md; [C05-modules/lms_membership.md](../../C-Design/C05-modules/lms_membership.md) | lms_membership | as-built | E-01 | Must |
| S-05-03 | lms_payment — PaymentGatewayManager plugin host + BankTransfer + settings | module | modules-commerce.md; [C05-modules/lms_payment.md](../../C-Design/C05-modules/lms_payment.md) | lms_payment | as-built | E-01 | Must |
| S-05-04 | lms_stripe — Stripe PaymentIntent gateway plugin | module | modules-commerce.md; [C05-modules/lms_stripe.md](../../C-Design/C05-modules/lms_stripe.md) | lms_stripe | as-built (webhook stub) | S-05-03 | Must |
| S-05-05 | CheckoutForm + MembershipCheckoutForm + auto-enrolment hook | page | modules-commerce.md; [C05-modules/lms_order.md](../../C-Design/C05-modules/lms_order.md) | lms_order | as-built (security risk — see notes) | S-05-01, S-05-03, S-05-04 | Must |
| S-05-06 | lms_wishlist — toggle/status/list | module | modules-commerce.md; [C05-modules/lms_wishlist.md](../../C-Design/C05-modules/lms_wishlist.md) | lms_wishlist | as-built | E-02 | Should |
| S-05-07 | lms_bundle — CourseBundle entity (data model only) | module | modules-commerce.md; [C05-modules/lms_bundle.md](../../C-Design/C05-modules/lms_bundle.md) | lms_bundle | stub / data-model-only | E-02 | Could |
| S-05-08 | Webhook handler + signature verification | module | modules-commerce.md | lms_stripe, lms_payment | target-state / planned | S-05-04 | Must (RISK-004) |
| S-05-09 | lms_paypal — dead code cleanup + real PayPal integration | module | modules-commerce.md; [C05-modules/lms_paypal.md](../../C-Design/C05-modules/lms_paypal.md) | lms_paypal | dead-code / planned | S-05-03 | Could |

---

## Build Order Within Epic

1. **S-05-01, S-05-02, S-05-03** — Order, Membership, PaymentGatewayManager (parallel; no inter-dependencies)
2. **S-05-04** — StripeGateway plugin (depends on S-05-03)
3. **S-05-05** — CheckoutForm + auto-enrolment (depends on S-05-01, S-05-02, S-05-03, S-05-04)
4. **S-05-06** — lms_wishlist (depends on E-02; parallel with step 1)
5. **S-05-07** — lms_bundle entity (depends on E-02; can be done at any time)
6. **S-05-08** — Webhook handler (depends on S-05-04; **must implement sig verification per RISK-004**)
7. **S-05-09** — PayPal real integration (depends on S-05-03, S-05-08; after RISK-004 resolved)

---

## Story Details

### S-05-01 — lms_order — Order + OrderItem entities
**As-built.** `lms_order`: fields `field_order_number` (string 32), `field_items` (→ lms_order_item, unlimited), `field_total/subtotal/discount` (decimal), `field_currency`, `field_status` (pending/processing/completed/cancelled/refunded), `field_payment_method`, `field_payment_id`, `field_coupon_code`, `field_reminder_sent`, `field_payment_receipt` (image; added via update_10001). `lms_order_item`: `field_course`, `field_membership_plan`, `field_price`, `field_type` (course/bundle/membership). Auto-enrolment via `hook_entity_insert/update` (`_lms_order_process_completed_order`): creates lms_user_course + lms_user_membership + sends email (hook_mail `order_completed`) + in-app notification on status→completed. `OrderAccessControlHandler`: view only if owner; admins always allowed. Minor bug: `createAndFulfillFreeMembershipOrder()` calls `$order->set('field_items',...)` twice. Source: [C05-modules/lms_order.md](../../C-Design/C05-modules/lms_order.md).

### S-05-02 — lms_membership — MembershipPlan + UserMembership entities + lifecycle
**As-built.** `lms_membership_plan`: fields `name`, `field_price`, `field_duration_days` (default 30), `field_billing_cycle` (monthly/quarterly/yearly/lifetime), `field_features` (string, unlimited), `field_course_access` (all/selected), `field_trial_days`, `field_recommended`, `status` (active/inactive). `lms_user_membership`: fields `field_plan` (→ lms_membership_plan), `field_start_date`, `field_end_date`, `field_status` (active/trial/expired/cancelled), `field_auto_renew` (default TRUE). `MembershipManager::hasActiveMembership()`: checks `administer lms_course` OR active/trial with `field_end_date > now()`. `MembershipAccessSubscriber`: redirects `AccessDeniedHttpException` on `/learn/` or `/course/` paths to pricing page. `hook_cron`: expires memberships with `field_end_date <= now` and `field_auto_renew=FALSE`; defers auto-renewal to "payment gateway webhook" (TODO — not implemented). Admin routes at `/workspace/pricing` and `/workspace/user-memberships`. Source: [C05-modules/lms_membership.md](../../C-Design/C05-modules/lms_membership.md).

### S-05-03 — lms_payment — PaymentGatewayManager plugin host + BankTransfer + settings
**As-built.** Plugin system: `PaymentGatewayManager` (DefaultPluginManager), `@PaymentGateway` annotation, `PaymentGatewayInterface` (Plugin namespace: `processPayment`, `handleWebhook`, `getLabel`). **Important:** Two separate interfaces exist — `Plugin\PaymentGatewayInterface` (used by plugin system) and `Service\PaymentGatewayInterface` (used only by lms_paypal dead code). `PaymentGatewayBase`: default implementations are stubs (processPayment returns `status=failed`; handleWebhook returns 500). `BankTransferGateway` (id: `bank_transfer`): processPayment returns `status=pending, transaction_id=MANUAL_{id}`. `PayPalGateway` in lms_payment (id: `paypal`): processPayment does NOT verify with PayPal API — stub. `ZaloPayGateway` (id: `zalopay`): returns `status=redirect` with hardcoded sandbox URL — stub. `PaymentSettingsForm` at `/admin/config/everlms/payment`. `PaymentManager` (service registry): exists in code but NOT registered in services.yml — not wired into DI container. **SECURITY:** Stripe, PayPal, ZaloPay API keys stored in `lms_payment.settings` config YAML — «secret in config — see security risk». Source: [C05-modules/lms_payment.md](../../C-Design/C05-modules/lms_payment.md).

### S-05-04 — lms_stripe — Stripe PaymentIntent gateway plugin
**As-built (webhook stub).** `StripeGateway` (id: `stripe`): `processPayment()` retrieves PaymentIntent from Stripe SDK and checks `status === 'succeeded'` — REAL. `handleWebhook()`: logs receipt, returns 200, `// TODO: Implement webhook verification.` — **STUB, no signature verification**. Stripe flow: `CheckoutForm::buildForm()` creates PaymentIntent server-side (`\Stripe\PaymentIntent::create()`) — one per page load including failed loads (**RISK**: orphaned PaymentIntents on every page render); `client_secret` passed to browser via drupalSettings; Stripe Elements JS confirms payment; `payment_intent_id` POSTed back; `StripeGateway::processPayment()` retrieves and verifies. Source: [C05-modules/lms_stripe.md](../../C-Design/C05-modules/lms_stripe.md).

### S-05-05 — CheckoutForm + MembershipCheckoutForm + auto-enrolment hook
**As-built with security risk.** `CheckoutController`: loads course/plan, redirects anonymous to login, handles free (immediate fulfilment) vs paid (form). `CheckoutForm`/`MembershipCheckoutForm`: renders payment method radios from config; Stripe Elements for Stripe; `BankTransferForm` with receipt upload (managed_file, 5MB, stores to `public://payment_receipts/YYYY-MM`). `submitForm()` dispatches to plugin manager. Auto-enrolment in `lms_order.module` hook_entity_insert/update. **SECURITY RISK (RISK-004):** Stripe PaymentIntent created in `buildForm()` — fires on every page render including accidental navigation, creating orphaned Stripe PaymentIntents. PaymentIntent should be created on form submission, not form build. Source: [C05-modules/lms_order.md](../../C-Design/C05-modules/lms_order.md).

### S-05-06 — lms_wishlist — toggle/status/list
**As-built.** Entity `lms_wishlist` (fields: `field_course` → lms_course). Routes: `/api/lms/wishlist/toggle` (POST), `/api/lms/wishlist/status` (GET), `/my-wishlist` (GET). Toggle: upsert with cache tag invalidation `lms_wishlist:{uid}`. Status endpoint: returns `{course_id: bool}` map. My Wishlist: builds rich course card data including enrollment check. Source: [C05-modules/lms_wishlist.md](../../C-Design/C05-modules/lms_wishlist.md).

### S-05-07 — lms_bundle — CourseBundle entity (data model only)
**Stub / data-model-only.** Entity `lms_course_bundle` (fields: `name`, `field_description`, `field_courses` → lms_course unlimited, `field_price`, `field_discount_percent`, `field_thumbnail`, `status`). No routes, no checkout integration, no controllers, no templates. Admin CRUD via auto-generated routes only (`/manage/bundles`). `lms_order_item.field_type` allows 'bundle' value but no code creates bundle order items. Source: [C05-modules/lms_bundle.md](../../C-Design/C05-modules/lms_bundle.md).

### S-05-08 — Webhook handler + signature verification
**Target-state / planned.** No webhook routes exist in any module. Stripe, PayPal, and ZaloPay cannot receive async callbacks. Must implement: dedicated routes (`/webhook/stripe`, `/webhook/paypal`, `/webhook/zalopay`), `\Stripe\WebhookSignature::verifyHeader()` for Stripe, HMAC verification for ZaloPay. Idempotency: duplicate events must not double-enrol or double-fulfil. Required before any production payment processing. This is **RISK-004**.

### S-05-09 — lms_paypal — dead code cleanup + real PayPal integration
**Dead-code / planned.** `lms_paypal\PayPalGateway`: implements `Service\PaymentGatewayInterface` (wrong interface), has no `@PaymentGateway` annotation, never discovered by plugin manager. All 3 methods are TODO stubs. The active PayPal plugin is `lms_payment\Plugin\PaymentGateway\PayPalGateway` which also does not verify with PayPal API. **This story** = clean up lms_paypal dead code AND implement real PayPal REST API order create/capture/verify. Depends on S-05-08 for webhook infrastructure.

---

## Affected Modules

| Module | Role in Epic | Status |
|--------|-------------|--------|
| lms_order | Order + OrderItem entities, checkout routes, auto-enrolment hook | as-built |
| lms_membership | Plan + UserMembership entities, lifecycle, admin UI | as-built |
| lms_payment | PaymentGatewayManager plugin host, BankTransfer, PayPal stub, ZaloPay stub, settings form | as-built (stubs) |
| lms_stripe | Stripe PaymentIntent gateway plugin | as-built (webhook stub) |
| lms_paypal | PayPal gateway — DEAD CODE, wrong interface, no annotation | dead-code |
| lms_wishlist | Wishlist toggle/status/list | as-built |
| lms_bundle | CourseBundle entity — data model only | stub |

---

## Known Issues / Risks (log to E02-issues/)

| Issue | Severity | Story | Description |
|-------|----------|-------|-------------|
| RISK-004: No webhook routes | Critical | S-05-08 | No webhook endpoints exist; async payment confirmations cannot be received |
| RISK-004: Stripe PaymentIntent on form BUILD | High | S-05-05 | `CheckoutForm::buildForm()` creates PaymentIntent on every page render; orphaned intents accumulate |
| RISK-004: Stripe webhook signature not verified | High | S-05-04 | `StripeGateway::handleWebhook()` is a stub; no `WebhookSignature::verifyHeader()` call |
| lms_paypal dead code | Medium | S-05-09 | `lms_paypal\PayPalGateway` implements wrong interface, never instantiated; also a PayPal stub in lms_payment |
| ZaloPay hardcoded sandbox URL | High | S-05-03 | `ZaloPayGateway` returns sandbox redirect URL; cannot be used in production |
| Secrets in config YAML | Critical | S-05-03 | Stripe keys, PayPal creds in `lms_payment.settings` YAML — «secret in config — see security risk»; no Key module |
| .env with test Stripe/PayPal creds committed | Critical | S-05-03 | `.env` file committed to git with test credentials — «secret in config — see security risk» |
| createAndFulfillFreeMembershipOrder double set | Low | S-05-01 | `$order->set('field_items',...)` called twice |

---

## Acceptance Criteria (from B05)

| AC ID | Scenario | Feature | Contributing Story(ies) |
|-------|----------|---------|-------------------------|
| AC-040 | Free course enrollment creates lms_user_course immediately | F-040 | S-05-05, S-05-01 |
| AC-041 | Paid course: Stripe payment completes; order status→completed; student enrolled | F-043 | S-05-05, S-05-04 |
| AC-042 | Bank transfer: student uploads receipt; order stays pending until admin approves | F-043 | S-05-05 |
| AC-043 | Membership cron expires memberships with field_end_date < now and auto_renew=FALSE | F-041 | S-05-02 |
| AC-044 | Wishlist toggle adds/removes course; My Wishlist page reflects state | F-044 | S-05-06 |
| AC-045 | Webhook signature verification rejects tampered payloads (planned) | F-042 | S-05-08 |

---

## Integration Points (from B06)

| INT ID | Integration | Contributing Story(ies) | Notes |
|--------|------------|-------------------------|-------|
| INT-040 | lms_order hook → lms_user_course (E-04 enrolment) | S-05-01 | On order status=completed |
| INT-041 | lms_order hook → lms_user_membership (E-05 membership grant) | S-05-01 | On order status=completed, field_type=membership |
| INT-042 | lms_course.field_price_type → checkout route guard | S-05-05 | free=immediate; paid=checkout; membership=membership check |
| INT-043 | MembershipManager::hasActiveMembership → lms_course hook_entity_access | S-05-02 | Called from lms_course.module to gate membership-type lessons |
| INT-044 | Stripe SDK (stripe/stripe-php Composer) → StripeGateway | S-05-04 | Requires stripe/stripe-php installed |

---

## Test Requirements

- **Unit + integration tests per Story:** coverage >= 70% per Story; payment gateway plugins must have unit tests with mocked API responses
- **Cross-Story integration tests:** Free course checkout creates enrolment; paid Stripe checkout flow (mocked Stripe API) results in completed order and enrolment; membership expiry cron test; wishlist toggle idempotency
- **Cross-Story Integration Test Owner:** `S-05-05` (CheckoutController orchestrates all gateway plugins and enrolment)
- **Acceptance tests:** AC-040 through AC-044 (AC-045 pending S-05-08 implementation)

---

## Feature Flag Plan

| Flag ID | Story | Description | Risk Level | Rollout Strategy |
|---------|-------|-------------|-----------|-----------------|
| FF_FEAT_WEBHOOK_STRIPE | S-05-08 | Enable Stripe webhook endpoint and sig verification | High | 0% until implemented; staged rollout |
| FF_FEAT_PAYPAL_LIVE | S-05-09 | Enable real PayPal REST API calls | High | 0% until dead code replaced; staged rollout |
| FF_FEAT_ZALOPAY_LIVE | S-05-03 | Enable real ZaloPay integration (remove sandbox URL) | High | 0% until implemented |
| FF_FEAT_BUNDLE_CHECKOUT | S-05-07 | Enable bundle purchase flow | Medium | 0% until checkout integration built |

---

## CP4 Readiness Criteria

- [x] S-05-01 through S-05-06 have status: DONE (as-built)
- [ ] S-05-07 status: stub only — bundle checkout NOT complete
- [ ] S-05-08 status: NOT STARTED — webhook handler required before production
- [ ] S-05-09 status: dead-code cleanup required
- [ ] G1-G4 passed for completed Stories
- [ ] Cross-Story integration tests within Epic passing (partial — S-05-08/09 blocked)
- [ ] D07 code-map delta complete for all files touched in this Epic
- [ ] E05 traceability complete for every contributed Feature
- [ ] E07 epic retrospective written (E07-retrospectives/) — pending
- [ ] RISK-004 (webhook, Stripe PaymentIntent on build, secrets in YAML) logged in E02-issues/ and G02-risk-register.md
- [ ] Feature flags registered for all FF_FEAT_* above
- [ ] Every Story that touched code/config/UI lists relevant D00-conventions/ files
