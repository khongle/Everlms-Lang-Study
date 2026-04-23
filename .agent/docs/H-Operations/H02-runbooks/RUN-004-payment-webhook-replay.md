# RUN-004 — Payment Webhook Replay

| Field | Value |
|-------|-------|
| **ID** | RUN-004 |
| **Owner** | SA |
| **Last Updated** | 2026-04-16 |
| **Last Tested** | UNTESTED |
| **Last Executed** | — |
| **Severity scope** | SEV2 / SEV3 |

---

## Khi nào dùng

- Stripe/PayPal webhook bị miss (network timeout, server down lúc webhook đến)
- Order ở trạng thái `pending` quá lâu dù payment đã thành công
- User báo đã thanh toán nhưng không được enroll vào course
- Webhook endpoint trả về lỗi 5xx → Stripe/PayPal đã retry nhưng không thành công hết

---

## Prerequisites

- [ ] Xác nhận payment đã thành công trên dashboard Stripe/PayPal
- [ ] Có Payment ID / Webhook Event ID từ Stripe/PayPal dashboard
- [ ] Webhook endpoint hiện tại hoạt động bình thường (không còn lỗi 5xx)
- [ ] DDEV đang chạy (local) hoặc SSH vào server (staging/prod)

---

## Part A — Xác định Webhook Event bị miss

### 1. Kiểm tra order trong DB

```bash
ddev drush sql:query "
  SELECT o.id, o.uuid, o.state, o.total_price__number, o.placed
  FROM commerce_order o
  WHERE o.state = 'pending'
    AND o.placed < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 MINUTE))
  ORDER BY o.placed DESC
  LIMIT 20;"
```

### 2. Kiểm tra log webhook trong lms_payment

```bash
ddev drush sql:query "
  SELECT *
  FROM lms_payment_log
  WHERE created > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR))
    AND status != 'success'
  ORDER BY created DESC
  LIMIT 20;"
# PROPOSED: bảng lms_payment_log chưa được implement
```

### 3. Kiểm tra Drupal watchdog

```bash
ddev drush watchdog:show --type=lms_payment --count=50
ddev drush watchdog:show --type=lms_stripe --count=50
```

---

## Part B — Replay Stripe Webhook

### 1. Lấy Event ID từ Stripe Dashboard

- Vào: https://dashboard.stripe.com/events
- Filter: Type = `payment_intent.succeeded` hoặc `checkout.session.completed`
- Tìm event của order bị miss → copy Event ID (`evt_xxx`)

### 2. Replay qua Stripe CLI (DDEV local)

```bash
# PROPOSED: cài Stripe CLI
# ddev exec stripe listen --forward-to https://everlms.ddev.site/webhooks/stripe

# Replay event cụ thể
# ddev exec stripe events resend evt_1ABC123XYZ
```

### 3. Replay qua Stripe Dashboard (không cần CLI)

- Dashboard → Developers → Webhooks → chọn endpoint → Events tab
- Tìm event → nút "Resend"

### 4. Xác nhận webhook đến Drupal

```bash
ddev drush watchdog:show --type=lms_stripe --count=10
# Mong đợi: log "Stripe webhook received: payment_intent.succeeded"
```

---

## Part C — Replay PayPal Webhook

### 1. Lấy Event ID từ PayPal Dashboard

- Vào: https://developer.paypal.com/dashboard/ → Webhooks
- Tìm event bị miss → copy Event ID

### 2. Replay qua PayPal API

```bash
# PROPOSED: PayPal webhook replay API
# curl -X POST https://api.paypal.com/v1/notifications/simulate-event \
#   -H "Authorization: Bearer {ACCESS_TOKEN}" \
#   -H "Content-Type: application/json" \
#   -d '{"webhook_id": "{WEBHOOK_ID}", "event_type": "PAYMENT.CAPTURE.COMPLETED"}'
```

---

## Part D — Manual Order Completion (Last Resort)

Nếu không thể replay webhook:

```bash
# Chuyển order sang trạng thái completed thủ công
ddev drush php-eval "
  \$order = \Drupal\commerce_order\Entity\Order::load({ORDER_ID});
  \$order->set('state', 'completed');
  \$order->save();
  // Trigger enrollment hooks
  \Drupal::moduleHandler()->invokeAll('lms_order_completed', [\$order]);
"
# CẢNH BÁO: Chỉ dùng khi đã xác nhận payment thành công trên Stripe/PayPal
# Ghi log hành động này vào H01
```

---

## Verification

- [ ] Order chuyển sang `completed` trong DB
- [ ] User được enroll vào course (`lms_progress` records được tạo)
- [ ] User nhận email xác nhận (nếu notification đã implement)
- [ ] Không có duplicate enrollment

---

## Tham chiếu

- H01: Luôn log khi thực hiện manual order completion
- lms_payment module: `web/modules/custom/lms_payment/`
- lms_stripe module: `web/modules/custom/lms_stripe/`
- H07 alerts: `alerts/02-payment-webhook-fail.md`
