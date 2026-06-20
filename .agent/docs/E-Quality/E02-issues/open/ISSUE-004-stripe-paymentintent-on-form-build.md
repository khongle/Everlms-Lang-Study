# ISSUE-004 — Stripe PaymentIntent Created on Form BUILD (Orphaned Intents Per Page Load)

| Field | Value |
|---|---|
| ID | `ISSUE-004` |
| Title | Stripe `PaymentIntent::create()` called in `buildForm()` — one orphaned intent per page view |
| Source | G4-Architecture / code-review |
| Reporter | Agent_QA |
| Created | 2026-06-20 |
| Updated | 2026-06-20 |
| Status | `open` |
| Severity | `HIGH` |
| Affected page / route | `/checkout/{course_id}`, `/checkout/membership/{plan_id}` |
| Owning Epic | E-10 (G-HARDENING) |
| Owning Story | [`payment-webhooks`](../../../D-Development/D03-stories/payment-webhooks.md) |
| Owning module(s) | `lms_order`, `lms_stripe` |

## Summary

`CheckoutForm::buildForm()` and `MembershipCheckoutForm::buildForm()` both call `\Stripe\PaymentIntent::create()` directly — every time the form is rendered. This means:

- Every page load of `/checkout/{course_id}` (including page refreshes, back-button revisits, and AJAX rebuilds) creates a new Stripe PaymentIntent object in the Stripe account.
- Only one PaymentIntent (the most recently created) is submitted on form submission; all others become **orphaned intents** that never progress to `succeeded` or `canceled`.
- Orphaned intents accumulate in the Stripe Dashboard, cluttering payment logs, inflating intent counts, and making reconciliation difficult.
- Each `PaymentIntent::create()` call is a synchronous Stripe API call on the critical render path — adding network latency to every checkout page load, even for users who abandon the page immediately.
- Under load (e.g., many concurrent students visiting checkout), this multiplies Stripe API calls and risks hitting Stripe rate limits.

The `client_secret` returned from `PaymentIntent::create()` is passed to the browser via `drupalSettings`, then the Stripe.js library uses it client-side to confirm the payment. This flow is architecturally correct except for the placement (build vs submit).

## Reproduction

1. Open `web/modules/custom/lms_order/src/Form/CheckoutForm.php` → `buildForm()` method.
2. Locate the block: `if (Stripe method is enabled) { \Stripe\Stripe::setApiKey(...); $intent = \Stripe\PaymentIntent::create([...]) }`.
3. Log in as a student, navigate to `/checkout/1` (any paid course).
4. Refresh the page 5 times.
5. In Stripe Dashboard → Payments → PaymentIntents: observe 5 new intents with status `requires_payment_method` (orphaned).
6. The same pattern exists in `MembershipCheckoutForm::buildForm()`.

## Root Cause

The initial Stripe integration placed `PaymentIntent::create()` in `buildForm()` to make the `client_secret` available as soon as the form renders (required by Stripe Elements JS before card input). This is a common Stripe integration pattern for single-page checkout, but should use either:

- **Deferred intent creation** — create the intent on the first AJAX interaction or on form submit.
- **`payment_method_types=['card']` with `setup_future_usage`** — use a SetupIntent for element mounting, create the PaymentIntent only on confirm.

The developer prioritized getting Stripe Elements working and did not account for the orphaned-intent side effect of form rebuilds.

## Proposed Fix

Per Story [`payment-webhooks`](../../../D-Development/D03-stories/payment-webhooks.md) session S1:

1. **Move `PaymentIntent::create()` to `submitForm()`** (or a dedicated AJAX endpoint called on first user interaction with the card field):
   - In `buildForm()`: pass `publishable_key` only to `drupalSettings`; do NOT call `PaymentIntent::create()` yet.
   - Add a `#ajax` callback or a new route `POST /api/lms/payment/stripe/create-intent` that creates the intent and returns the `client_secret`.
   - Stripe.js calls this endpoint once, caches the `client_secret`, mounts Elements.
   - On form submit, the `payment_intent_id` is sent as before.
2. **Cancel stale intents** — add a Drush command or cron job to cancel PaymentIntents older than 1 hour with status `requires_payment_method` (Stripe allows programmatic cancellation).
3. Apply the same fix to `MembershipCheckoutForm`.

## Trace links

- **Story:** `D-Development/D03-stories/payment-webhooks.md` (explicitly notes this bug: "creates PaymentIntent on form build — must fix")
- **C05 modules:** `C-Design/C05-modules/lms_order.md`, `C-Design/C05-modules/lms_stripe.md`
- **Facts base:** `modules-commerce.md` §lms_order — CheckoutForm.php, §Stripe flow (actual runtime)
- **Related issues:** [ISSUE-002](ISSUE-002-no-payment-webhook-endpoints.md) (Stripe webhook gap — same Story scope)
