# I03 — Webhook Contracts

EverLMS nhận inbound webhooks từ payment providers (Stripe, PayPal) để xử lý fulfillment sau khi thanh toán hoàn tất.

> **Khác biệt:** EverLMS là *receiver* (nhận webhook từ Stripe/PayPal), không phải *sender*.
> EverLMS không (chưa) phát outbound webhooks đến external consumers.

---

## Inbound Webhook Receivers

| Provider | Endpoint | Status | Signature Verified? |
|----------|----------|--------|---------------------|
| Stripe | `POST /payment/webhook/stripe` | Partial | **NOT YET — see flag in 01-stripe-webhook.md** |
| PayPal | `POST /payment/webhook/paypal` | Proposed | **NOT YET — see flag in 02-paypal-webhook.md** |

---

## Files in This Folder

| File | Description |
|------|-------------|
| `01-stripe-webhook.md` | Contract cho Stripe → EverLMS webhook |
| `02-paypal-webhook.md` | Contract cho PayPal → EverLMS webhook |

---

## Security Note

**Status: NOT YET IMPLEMENTED**

Cả hai webhook receivers hiện chưa verify signature.
Đây là security gap — bất kỳ request nào gửi đến endpoint cũng được xử lý.
Xem flag trong từng file contract để biết implementation requirement.

---

## Outbound Webhooks (PROPOSED)

**Status: PROPOSED** — EverLMS chưa gửi webhooks ra ngoài.

Khi được implement, outbound webhooks sẽ thông báo external systems về:
- `enrollment.created`
- `certificate.issued`
- `payment.received`

*Outbound webhook contracts sẽ được thêm vào folder này khi được implement.*
