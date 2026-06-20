# PROTO-002 — Payment Webhook Signature Verification (Stripe & PayPal)

**Spike Area:** Verify Stripe (`Stripe-Signature` HMAC-SHA256) và PayPal (`PAYPAL-TRANSMISSION-SIG` cert chain) webhook signatures bên trong Drupal route handler, không dùng Drupal Commerce, để fulfill order atomically và idempotent.
**Hypothesis:** Có thể implement signature verification cho cả Stripe và PayPal trực tiếp trong `PaymentGatewayInterface::handleWebhook()` adapter (theo ADR-007) mà chỉ dùng (a) `stripe/stripe-php` SDK cho Stripe, (b) `paypal/rest-api-sdk-php` hoặc raw HTTPS verify cho PayPal — không cần thêm dependency Symfony / Guzzle middleware. End-to-end (signature → idempotent fulfillment → enrollment) hoàn thành ≤ 500ms ở P95 trên webhook payload sandbox.
**Timebox:** 1.5 ngày (12 giờ — split: 6h Stripe, 4h PayPal, 2h idempotency + report)
**Priority:** High — block release (RISK-004 Critical, FIND-001 + FIND-002 Critical, FIND-009 High); không có spike thì không vào production được.
**Status:** PENDING

---

## Approach

### Stripe path (FIND-001)

1. **Setup Stripe CLI** (`stripe listen --forward-to https://everlms.local/lms/webhook/stripe`) tạo `whsec_*` signing secret cho local; lưu trong `settings.local.php` hoặc Drupal State (key: `lms_stripe.webhook_secret`).
2. **Implement signature verify** trong `lms_stripe/src/Plugin/PaymentGateway/StripeGateway::handleWebhook()`:
   - Đọc raw body qua `$request->getContent()` **trước** khi Drupal parse JSON (Symfony `Request` cho phép đọc raw stream).
   - Gọi `\Stripe\Webhook::constructEvent($payload, $sig_header, $secret, $tolerance = 300)` — lib tự verify HMAC-SHA256 và timestamp.
   - Bắt `SignatureVerificationException` → return 400, log `lms_stripe.webhook` channel.
3. **Idempotency** — lưu `event.id` vào table `lms_payment_webhook_log` (cột `event_id UNIQUE`); duplicate insert → 200 OK no-op (Stripe retry xảy ra nhiều, đây là requirement của Stripe).
4. **Fulfillment hook** — trên `payment_intent.succeeded`: dispatch `PaymentReceivedEvent`, transaction wrap order update + enrollment create (ties với FIND-005, ADR-010).
5. **Stress trigger** — `stripe trigger payment_intent.succeeded` 50 lần liên tiếp + 5 lần với header tampered → kỳ vọng 50 fulfillments + 5 rejections.

### PayPal path (FIND-002, FIND-009)

1. **Setup** PayPal Developer sandbox app, enable webhook tới `https://everlms.local/lms/webhook/paypal`, capture `webhook_id`.
2. **Implement signature verify** trong `lms_paypal/src/Plugin/PaymentGateway/PayPalGateway::handleWebhook()`:
   - Approach **A** (preferred): gọi PayPal Verify API `POST /v1/notifications/verify-webhook-signature` với headers `paypal-auth-algo`, `paypal-cert-url`, `paypal-transmission-id`, `paypal-transmission-sig`, `paypal-transmission-time` + `webhook_id` + raw body. Đo latency (kỳ vọng 200-400ms vì gọi out-of-band).
   - Approach **B** (fallback): verify cert chain locally (download cert từ `paypal-cert-url`, validate chain to PayPal root, verify SHA256withRSA). Phức tạp hơn nhưng không có round-trip.
3. **Resolve FIND-009 first** — xóa `PayPalGateway` duplicate trong `lms_payment` module (dùng `git grep -l "class PayPalGateway"` để xác nhận chỉ còn 1 copy trong `lms_paypal`); update `services.yml` nếu cần.
4. **Routing** — thêm vào `lms_paypal.routing.yml`:
   ```yaml
   lms_paypal.webhook:
     path: '/lms/webhook/paypal'
     defaults:
       _controller: '\Drupal\lms_paypal\Controller\WebhookController::handle'
     methods: [POST]
     requirements:
       _access: 'TRUE'   # webhook là public; auth qua signature verify trong controller
     options:
       no_cache: TRUE
   ```
5. **Idempotency + fulfillment** — tương tự Stripe: `event.id` (PayPal: `id` từ payload) lưu unique table; trigger fulfillment trên `PAYMENT.SALE.COMPLETED` / `PAYMENT.CAPTURE.COMPLETED`.

### Cross-cutting

