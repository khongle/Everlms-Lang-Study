---
usecase_id: UC-001-04
workflow: WF-001-enrollment-learning
type: edge
---

# UC-001-04 — Bank Transfer Checkout → Pending Order (No Auto-Enrollment)

**One-line purpose:** Student selects bank transfer as payment method; the order is created with `pending` status and enrollment is NOT triggered until an admin manually approves the order. There is no webhook mechanism to automate this.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Authenticated user choosing bank transfer |
| `CheckoutForm` | Builds payment radios from `lms_payment.settings`; handles submit |
| `BankTransferGateway` | `lms_payment` plugin; returns `status=pending` |
| `CheckoutController::bankTransferInstruction()` | Renders the bank transfer instructions page |
| `BankTransferForm` | Accepts receipt image upload |
| Admin | Manually inspects receipt and changes order status to `completed` |

---

## Preconditions

- Student is authenticated.
- Bank transfer is enabled in `lms_payment.settings` (`enable_bank_transfer=1`); `bank_bin`, `bank_account_no`, `bank_account_name` are configured.
- Course is a paid course.

---

## Trigger

Student visits `/checkout/{course_id}`, selects "Bank Transfer" radio on the checkout form, and submits.

---

## Main Flow

| # | Given / When / Then |
|---|---------------------|
| 1 | **Given** student is on the checkout page and selects `bank_transfer` as payment method |
| 2 | **When** `CheckoutForm::submitForm()` runs |
| 3 | **Then** `plugin.manager.payment_gateway->createInstance('bank_transfer')` is called |
| 4 | **When** `BankTransferGateway::processPayment($order, [])` is called |
| 5 | **Then** the gateway logs a notice and returns `['status' => 'pending', 'transaction_id' => 'MANUAL_{order_id}']` |
| 6 | **Then** `CheckoutForm::submitForm()` detects `status=pending`; creates `lms_order` with `field_status=pending`, `field_payment_method=bank_transfer`, `field_payment_id=MANUAL_{id}` |
| 7 | **Then** `lms_order.module::hook_entity_insert()` fires but `field_status != completed` — `_lms_order_process_completed_order()` is NOT called; no enrollment occurs |
| 8 | **Then** student is redirected to `/checkout/bank-transfer/{lms_order}` |
| 9 | **When** `CheckoutController::bankTransferInstruction()` runs |
| 10 | **Then** it verifies order status is `pending`; returns `BankTransferForm` which renders `lms-bank-transfer.html.twig` with VietQR payment details (bank BIN, account number, amount) |
| 11 | **When** student uploads proof-of-payment image via the `managed_file` widget (png/jpg/jpeg, max 5 MB) |
| 12 | **Then** `BankTransferForm::submitForm()` marks the file permanent, attaches it to `lms_order.field_payment_receipt`, redirects student to `<front>` |
| 13 | **When** admin reviews the uploaded receipt in `/manage/orders` |
| 14 | **Then** admin manually edits the order and sets `field_status=completed` |
| 15 | **When** `lms_order.module::hook_entity_update()` fires and detects status transition to `completed` |
| 16 | **Then** `_lms_order_process_completed_order()` runs: creates `lms_user_course` enrollment, sends email, creates in-app notification |

---

## Entities Mutated

| Entity | Field | Change |
|--------|-------|--------|
| `lms_order` | `field_status` | `pending` (at checkout); `completed` (after admin action) |
| `lms_order` | `field_payment_method` | `bank_transfer` |
| `lms_order` | `field_payment_id` | `MANUAL_{order_id}` |
| `lms_order` | `field_payment_receipt` | image file (attached after student upload) |
| `lms_user_course` | all fields | created ONLY after admin sets status=completed |

---

## Observability Hooks

| Signal | Mechanism | Details |
|--------|-----------|---------|
| Notice log on bank transfer process | `\Drupal::logger` | "Processing bank transfer payment for order {id}" in `BankTransferGateway::processPayment()` |
| Receipt file upload | Drupal managed file | Stored at `public://payment_receipts/YYYY-MM/` |
| Admin order status change | `hook_entity_update` | Triggers enrollment when status reaches `completed` |
| Enrollment email | `hook_mail` `order_completed` | Sent only after admin approves |

---

## Acceptance Link

| B05 AC | E03 Test (planned) |
|--------|--------------------|
| [AC-039](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md#ac-039-bank-transfer-checkout-creates-a-pending-order-and-shows-upload-form) — Bank transfer creates pending order + shows VietQR | TEST-039 |
| [AC-040](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md#ac-040-webhook-triggered-order-completion--blocked--not-implemented) — Webhook not implemented (blocked) | TEST-040 |

---

## Notes

- **No webhook:** There is no async mechanism for the bank gateway to confirm payment automatically. Enrollment relies entirely on a human admin updating `field_status`.
- **No webhook for Stripe/PayPal either:** `StripeGateway::handleWebhook()` and `PayPalGateway::handleWebhook()` (in `lms_payment`) are stubs — they return HTTP 200 with no-op bodies. No webhook routes are registered in any module.
- Files are stored at `public://payment_receipts/YYYY-MM/` (directory pattern from `lms_order_update_10001`).
- Receipt field `field_payment_receipt` was added by the `lms_order_update_10001` update hook — installations before this update ran may not have the field.
