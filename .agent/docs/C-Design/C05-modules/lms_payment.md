# lms_payment — Payment Gateway Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_payment` |
| Name | LMS Payment |
| Layer | **Commerce** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_base` |

## Mô tả

Module định nghĩa Payment Gateway plugin system — interface và plugin manager để các gateway cụ thể (Stripe, PayPal, ZaloPay, BankTransfer) implement. Cung cấp entity `lms_payment_transaction` để lưu giao dịch, và admin settings form.

---

## Provided Entities

### `lms_payment_transaction` (Content Entity)
| Fields | Description |
|--------|-------------|
| order ref | Ref → lms_order |
| gateway | Plugin ID của gateway |
| external_id | Transaction ID từ provider |
| amount | Số tiền |
| currency | Currency code |
| status | success/failed/pending/refunded |
| raw_response | JSON payload từ provider |
| created | Timestamp |

---

## Provided Routes

| Route ID | Path | Form | Access |
|----------|------|------|--------|
| `lms_payment.settings` | `/admin/config/everlms/payment` | `PaymentSettingsForm` | `administer site configuration` |

---

## Provided Services

| Service ID | Class | Purpose |
|-----------|-------|---------|
| `plugin.manager.payment_gateway` | `PaymentGatewayManager` | Drupal Plugin Manager cho PaymentGateway plugins; extends `default_plugin_manager` |

---

## Provided Plugins

### Plugin Type: `PaymentGateway`

| Plugin ID | Class | Purpose |
|-----------|-------|---------|
| `paypal` (in lms_payment) | `PayPalGateway` | PayPal gateway (duplicate — xem lms_paypal) |
| `bank_transfer` | `BankTransferGateway` | Manual bank transfer |
| `zalopay` | `ZaloPayGateway` | ZaloPay Vietnam |

**Base class:** `PaymentGatewayBase` — implements `PaymentGatewayInterface`.
**Annotation:** `@PaymentGateway` (class `Drupal\lms_payment\Annotation\PaymentGateway`).

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

Không có template. Admin settings form cached theo config.

---

## Related Units

- **C03** § Commerce Domain — entity PaymentTransaction
- **C05** `03-progress-commerce.md` — layer overview
- `lms_order.md` — consumer
- `lms_stripe.md` — Stripe plugin
- `lms_paypal.md` — PayPal plugin (separate module)

---

## Known Issues / Debt

- `PayPalGateway` tồn tại cả trong `lms_payment/src/Plugin/PaymentGateway/` VÀ trong `lms_paypal/src/Plugin/` — khả năng cao trùng lặp / conflict.
- ZaloPay gateway chưa rõ trạng thái implementation (production ready?).
- Không có webhook handler — gateway callback cần route riêng.
