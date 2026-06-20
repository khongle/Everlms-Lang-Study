# Alert: Payment Webhook Signature Failure Spike

> **PROPOSED** — Chưa active. Implement khi lms_payment có structured logging + Prometheus.

---

## Metadata

| Field | Value |
|-------|-------|
| **Alert ID** | alert-everlms-webhook-fail |
| **Severity** | high |
| **Owner** | SA |
| **Runbook** | `H-Operations/H02-runbooks/RUN-004-payment-webhook-replay.md` |
| **Created** | 2026-04-16 |
| **Status** | PROPOSED |

---

## Alert Definition (YAML)

```yaml
id: alert-everlms-webhook-fail
severity: high
for: 2m
team: sa
runbook: H-Operations/H02-runbooks/RUN-004-payment-webhook-replay.md
suppress_during: [deploy]
notification:
  channels: [email, slack]
  auto_resolve: true
labels:
  service: everlms-payment
  env: production

signal: |
  # Option A: Nếu có custom Drupal metric (PROPOSED)
  rate(lms_payment_webhook_signature_failures_total[5m]) > 0.1

  # Option B: Từ log (Loki + Grafana)
  # count_over_time({job="everlms", component="lms_payment"} |= "WEBHOOK_SIGNATURE_INVALID" [5m]) > 3
```

---

## Trigger Condition

| Metric | Operator | Threshold | Window | Action |
|--------|----------|-----------|--------|--------|
| Webhook signature failures | > | 3 events | 5 phút | Fire alert |
| Hoặc: failure rate | > | 0.1/sec | 2 phút liên tục | Fire alert |

---

## Why This Threshold

- 0 signature failures là normal trong healthy state
- 1–2 failures có thể là attacker probe (tự động block bởi Stripe/PayPal)
- > 3 failures trong 5 phút = potential attack hoặc webhook secret bị thay đổi/lộ
- Revenue impact: mỗi failed webhook = 1 order có thể stuck ở `pending`

---

## Triage Steps (khi alert fire)

```
1. Phân loại nguyên nhân:
   a. Secret key thay đổi → Stripe/PayPal dashboard, xác nhận webhook secret
   b. Attacker forge requests → IP log, block nếu cần
   c. Bug trong signature validation code → xem watchdog + code

2. Kiểm tra orders bị ảnh hưởng:
   drush sql:query "SELECT id, state, placed FROM commerce_order WHERE state = 'pending'
     AND placed > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 MINUTE));"

3. Nếu secret đổi không chủ ý → update lại trong Drupal config
   drush config:set lms_stripe.settings webhook_secret "whsec_xxx" --yes
   drush config:set lms_paypal.settings webhook_id "xxx" --yes

4. Replay missed webhooks → RUN-004

5. Nếu là attack → log SEV3 incident H01 + review security
```

---

## Signature Validation Flow (tham chiếu code)

```
Request POST /webhooks/stripe
  → lms_stripe: StripeGateway::handleWebhook()
  → Verify signature: \Stripe\Webhook::constructEvent(payload, sig, secret)
  → Nếu SignatureVerificationException → log + return 400
  → Nếu OK → process event
```

- Module: `web/modules/custom/lms_stripe/src/Plugin/PaymentGateway/StripeGateway.php`
- Module: `web/modules/custom/lms_paypal/` (PayPal tương tự)

---

## Custom Metric (cần implement)

```php
// PROPOSED — thêm vào StripeGateway::handleWebhook()
// Khi signature invalid:
\Drupal::service('lms_monitoring.counter')
  ->increment('lms_payment_webhook_signature_failures_total', [
    'provider' => 'stripe',
    'reason' => 'invalid_signature',
  ]);
```

---

## SLO Link

- Không trực tiếp trong SLO `02-quiz-submission-success`, nhưng ảnh hưởng revenue
- Nếu webhook fail rate cao → manual intervention required → track trong H01

---

## Related Alerts

| Alert | Quan hệ |
|-------|---------|
| `01-error-rate` | Server overload có thể làm webhook endpoint trả 5xx → Stripe retry |
