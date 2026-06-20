# Wireframe: Checkout Flow

## Step 1: Cart Review

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER                                                               │
├─────────────────────────────────────────────────────────────────────┤
│ PROGRESS: [① Cart ●]──[② Payment ○]──[③ Confirmation ○]            │
├──────────────────────────────────────────┬──────────────────────────┤
│ CART ITEMS (col-8)                       │ ORDER SUMMARY (col-4)    │
│                                          │                          │
│ ┌──────────────────────────────────────┐│ Subtotal:     $79.98     │
│ │ [Thumb] Web Dev Bootcamp    $49.99  ││ Discount:     -$10.00    │
│ │         By John Doe         [✕ Rm]  ││ ──────────────────       │
│ ├──────────────────────────────────────┤│ Total:        $69.98     │
│ │ [Thumb] React Masterclass   $29.99  ││                          │
│ │         By Sarah L.         [✕ Rm]  ││ Coupon: [_________] [Go] │
│ └──────────────────────────────────────┘│ ✅ SAVE20 applied        │
│                                          │                          │
│                                          │ [Proceed to Payment →]   │
├──────────────────────────────────────────┴──────────────────────────┤
│ FOOTER                                                               │
└─────────────────────────────────────────────────────────────────────┘
```

## Step 2: Payment

```
┌─────────────────────────────────────────────────────────────────────┐
│ PROGRESS: [① Cart ✅]──[② Payment ●]──[③ Confirmation ○]            │
├──────────────────────────────────────────┬──────────────────────────┤
│ PAYMENT METHOD (col-8)                   │ ORDER SUMMARY (col-4)    │
│                                          │ (same as step 1)         │
│ ● Credit/Debit Card (Stripe)            │                          │
│   Card Number: [________________]        │ Total: $69.98            │
│   Expiry: [MM/YY] CVV: [___]           │                          │
│   Name: [_________________________]     │                          │
│                                          │                          │
│ ○ PayPal                                │                          │
│   [Pay with PayPal]                     │                          │
│                                          │                          │
│ [🔒 Secure Payment]                    │ [Complete Purchase]       │
└──────────────────────────────────────────┴──────────────────────────┘
```

## Step 3: Confirmation

```
┌─────────────────────────────────────────────────────────────────────┐
│ PROGRESS: [① Cart ✅]──[② Payment ✅]──[③ Confirmation ✅]           │
├─────────────────────────────────────────────────────────────────────┤
│                    ✅ Payment Successful!                            │
│                                                                      │
│  Order #12345                                                        │
│  Date: March 8, 2024                                                 │
│                                                                      │
│  Items:                                                              │
│  • Web Dev Bootcamp — $49.99                                        │
│  • React Masterclass — $29.99                                       │
│  Discount: -$10.00                                                   │
│  Total: $69.98                                                       │
│                                                                      │
│  📧 Confirmation email sent to john@example.com                     │
│                                                                      │
│  [Start Learning →]  [View My Courses]  [📄 Download Invoice]       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Implementation status (as of 2026-06-20)

**as-built (payment form + bank transfer); webhook confirmation is not implemented.**

- Checkout route: `/checkout/{course_id}` → `CheckoutController::checkout` → `lms-checkout.html.twig`. Membership checkout: `/checkout/membership/{plan_id}`. Free courses bypass the form and enroll immediately.
- Payment methods shown in form are driven by `lms_payment.settings` config (enable_stripe / enable_paypal / enable_zalopay / enable_bank_transfer).
- **Stripe:** real — `CheckoutForm::buildForm()` creates a `PaymentIntent` server-side on every form build (one per page load, a known bug), passes `client_secret` to browser via `drupalSettings`, client JS confirms via Stripe Elements. Server verifies via `StripeGateway::processPayment()`.
- **Bank transfer:** real — `BankTransferForm` shows VietQR + receipt upload; `/checkout/bank-transfer/{order}` for instructions.
- **PayPal (lms_payment plugin):** stub — accepts any `paypal_order_id` without server-side API verification.
- **ZaloPay:** stub — hardcoded sandbox redirect URL.
- **Webhooks:** NOT IMPLEMENTED — no webhook routes exist for any gateway. Stripe `handleWebhook()` has a TODO comment. See [../C10-prototypes/PROTO-002-payment-webhook-signature.md](../C10-prototypes/PROTO-002-payment-webhook-signature.md).
- Enrollment auto-triggered: on `lms_order` status → `completed` via `hook_entity_insert/update` in `lms_order.module`. Confirmation email is sent via `hook_mail`.
- The "multi-item cart" flow depicted in the wireframe is **target-state** — actual checkout is per-course or per-membership-plan (single item).

Related: [../C04-entities/04-commerce-entities.md](../C04-entities/04-commerce-entities.md) · [../C05-modules/lms_order.md](../C05-modules/lms_order.md) · [../C05-modules/lms_payment.md](../C05-modules/lms_payment.md)
