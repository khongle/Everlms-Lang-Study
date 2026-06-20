# D03 — Story Spec: lms_stripe

**Story ID:** lms_stripe
**Story type:** module
**Title:** Stripe Payment Gateway — PaymentIntent Verify and Webhook Stub
**Primary source:** `C-Design/C05-modules/lms_stripe.md`
**Owning module(s):** lms_stripe
**Service(s):** N/A (no services.yml; plugin discovered by PaymentGatewayManager)
**Epic:** E-05 (Commerce & Payment)
**Contributes to Feature(s):** F-005 (Payment Integration)
**Design source(s):** `../C05-modules/lms_stripe.md`
**Round:** N/A
**Driver:** SA directive
**Status:** IN_PROGRESS
**Adequacy:** NOT_RUN
**Adequacy run:** —
**Assigned session(s):** —
**Tasks:** see `D04-tasks/` filtered by `Story ID: lms_stripe`

## Session Strategy (Cấp Độ 5)

**Estimated session count:** 1 (small — single plugin file; open work is webhook and PaymentIntent timing)
**Splitting strategy:** N/A — single session covers StripeGateway plugin class

## Scope (from primary source)

Provides the `stripe` payment gateway plugin for `lms_payment`'s plugin system. Contains a single PHP class: `StripeGateway`. At runtime the client-side Stripe Elements JS (hosted in `lms_order`) creates a PaymentIntent and confirms card payment in the browser; the server-side `StripeGateway::processPayment()` retrieves the PaymentIntent via the Stripe PHP SDK and checks `status === 'succeeded'`.

**IN_PROGRESS status rationale — two open issues:**

1. **PaymentIntent created at form BUILD time (bug):** `CheckoutForm::buildForm()` (in `lms_order`) calls `\Stripe\PaymentIntent::create()` on every page load, before the user submits. This produces a new dangling PaymentIntent on every GET request and passes a stale `client_secret` if the user navigates back. The fix belongs in `lms_order` (move creation to pre-submit or use `stripe_elements.js` to create client-side), but this Story owns the Stripe integration context and must close once the fix is verified end-to-end. Logged as E02 issue.

2. **Webhook stub (target-state / planned):** `handleWebhook()` returns HTTP 200 with a TODO comment. No Stripe webhook signature verification (`Stripe\Webhook::constructEvent()`) is implemented. No route exists to receive webhooks.

## Source Documents

| Source | Why loaded |
|---|---|
| `../C05-modules/lms_stripe.md` | Owning module design |
| `lms_payment.md` | Plugin host; settings form owns stripe credentials |
| `lms_order.md` | CheckoutForm owns PaymentIntent creation (build-time bug) |
| `../../B-Requirements/B06-integration-requirements/` | Stripe integration contract |

## Requirements

| Requirement | Source | Type |
|------------|--------|------|
| Stripe card payment via PaymentIntent server-side retrieval and status check | B03-features/04-order-membership | Acceptance — as-built |
| PaymentIntent created once per user intent (not per page load) | B03-features/04-order-membership | target-state / planned (bug open) |
| Stripe webhook endpoint with signature verification | B03-features/04-order-membership | target-state / planned (not implemented) |

## Files Touched (running record)

| File | Action | Description | Task | Session |
|------|--------|-------------|------|---------|
| `web/modules/custom/lms_stripe/lms_stripe.info.yml` | Create | Module declaration (depends on lms_payment) | — | S1 |
| `web/modules/custom/lms_stripe/src/Plugin/PaymentGateway/StripeGateway.php` | Create | @PaymentGateway plugin; processPayment() + handleWebhook() stub | — | S1 |

## Dependencies

| Dependency | Type | Source |
|-----------|------|--------|
| lms_payment | Intra-module | info.yml; PaymentGatewayBase + PaymentGatewayManager |
| stripe/stripe-php (Composer) | External | StripeGateway::processPayment() |
| lms_order (CheckoutForm — PaymentIntent timing bug) | Intra-Epic | Bug co-ownership |

## Conventions Checked (D00)

| Convention | Applies to | Evidence |
|---|---|---|
| @PaymentGateway annotation | StripeGateway class | As-built |
| Extends PaymentGatewayBase | StripeGateway | As-built |
| Plugin namespace Plugin/PaymentGateway | StripeGateway location | As-built |

## Editorial Usability / Entity-First Evidence

Not applicable — this module contains no user-visible pages or content surfaces. Credentials managed via `lms_payment` settings form.

**Hardcoded content exceptions:** None in this module.

## Feature Flags Introduced or Read

None.

## Test Plan

| Test ID | Type | Description | Acceptance Criteria |
|---------|------|-------------|-------------------|
| T-STR-001 | Unit | StripeGateway::processPayment() reads lms_payment.settings stripe_secret_key and calls Stripe SDK | Stripe connect AC |
| T-STR-002 | Integration | processPayment() returns status=success when PaymentIntent status=succeeded | Payment success AC |
| T-STR-003 | Integration | processPayment() returns error when PaymentIntent not found or status != succeeded | Payment failure AC |
| T-STR-004 | Unit | handleWebhook() returns HTTP 200 (stub — non-fatal) | Webhook stub AC |
| T-STR-005 | Integration | PaymentIntent not created until user submits (after build-time bug fixed) | Bug fix verification |

## Gate Results (per Story)

| Gate | Status | Details | Attempt | Date |
|------|--------|---------|---------|------|
| G1 Build | PASS | StripeGateway discovered by PaymentGatewayManager; stripe/stripe-php installed | — | — |
| G2 Lint | PASS | Single file, no violations | — | — |
| G3 Tests | PASS | processPayment() manual smoke passes against Stripe test keys | — | — |
| G4 Adversarial | PENDING | PaymentIntent build-time bug open; webhook stub unresolved | 0/3 | — |

## Notes

- **PaymentIntent build-time bug (BUG — E02):** `CheckoutForm::buildForm()` in `lms_order` creates a Stripe PaymentIntent on every page GET. This Story is not DONE until the fix is implemented and T-STR-005 passes. Proposed fix: remove PaymentIntent creation from buildForm(); use Stripe Elements' automatic PaymentIntent creation (paymentElement) or create the intent via AJAX on payment method selection.
- **Webhook verification (target-state):** Implement `Stripe\Webhook::constructEvent($payload, $sig_header, $webhook_secret)` inside `handleWebhook()` and add a route (e.g., `/api/lms/webhook/stripe`) in `lms_stripe.routing.yml`. Required for reliable async charge confirmation and chargeback handling.
- **Stripe test keys in committed config:** `stripe_secret_key` and `stripe_publishable_key` in `lms_payment.settings` YAML and in committed `.env` — `«secret in config — see security risk»`.
- **No routes or services in this module:** All config lives in `lms_payment`; all JS in `lms_order`. This module is intentionally a thin plugin wrapper.
