# G01-ADR-007: PaymentGatewayInterface + PaymentManager (Stripe & PayPal là Adapter)

**Status:** ACCEPTED
**Date:** 2026-03-08
**Decision Makers:** SA, Agent_Architect

---

## Context

EverLMS cần xử lý thanh toán cho:
- Course purchase (one-time)
- Membership subscription (recurring)
- Bundle purchase

Yêu cầu payment system:
- Support nhiều gateway: Stripe, PayPal, ZaloPay, Bank Transfer (VN market)
- Mỗi gateway có API riêng, webhook format riêng, credential riêng
- Caller code (OrderController, MembershipController) không phụ thuộc vào gateway cụ thể
- Dễ thêm gateway mới mà không sửa business logic

**Tình trạng hiện tại:**
- `PaymentGatewayInterface` và `PaymentGatewayManager` đã implement
- `PaymentGatewayBase` (abstract base class) đã có
- Adapters: `PayPalGateway`, `ZaloPayGateway`, `BankTransferGateway` đã có
- **Stripe gateway: stub** (RISK-004 trong A10)
- `PaymentManager` service: implemented

---

## Decision

Áp dụng **Adapter Pattern** qua Drupal Plugin system:

**Interface** (`lms_payment/src/Plugin/PaymentGatewayInterface.php`):
```php
interface PaymentGatewayInterface extends PluginInspectionInterface {
  public function processPayment($order, array $payment_details = []): array;
  public function handleWebhook($request): Response;
  public function getLabel(): string;
}
```

**Manager** (`lms_payment/src/Plugin/PaymentGatewayManager.php`):
- Extends `DefaultPluginManager`
- Discovery: `Plugin/PaymentGateway/` directory
- Annotation: `@PaymentGateway`

**Service layer** (`lms_payment/src/Service/PaymentManager.php`):
- Business logic: validate order, call gateway, persist result, dispatch event
- Inject `PaymentGatewayManager` để load gateway theo config

**Adapter implementations:**
```
Plugin/PaymentGateway/
├── PayPalGateway.php       ← IMPLEMENTED
├── ZaloPayGateway.php      ← IMPLEMENTED
└── BankTransferGateway.php ← IMPLEMENTED
lms_stripe/
└── StripeGateway.php       ← STUB (RISK-004)
```

Mỗi gateway implement `processPayment()` và `handleWebhook()` theo API của provider đó.

---

## Rationale

**Adapter Pattern:**
Mỗi payment gateway có HTTP API khác nhau hoàn toàn (Stripe REST, PayPal SDK, ZaloPay signature scheme). Adapter pattern cho phép wrap từng gateway API vào cùng một interface — caller không cần biết đang dùng gateway nào.

**Drupal Plugin (không phải Strategy factory):**
Khác với Question Grading (8 loại cố định nội bộ), payment gateways cần:
- Discovery từ nhiều modules (`lms_stripe` là module riêng)
- Admin UI để enable/disable gateway per site
- Configuration per gateway (API keys, sandbox/live mode)

Plugin Manager phù hợp hơn static factory.

**Tách `PaymentGatewayManager` (plugin discovery) và `PaymentManager` (business logic):**
- `PaymentGatewayManager`: Drupal plugin infrastructure
- `PaymentManager`: orchestrate flow (validate → charge → save → event)
- Separation cho phép test business logic độc lập với gateway

---

### Options Considered

#### Option 1: PaymentGatewayInterface + Plugin Manager (CHOSEN)
- **Pros:** Extensible (gateway mới = module mới), gateway-specific logic encapsulated trong adapter, admin UI có thể toggle gateway, consistent với lms_ai plugin pattern
- **Cons:** Overhead Plugin system setup, Stripe ở trong module riêng (`lms_stripe`) cần maintain separately
- **Fit:** Phù hợp — VN market cần nhiều gateway (ZaloPay, MoMo tiềm năng)

#### Option 2: Direct Stripe SDK integration (REJECTED)
- **Pros:** Đơn giản nhất cho v1, ít boilerplate
- **Cons:** Hard-coupled vào Stripe, thêm PayPal/ZaloPay phải sửa core code, không extensible
- **Reason rejected:** Không phù hợp VN market (ZaloPay, bank transfer phổ biến hơn Stripe)

#### Option 3: Third-party payment module (Drupal Commerce) (REJECTED)
- **Pros:** Mature, nhiều gateway có sẵn
- **Cons:** Drupal Commerce có schema và workflow riêng, conflict với LMS-specific Order entity, over-engineering
- **Reason rejected:** EverLMS có Order entity tùy chỉnh không tương thích với Drupal Commerce schema

---

## Consequences

### Positive
- Thêm gateway mới: tạo module `lms_momo` với `MomoGateway.php` — không sửa `lms_payment`
- `processPayment()` trả về chuẩn `['status', 'transaction_id', 'message']` — OrderController xử lý thống nhất
- Webhook handler per gateway — mỗi gateway xử lý signature verification riêng
- `PaymentManager` có thể mock gateway trong unit test

### Negative
- Stripe hiện là **stub** (RISK-004): `processPayment()` chưa implement real Stripe API call
- `handleWebhook()` parameter type hint dùng `mixed` (`$request`) thay vì `Request` — cần standardize
- ZaloPay/BankTransfer signature verification cần security review

### Risks
- **RISK-002** (G02, P0): Payment webhook spoofing — mỗi gateway phải verify signature trong `handleWebhook()`; hiện tại chưa đồng đều
- **RISK-004** (A10): Stripe gateway là stub — phải implement trước production release
- **RISK-009** (G02, P1): Stripe/PayPal API version deprecation — pin SDK versions

---

## Related

- **Design docs:** C01-architecture-decision.md (Design Patterns: Plugin/Payment)
- **Implementation:** `web/modules/custom/lms_payment/src/Plugin/`, `web/modules/custom/lms_stripe/`
- **Other ADRs:** ADR-006 (AI provider, same plugin pattern)
- **Risks:** RISK-002, RISK-004, RISK-009 in G02-risk-register.md
- **Modules:** `lms_payment`, `lms_stripe`, `lms_paypal`
