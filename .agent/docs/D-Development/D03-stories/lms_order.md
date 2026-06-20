# D03 — Story Spec: lms_order

**Story ID:** lms_order
**Story type:** module
**Title:** Order Management — Checkout, Payment Dispatch, and Auto-Enrollment
**Primary source:** `C-Design/C05-modules/lms_order.md`
**Owning module(s):** lms_order
**Service(s):** N/A (no custom services registered; lms_payment plugin manager consumed)
**Epic:** E-05 (Commerce & Payment)
**Contributes to Feature(s):** F-004 (Order Management), F-005 (Payment Integration)
**Design source(s):** `../C05-modules/lms_order.md`
**Round:** N/A
**Driver:** SA directive
**Status:** DONE
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_order`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 3
**Splitting strategy:**
- S1: Entity definitions (Order, OrderItem), permissions, access handler, list builder
- S2: CheckoutController, CheckoutForm, MembershipCheckoutForm, BankTransferForm, Stripe Elements library
- S3: hook_entity_insert/update (auto-enrollment), hook_mail, update_10001 (field_payment_receipt), templates

## Scope (from primary source)

Implements purchase transactions in EverLMS. A user selects a course or membership plan and is directed to `/checkout/{course_id}` or `/checkout/membership/{plan_id}`. Free items are fulfilled immediately; paid items render a payment form that dispatches to the configured `PaymentGatewayManager` plugin. On order completion (`field_status = completed`), `hook_entity_insert`/`hook_entity_update` auto-enroll the user (creates `lms_user_course` or `lms_user_membership`) and send a confirmation email and in-app notification.

**Known bug (as-built):** `CheckoutForm::buildForm()` and `MembershipCheckoutForm::buildForm()` call `\Stripe\PaymentIntent::create()` on every page load — one PaymentIntent is created per page load even before the user submits. This causes Stripe balance pollution and user-visible key mismatches on back-navigation. See [E02 issue: Stripe PaymentIntent created at form build time].

**Minor bug (as-built):** `CheckoutController::createAndFulfillFreeMembershipOrder()` calls `$order->set('field_items', ...)` twice — second call overwrites the first (effectively sets it to a single-item array, which is correct in the current flow but fragile).

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_order.md` | Owning module design |
| `../../B-Requirements/B03-features/04-order-membership.md` | Feature requirements |
| `../../B-Requirements/B05-acceptance-criteria/` | User-facing AC |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Checkout flow for course and membership plan purchases | B03-features/04-order-membership | Acceptance |
| Auto-enrollment when order status transitions to `completed` | B03-features/04-order-membership | Acceptance |
| Bank transfer receipt upload | B03-features/04-order-membership | Acceptance |
| Stripe card payment via PaymentIntent confirm flow | B03-features/05-content-media | Acceptance |
| Email confirmation on order completion | B03-features/08-communication | Acceptance |
| Admins may view/edit/approve all orders; users may view own orders only | B03-features/07-user-management | Acceptance |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_order/lms_order.info.yml` | Create | Module declaration | — | S1 |
| `web/modules/custom/lms_order/lms_order.permissions.yml` | Create | administer/create/view own lms_order | — | S1 |
| `web/modules/custom/lms_order/lms_order.routing.yml` | Create | checkout, checkout_membership, bank_transfer routes | — | S1 |
| `web/modules/custom/lms_order/lms_order.libraries.yml` | Create | stripe_elements library (Stripe.js v3 + local JS) | — | S2 |
| `web/modules/custom/lms_order/lms_order.module` | Create | hook_entity_insert/update, hook_theme, hook_mail | — | S3 |
| `web/modules/custom/lms_order/lms_order.install` | Create | update_10001 — adds field_payment_receipt | — | S3 |
| `web/modules/custom/lms_order/src/Entity/Order.php` | Create | Order entity (EntityOwnerInterface) | — | S1 |
| `web/modules/custom/lms_order/src/Entity/OrderItem.php` | Create | OrderItem entity | — | S1 |
| `web/modules/custom/lms_order/src/Access/OrderAccessControlHandler.php` | Create | Admin always; view = owner only | — | S1 |
| `web/modules/custom/lms_order/src/OrderListBuilder.php` | Create | Admin list with status colour-coding | — | S1 |
| `web/modules/custom/lms_order/src/Controller/CheckoutController.php` | Create | Free-order fast-path + paid form dispatch | — | S2 |
| `web/modules/custom/lms_order/src/Form/CheckoutForm.php` | Create | Stripe PaymentIntent build + submit dispatch | — | S2 |
| `web/modules/custom/lms_order/src/Form/MembershipCheckoutForm.php` | Create | Same as CheckoutForm for membership plans | — | S2 |
| `web/modules/custom/lms_order/src/Form/BankTransferForm.php` | Create | VietQR display + receipt upload | — | S2 |
| `web/modules/custom/lms_order/js/stripe_elements.js` | Create | Client-side Stripe Elements confirm | — | S2 |
| `web/modules/custom/lms_order/templates/lms-checkout.html.twig` | Create | Checkout page template | — | S3 |
| `web/modules/custom/lms_order/templates/lms-bank-transfer.html.twig` | Create | Bank transfer instructions/VietQR template | — | S3 |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_base | Intra-module | info.yml dependency |
| lms_payment (PaymentGatewayManager) | Intra-module | info.yml dependency |
| lms_membership (creates lms_user_membership on completion) | Intra-Epic | lms_order.module |
| stripe/stripe-php (Composer) | External | CheckoutForm, MembershipCheckoutForm |
| `lms_membership` Story | Intra-Epic | Must exist before membership checkout |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| Entity annotation pattern | Order, OrderItem entity classes | As-built |
| EntityOwnerTrait | Order entity | As-built |
| AdminHtmlRouteProvider | Order admin routes | As-built |

## Editorial Usability / Entity-First Evidence

| Surface / Component | Visible content | Editable source | Editor UI | Role / permission | Revision / moderation | Translation | Cache invalidation | Smoke evidence |
|---|---|---|---|---|---|---|---|---|
| `/manage/orders` | Order list | lms_order entity | `/manage/orders` | administer lms_order | None | None | Default Drupal entity cache | As-built |
| `/checkout/{course_id}` | Checkout form | lms_payment.settings config | `/admin/config/everlms/payment` | _access: TRUE | None | None | No page cache (form) | As-built |
| `/checkout/bank-transfer/{lms_order}` | VietQR + upload | lms_payment.settings config | `/admin/config/everlms/payment` | access content | None | None | No page cache (form) | As-built |

**Hardcoded content exceptions:** Stripe.js CDN URL (`https://js.stripe.com/v3/`) hardcoded in lms_order.libraries.yml; no ADR filed.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-ORD-001 | Integration | Free course checkout creates completed order + lms_user_course in one request | Auto-enrollment AC |
| T-ORD-002 | Integration | Free membership plan checkout creates lms_user_membership | Membership AC |
| T-ORD-003 | Integration | Paid course checkout renders CheckoutForm with Stripe client_secret in drupalSettings | Stripe AC |
| T-ORD-004 | Integration | Bank transfer submission uploads receipt and redirects to front | Bank transfer AC |
| T-ORD-005 | Integration | Order status transition to `completed` triggers enrollment + email + notification | Auto-enrollment AC |
| T-ORD-006 | Unit | OrderAccessControlHandler: non-owner denied view; admin allowed | Access AC |
| T-ORD-007 | Unit | `createAndFulfillFreeMembershipOrder()` double-set bug regression | Bug regression |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | Module installs; checkout routes resolve | — | — |
| G2 Lint | PASS | No blocking CS violations reported | — | — |
| G3 Tests | PASS | Checkout flow functional per manual smoke | — | — |
| G4 Adversarial | PASS | Known issues logged: Stripe PaymentIntent at build-time (E02), double-set bug (E02) | — | — |

## Notes

- **Stripe PaymentIntent at build-time (BUG):** `CheckoutForm::buildForm()` calls `\Stripe\PaymentIntent::create()` on every GET request. Fix: move to `submitForm()` pre-submit step or use a Stripe Setup Intent on load + PaymentIntent on submit. Logged as issue in E02.
- **Secrets risk:** `stripe_publishable_key` and `stripe_secret_key` are stored in `lms_payment.settings` config YAML committed to repo. See security risk in G02 risk register (planned, layer not yet materialized) — `«secret in config — see security risk»`.
- **Webhook gap:** No webhook endpoint exists for any gateway. Async payment confirmation (chargebacks, delayed PayPal IPN) cannot be received. Logged as target-state item.
- **PayPal in checkout:** The PayPal radio in `CheckoutForm` dispatches to `lms_payment\Plugin\PaymentGateway\PayPalGateway` (stub — no server-side verification). Not the `lms_paypal` module class (dead code). See [lms_payment Story](lms_payment.md).
