# Feature Guide: Customer — Checkout (Stripe/PayPal)

**Feature ID:** FEAT-04 (order-membership)
**Available since:** v0.1.0 (⚠ stub implementation — xem Known Limitations)
**User roles:** Authenticated
**Prerequisites:** Có item trong cart.

> ⚠️ **FIND-001 + FIND-004 (Critical):**
> - Stripe `handleWebhook()` hiện trả 200 stub, chưa verify signature — **không production-ready**.
> - `/checkout` routes dùng `_access: TRUE` (anonymous) — đang tracked bởi TASK-015.
> Đợi TASK-002 + TASK-015 + U-060 hoàn thành trước khi bật production payment.

## What It Does

Checkout flow xử lý: cart → user info → payment (Stripe / PayPal) → order creation → enrollment/membership activation → confirmation email.

## How to Use (User Flow)

### Bước 1: Add to Cart

- Từ course detail / membership plan / bundle detail → **Add to Cart**.
- Cart icon header cập nhật count.

### Bước 2: Open Cart

- Click cart icon → review items.
- Quantity mặc định 1 (không thay đổi được cho course).
- **Remove** để xóa item.
- **Apply coupon code** → giảm giá nếu code valid + applicable.

### Bước 3: Checkout

1. Click **Checkout**.
2. Trang 1: **Billing info** (prefill từ user profile nếu đã lưu).
3. Trang 2: **Payment method**.
   - **Stripe:** Card number / expiry / CVC (Stripe Elements iframe).
   - **PayPal:** Redirect đến PayPal → login → approve → redirect back.
4. Review tổng tiền + tax (VAT nếu config).
5. Click **Pay Now**.

### Bước 4: Confirmation

- Payment success → redirect về `/checkout/complete/{order_id}`.
- Order confirmation hiện: order number, items, total.
- Email nhận trong vòng 1-2 phút.
- Enrollment/Membership active ngay sau webhook confirm (should happen < 30s trong production).

### Bước 5: Failure handling

- Card declined / user cancel → redirect về `/checkout/failed`.
- Cart giữ nguyên → user retry payment.

## Payment Gateways

| Gateway | Status | Supported | Notes |
|---------|--------|-----------|-------|
| **Stripe** | Stub (FIND-001) | Card (Visa/MC/Amex), Apple Pay, Google Pay | Cần U-060 webhook signature fix |
| **PayPal** | Stub (FIND-002, FIND-009) | PayPal balance, linked card | Cần rewrite (TASK-003) |
| **Bank transfer** | Manual | VN local banks | Admin xác nhận payment thủ công |

## Coupon & Discount

- Admin tạo coupon: `%` giảm / cố định amount / free membership.
- Coupon có: min_order_value, usage_limit (total + per_user), expires_at, applicable_to (courses/plans/bundles).
- Error states: `INVALID_CODE`, `EXPIRED`, `USAGE_LIMIT_REACHED`, `MIN_ORDER_NOT_MET`, `NOT_APPLICABLE`.

## Configuration Options (user)

| Option | Default | Description |
|--------|---------|-------------|
| Save card for later | Off | Stripe `setup_future_usage` — yêu cầu user consent |
| Default payment method | — | Prefill checkout next time |
| Billing address | From profile | Override per order |

## Tips & Best Practices

- **Check coupon before checkout** — apply code ở cart trang để thấy giá cuối.
- **Email notification:** Nếu không nhận trong 5 phút, check spam hoặc `/my-orders`.
- **Refund:** Request qua admin (không self-service trong MVP). Admin dùng runbook `RUN-order-refund.md`.

## Known Limitations (Critical)

- **Stripe webhook signature verification missing** (FIND-001, RISK-004) — do **không** enable Stripe cho real transaction.
- **PayPal chưa implement** (FIND-002) — không available trong production.
- **Order + enrollment atomicity** chưa có (FIND-005) — có thể có edge case: payment success nhưng enrollment fail, cần manual fix.
- **Refund flow** chưa automate.
- **Multi-currency** chưa hỗ trợ trong MVP (chỉ VND).

Tất cả bị block bởi **G-HARDENING** group (14 units pending).

## Related

- Feature spec: `B-Requirements/B03-features/04-order-membership.md`
- AC: `B-Requirements/B05-acceptance-criteria/AC-04-order-membership.md`
- Workflow: `B-Requirements/B04-business-workflows/WF-004-order-management.md`
- Integration: `B-Requirements/B06-integration-requirements/INT-001-stripe.md`, `INT-002-paypal.md`
- Entity: `C-Design/C04-entities/04-commerce-entities.md`
- Wireframe: `C-Design/C08-wireframes/09-checkout.md`
- Risks: G02 RISK-004, FIND-001, FIND-002, FIND-004, FIND-005, FIND-009
- Admin: `U08-admin-guide.md` §Payment Gateway Setup
