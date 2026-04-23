# TEST-004 — Payment Checkout Flow

| Field | Value |
|-------|-------|
| ID | TEST-004 |
| Type | Integration |
| Priority | P0 — Must |
| Feature | Commerce / F-PAYMENT |
| Units | U-027 (PaymentManager), U-030 (Checkout workflow), U-060/U-061 (Webhook) |
| Traceability | E2E-020, E2E-021 (E01), ISSUE-004 |
| Owner | Unassigned |

## Scope

Verify checkout workflow: order creation, payment initiation, webhook handling, và enrollment trigger. Test này **định nghĩa yêu cầu cho real implementation** — hiện tại stub nên các TC sẽ fail cho đến khi U-030/U-060 được implement.

## Preconditions

- Course tồn tại với `price > 0`
- User authenticated với role `student`, chưa enrolled
- (Webhook test) Stripe test secret key configured trong `lms_payment.settings`
- Payment gateway mode = `sandbox`

## Test Cases

### TC-004-A: Order creation

**Given:** User chọn course, click "Add to Cart"  
**When:** POST `/api/lms/order/create` với `{ course_id, gateway: 'stripe' }`  
**Then:**
- Order entity được tạo với `status = pending`
- `order_item` record chứa course reference + amount đúng
- Response trả về `{ order_id, payment_url }`

---

### TC-004-B: Payment success → enrollment grant

**Given:** Order `pending` tồn tại  
**When:** Stripe webhook gửi `payment_intent.succeeded` với `metadata.order_id`  
**Then:**
- Order status chuyển `pending → completed`
- `user_course` record được tạo (enrollment granted)
- Notification "Payment successful" được dispatch

---

### TC-004-C: Payment failure → order cancelled

**Given:** Order `pending` tồn tại  
**When:** Stripe webhook gửi `payment_intent.payment_failed`  
**Then:**
- Order status chuyển `pending → failed`
- Không tạo `user_course`
- Notification "Payment failed" được dispatch

---

### TC-004-D: Webhook signature verification (ISSUE-004 fix)

**Given:** Request đến `/api/lms/payment/webhook` thiếu `Stripe-Signature` header  
**When:** Webhook handler nhận request  
**Then:** HTTP 400, không xử lý, log cảnh báo

| Scenario | Expected |
|----------|---------|
| Valid signature | 200, process |
| Invalid signature | 400, reject |
| Missing header | 400, reject |
| Replay (same event_id) | 200, idempotent no-op |

---

### TC-004-E: Concurrent webhook — idempotency

**Given:** Stripe gửi cùng `event_id` 2 lần  
**When:** Cả 2 webhook được nhận  
**Then:** Order chỉ complete 1 lần, enrollment không duplicate

## Stub Note

> Các TC trên sẽ **fail** cho đến khi U-028 (StripeGateway real impl), U-030 (Checkout workflow), U-060/U-061 (Webhook signature), và U-062 (Idempotency) được implement. Test cases này là acceptance spec cho những units đó.

## Công cụ

- PHPUnit KernelTestBase + Stripe PHP SDK mock
- Webhook test: custom HTTP client với signed payload
