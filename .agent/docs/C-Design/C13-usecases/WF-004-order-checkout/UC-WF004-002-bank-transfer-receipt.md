---
usecase_id: UC-WF004-002
workflow: WF-004
title: Bank transfer checkout — receipt upload and admin approval
type: edge
status: as-built
---

# UC-WF004-002 — Bank Transfer Checkout (Edge Path: Manual Approval Required)

Covers the bank transfer payment path where the order stays in `pending` status until an admin manually approves it after reviewing the uploaded receipt. This is a two-actor, asynchronous flow — payment is not confirmed programmatically.

---

## Actors

| Actor | Role |
|-------|------|
| Student | Authenticated user selecting bank transfer |
| System | Order creation, redirect, receipt storage |
| Admin | Reviews order in workspace; manually sets status to `completed` |

---

## Preconditions

1. Student is authenticated.
2. `lms_course` entity exists with `field_price_type = paid`.
3. `lms_payment.settings` has `enable_bank_transfer = TRUE`, `bank_bin`, `bank_account_no`, `bank_account_name`, and `bank_instructions` configured.
4. VietQR parameters are present in `lms_payment.settings` (used to render the payment QR code).
5. Admin has `administer lms_order` permission.

---

## Trigger

Student visits `/checkout/{course_id}`, selects "Bank Transfer" from the payment method radios, and submits the checkout form.

---

## Main Flow

| # | Given / When / Then | Entity / Code |
|---|---------------------|---------------|
| 1 | **Given** the student is on the checkout form and selects `bank_transfer` | `CheckoutForm::buildForm()` rendered payment method radios from `lms_payment.settings`; student selects `bank_transfer` radio |
| 2 | **When** the student submits the checkout form | `CheckoutForm::submitForm()` reads `payment_method = bank_transfer`; dispatches to `BankTransferGateway::processPayment()` via `plugin.manager.payment_gateway` |
| 3 | **When** `BankTransferGateway::processPayment()` runs | Logs a notice; returns `['status' => 'pending', 'transaction_id' => 'MANUAL_{order_id}']` |
| 4 | **Then** `CheckoutForm::submitForm()` creates the order | Creates `lms_order` (field_status=`pending`, field_payment_method=`bank_transfer`, field_payment_id=`MANUAL_{id}`, field_total=price); creates `lms_order_item` |
| 5 | **Then** the student is redirected to `/checkout/bank-transfer/{lms_order}` | `CheckoutController::bankTransferInstruction()` loads order; verifies `field_status = pending`; builds `BankTransferForm`; renders `lms_bank_transfer` Twig template showing VietQR parameters |
| 6 | **When** the student uploads their payment receipt | `BankTransferForm` renders a `managed_file` widget (accepts png/jpg/jpeg, max 5 MB); student selects file and submits form |
| 7 | **When** `BankTransferForm::submitForm()` runs | Marks uploaded file as permanent; attaches file to `lms_order.field_payment_receipt`; saves order; redirects to `<front>` — **order remains `pending`** |
| 8 | **When** admin reviews at `/workspace/orders/{lms_order}/edit` | `AdminController::orderEditForm()` embeds `ApproveOrderForm`; admin sees order details, receipt image, and status select |
| 9 | **When** admin sets status to `completed` and saves | `ApproveOrderForm::submitForm()` saves `lms_order.field_status = completed`; Drupal fires `hook_entity_update` |
| 10 | **Then** `lms_order_entity_update()` fires | `_lms_order_process_completed_order()` detects `field_status = completed`; creates `lms_user_course`; sends `order_completed` email; creates `lms_notification` |
| 11 | **Then** student is enrolled | `lms_user_course` exists; student can access course content |

---

## Entities Mutated

| Entity | Fields Changed | When |
|--------|---------------|------|
| `lms_order` | `uid`, `field_order_number`, `field_items`, `field_total`, `field_status=pending`, `field_payment_method=bank_transfer`, `field_payment_id=MANUAL_{id}` | Step 4 |
| `lms_order_item` | `field_course`, `field_type=course`, `field_price` | Step 4 |
| `lms_order.field_payment_receipt` | Image file (managed file entity referenced) | Step 7 |
| `lms_order.field_status` | `pending` → `completed` | Step 9 |
| `lms_user_course` | `uid`, `field_course` | Step 10 |
| `lms_notification` | `uid`, notification payload | Step 10 |

---

## Observability Hooks

| Signal | Where | Detail |
|--------|-------|--------|
| Log notice | `BankTransferGateway::processPayment()` | `\Drupal::logger()` logs payment intent as manual pending |
| Receipt file stored | `BankTransferForm::submitForm()` | Managed file at `public://payment_receipts/YYYY-MM/`; attached to `lms_order.field_payment_receipt` |
| Admin list | `OrderListBuilder` at `/manage/orders` | Shows order with status `pending`, colour-coded; "View / Edit / Approve" operation link |
| Email to student | `hook_mail` key `order_completed` | Sent only after admin approves (step 9 triggers `_lms_order_process_completed_order()`) |
| In-app notification | `_lms_order_process_completed_order()` | `lms_notification` entity created for student at approval time |

---

## Error Sub-Flow: Admin rejects (sets status to `cancelled`)

| # | Condition | System Response |
|---|-----------|----------------|
| 9a | Admin sets status to `cancelled` | `lms_order.field_status = cancelled`; `hook_entity_update` fires but `_lms_order_process_completed_order()` is NOT called (function only triggers on `completed`); no enrollment |
| 10a | Student is not enrolled | No `lms_user_course`, no notification; student must retry checkout |

---

## Acceptance Link

| Acceptance Criterion | Test Definition |
|---------------------|----------------|
| [AC-039](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md#ac-039-bank-transfer-checkout-creates-a-pending-order-and-shows-upload-form) — Bank transfer creates pending order and shows upload form | E03-TEST-WF004-002-A |
| [AC-040](../../../B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md#ac-040-webhook-triggered-order-completion--blocked--not-implemented) — Webhook completion (blocked, not implemented) | E03-TEST-WF004-002-B |

---

## Notes

- The bank transfer path is inherently asynchronous: enrollment cannot happen until an admin manually intervenes. There is no automated receipt verification.
- VietQR is the configured QR payment format for Vietnamese bank transfers; parameters are read from `lms_payment.settings` (bank_bin, bank_account_no, bank_account_name).
- Receipt file upload is limited to PNG/JPG/JPEG at max 5 MB; stored at `public://payment_receipts/YYYY-MM/` (month-partitioned directory from `field_payment_receipt` definition added in `lms_order_update_10001`).
- The `ApproveOrderForm` is registered as a route handler at `lms_course.admin_order.edit` (`/workspace/orders/{lms_order}/edit`) — it lives in `lms_course` module, not `lms_order`.