6. **Common service** — extract `WebhookIdempotencyService` (table: `lms_payment_webhook_log`, columns: `event_id VARCHAR(128) UNIQUE`, `provider VARCHAR(32)`, `received_at INT`, `payload_hash CHAR(64)`); cả 2 gateway dùng chung.
7. **Negative test matrix:**
   - Tampered body → 400 (Stripe: SignatureVerificationException; PayPal: `verification_status: FAILURE`).
   - Replay sau timestamp tolerance window (>5 phút Stripe) → 400.
   - Same event.id 2 lần → 200 lần 1, 200 no-op lần 2 (đảm bảo PayPal/Stripe không retry vô tận do non-200).
   - Tampered amount but valid signature (impossible, but verify amount khớp với order) → 422.

---

## Success Criteria

- **Correctness:**
  - Stripe: 100% tampered payload bị reject; 100% valid payload pass; 0 duplicate fulfillment trên 50 lần retry cùng event.id.
  - PayPal: tương tự; verify API trả `SUCCESS` cho valid và `FAILURE` cho tampered.
- **Latency:**
  - Stripe end-to-end (verify + DB insert + dispatch event) P95 ≤ 200ms (verify offline, không I/O ngoài).
  - PayPal end-to-end P95 ≤ 500ms với Approach A (verify API call); ghi nhận latency của PayPal API riêng để biết SLA phụ thuộc.
- **Atomicity:** Order paid + enrollment created luôn atomic — không tồn tại trạng thái "order paid nhưng chưa có enrollment" (ties với FIND-005). Test: kill DB connection mid-transaction → cả 2 rollback.
- **Security:** `webhook_secret` (Stripe) và `webhook_id` (PayPal) đọc từ Drupal State, không từ filesystem-readable file; rotate test pass.
- **No duplicate gateway:** `git grep -c "class PayPalGateway"` = 1 sau khi resolve FIND-009.

---

## Dependencies

- Stripe test account + Stripe CLI installed.
- PayPal Developer sandbox app (REST API credentials) + sandbox webhook endpoint.
- Drupal site có HTTPS local (mkcert hoặc ngrok tunnel) — webhook providers yêu cầu HTTPS.
- DB schema migration cho `lms_payment_webhook_log` (Phase 1 spike: tạo trong update hook tạm).
- `stripe/stripe-php` SDK (`composer require stripe/stripe-php:^14.0`).
- `lms_payment` PaymentGatewayInterface đã có (theo ADR-007 ACCEPTED).

---

## Outcome

**Status:** PENDING
**Result:** _(điền sau: signature pass/fail counts, latency P50/P95/P99 mỗi provider, idempotency test results, atomicity rollback test result)_
**Decision:** _(điền sau: chốt approach Stripe + PayPal; quyết định Approach A hay B cho PayPal; chốt schema cho `lms_payment_webhook_log`)_

### Artifacts

- _(điền sau)_ Branch: `spike/proto-002-payment-webhook`
- _(điền sau)_ Stripe verify benchmark: `.agent/generated/proto-002/stripe-bench.csv`
- _(điền sau)_ PayPal verify benchmark: `.agent/generated/proto-002/paypal-bench.csv`
- _(điền sau)_ Stripe CLI capture: `.agent/generated/proto-002/stripe-events.json`
- _(điền sau)_ PayPal sandbox webhook capture: `.agent/generated/proto-002/paypal-events.json`

### Impact on Architecture

- **Nếu VALIDATED:** Resolve FIND-001, FIND-002, FIND-009; close RISK-004 (degrade Critical → Mitigated). Update ADR-007 status section ghi nhận signature mechanism per gateway. Promote `WebhookIdempotencyService` lên `lms_payment` core service (không phải per-gateway). U-028 (Stripe gateway), U-029 (PayPal gateway), U-060/U-061 (webhook) mở khoá implementation.
- **Nếu INVALIDATED — Stripe SDK conflict:** Highly unlikely; nếu xảy ra (e.g., Drupal HTTP middleware nuốt raw body) → ADR mới về raw-body access pattern; có thể dùng `kernel.request` listener early.
- **Nếu INVALIDATED — PayPal Approach A latency > SLA:** Switch Approach B (local cert chain verify); thêm RISK mới về cert-rotation maintenance; tạo runbook H02 cho cert update.
- **Routing:** FIND-002 yêu cầu thêm `lms_paypal.webhook` route — confirm trong PROTO-002 + đăng ký vĩnh viễn trong U-061.

---

## Related

- **Risks:** RISK-004 (G02), FIND-001, FIND-002, FIND-009, FIND-005 (atomicity)
- **ADRs:** ADR-007 (PaymentGatewayInterface — ACCEPTED)
- **Affected units:** U-028, U-029, U-031, U-060, U-061, U-062
- **Implementation reference:**
  - `web/modules/custom/lms_stripe/src/Plugin/PaymentGateway/StripeGateway.php`
  - `web/modules/custom/lms_paypal/src/Plugin/PaymentGateway/PayPalGateway.php`
  - `web/modules/custom/lms_payment/src/Plugin/PaymentGatewayInterface.php`
- **External references:** Stripe webhook docs, PayPal Webhook Verification API docs (URLs to be added by implementer; do not link unverified URLs).
