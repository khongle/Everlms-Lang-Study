# lms_paypal — PayPal Payment Gateway Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_paypal` |
| Name | LMS PayPal |
| Layer | **Commerce / Integration** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_payment` |

## Mô tả

Module tích hợp PayPal payment gateway. Implement `PaymentGateway` plugin từ `lms_payment`. Module rất minimal — chỉ có 1 PHP class là `PayPalGateway`.

---

## Provided Entities

Không có entity.

---

## Provided Routes

Không có routing file.

---

## Provided Services

Không có services file.

---

## Provided Plugins

| Plugin Type | Plugin ID | Class | Purpose |
|-------------|-----------|-------|---------|
| `PaymentGateway` | `paypal` (inferred) | `PayPalGateway` | Tích hợp PayPal REST API / Express Checkout |

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

Không có template.

---

## Related Units

- **C05** `03-progress-commerce.md` — layer overview
- `lms_payment.md` — plugin system base, cũng có `PayPalGateway` duplicate trong `src/Plugin/PaymentGateway/`
- `lms_order.md` — consumer

---

## Known Issues / Debt

- **CONFLICT**: `lms_payment` cũng chứa `PayPalGateway` class trong `src/Plugin/PaymentGateway/PayPalGateway.php` — duplicate namespace conflict.
- Module scaffold-only level — chỉ có 1 class.
- Không có webhook route để nhận PayPal IPN/webhook.
- Không rõ dùng PayPal Classic hay REST API mới.
