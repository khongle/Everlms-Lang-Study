# Flow: Payment Webhook (Stripe)

**Trigger:** Stripe sends POST to `/payment/webhook/stripe` after a payment event.
**Outcome:** Order updated to `completed`; course enrollment activated; user notified.
**Critical path?** Yes — tied to revenue fulfillment.
**Owner:** hainn (modules: `lms_stripe`, `lms_order`, `lms_payment`, `lms_progress`)

> **WARNING:** Stripe signature verification is NOT YET IMPLEMENTED.
> Current handler (`StripeGateway::handleWebhook()`) logs receipt and returns 200 without any processing.
> The flow below describes the **intended** behavior when fully implemented.

---

## Happy Path Steps (Intended)

| # | Actor | Action | Implemented? | On Failure |
|---|-------|--------|-------------|-----------|
| 1 | Stripe | POST `{"type":"payment_intent.succeeded",...}` to `/payment/webhook/stripe` | Stripe-side: Yes | Stripe retries up to 3 days |
| 2 | Nginx | Route request to PHP-FPM; no CSRF middleware (webhook route) | Partial (CSRF must be disabled) | Stripe gets 403 |
| 3 | StripeGateway::handleWebhook() | **Verify `Stripe-Signature` header** using webhook secret | **NOT YET** | Accept or reject request |
| 4 | StripeGateway | Parse event body → extract `event.type` | NOT YET | Log and return 200 (ignore) |
| 5 | StripeGateway | For `payment_intent.succeeded`: extract `metadata.order_id` | NOT YET | Log and return 200 |
| 6 | OrderManager | Load `lms_order` by `order_id` | NOT YET | Log error; return 200 to Stripe |
| 7 | OrderManager | Check idempotency: has this `event.id` been processed? | NOT YET | Skip duplicate; return 200 |
| 8 | OrderManager | Update order status: `pending` → `completed` | NOT YET | Retry via Stripe re-delivery |
| 9 | lms_progress | Create `lms_user_course` enrollment record | NOT YET | Critical gap — paid but no access |
| 10 | lms_notification | Dispatch payment received event → send confirmation email | NOT YET | Log; retry via cron |
| 11 | StripeGateway | Return HTTP 200 to Stripe | Partial (returns 200 but does nothing) | Stripe retries |

---

## Handling Other Events (Intended)

| Stripe Event | Intended Action | Implemented? |
|--------------|----------------|--------------|
| `charge.refunded` | Update order to `refunded`; revoke course access | NOT YET |
| `customer.subscription.created` | Activate membership plan | NOT YET |
| `customer.subscription.updated` | Update membership tier/dates | NOT YET |
| `customer.subscription.deleted` | Deactivate membership | NOT YET |

---

## Current State vs Intended

```
CURRENT (as-coded):
  Stripe POST → StripeGateway::handleWebhook()
                     → logger->notice('Received webhook from Stripe.')
                     → return Response('Webhook processed', 200)
  [Nothing else happens]

INTENDED:
  Stripe POST → Signature verify → Parse event → Route by type
                     → payment_intent.succeeded → fulfill order
                     → charge.refunded → revoke access
                     → subscription.* → manage membership
```

---

## Idempotency Design (Required)

```
// Before processing any event:
$processed = \Drupal::state()->get('stripe_event_' . $eventId);
if ($processed) {
    return new Response('Already processed', 200);
}

// After successful processing:
\Drupal::state()->set('stripe_event_' . $eventId, time());
```

Or store processed event IDs in a dedicated database table with TTL.

---

## Failure Modes

| Failure | Impact | Stripe Behavior |
|---------|--------|----------------|
| Handler returns 4xx/5xx | Stripe retries (exp backoff, up to 3 days) | Retried |
| Handler returns 200 but does nothing (current state) | Order never fulfilled | No retry (Stripe thinks success) |
| Order not found by ID | Fulfillment impossible | Return 200; log; manual resolution |
| Enrollment creation fails after order update | Payment recorded, no access granted | Manual admin fix |
