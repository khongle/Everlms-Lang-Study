# lms_stripe — Stripe Payment Gateway Module

## Module Metadata

| Key | Value |
|-----|-------|
| Module ID | `lms_stripe` |
| Name | LMS Stripe |
| Layer | **Commerce / Integration** |
| Version / Lifecycle | stable, Drupal ^11 |
| Package | EverLMS |
| Dependencies | `lms_payment` |

## Mô tả

Module tích hợp Stripe payment gateway. Implement `PaymentGateway` plugin từ `lms_payment`. Module rất minimal — chỉ có 1 PHP class là `StripeGateway`.

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
| `PaymentGateway` | `stripe` (inferred) | `StripeGateway` | Tích hợp Stripe Checkout / Payment Intents API |

---

## Hook Implementations

Không có `.module` file.

---

## Templates & Cache Strategy

Không có template.

---

## Related Units

- **C05** `03-progress-commerce.md` — layer overview
- `lms_payment.md` — plugin system base
- `lms_order.md` — consumer

---

## Known Issues / Debt

- Module scaffold-only level — chỉ có 1 class. Chưa rõ Stripe API version được support.
- Không có webhook route để nhận Stripe webhook (payment_intent.succeeded, etc.).
- Không có `lms_stripe.services.yml` → nếu cần config (secret key), phải dùng Drupal config hoặc Key module.
