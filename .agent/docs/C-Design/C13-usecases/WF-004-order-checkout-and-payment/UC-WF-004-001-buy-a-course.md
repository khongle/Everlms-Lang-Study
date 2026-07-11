---
usecase_id: UC-WF-004-001
workflow: WF-004-order-checkout-and-payment
status: as-built
primary_actor: Student
modules: [lms_order, lms_payment, lms_stripe]
processes: [order-checkout-and-payment]
---

# UC-WF-004-001 - Buy a course

## Intent
Một buyer mua một course và có được quyền truy cập.

## Workflow context
| Item | Value |
| ---- | ----- |
| Workflow | WF-004 |
| Business flow | cart → coupon → pay/upload receipt → entitlement |
| Modules | lms_order, lms_payment, lms_stripe |
| Related features | F-010, F-011 |
| B04 source | WF-004 |

## Actors
Buyer (primary); gateway; manager (xác nhận bank).

## Preconditions
Đã authenticated; course có trả phí; gateway đã bật.

## Trigger
Buyer xác nhận checkout.

## Main success scenario
1. Mở checkout (P029); review item.
2. Có thể áp coupon (WF-009).
3. Chọn gateway (stripe/bank_transfer/…); thanh toán.
4. Gateway online thành công → order paid/completed; enrollment được cấp (WF-001).
5. Đường bank transfer thành công → order giữ `pending`, buyer thấy QR/hướng dẫn thanh toán, upload biên lai, order chuyển sang `processing`.
6. Manager mở P036 `/workspace/orders`, filter `processing`, review biên lai, và đánh dấu order `completed`; entitlement được cấp (WF-001).

## Alternate and exception flows
- Bị declined → order pending; retry/đổi gateway.
- Bank transfer chưa có biên lai → giữ `pending`.
- Bank transfer đã upload biên lai → `processing` cho tới khi manager xác nhận (P036).
- ⚠️ Chưa có webhook (R-002) → phải dựa vào synchronous return.

## State transitions (evidence)
- `draft` → `submitted`: buyer xác nhận checkout.
- `submitted` → `in_review`: biên lai bank transfer đã upload.
- `in_review` → `approved_or_completed`: manager xác nhận thanh toán.
- `submitted` → `rejected_or_returned`: gateway declined/cancelled.
- `approved_or_completed` → `archived`: hoàn tiền thủ công.

## Entry pages
| Page | Category | Path |
| ---- | -------- | ---- |
| Checkout | App | /checkout/{course} |
| Bank transfer instructions | App | /checkout/bank-transfer/{lms_order} |
| Manage orders | Manage | /workspace/orders |

## Supporting pages
Administrator cấu hình gateway tại Payment settings (P047) trước khi checkout khả dụng. Manager theo dõi doanh thu/đơn hàng tại Reports workspace (P045) và xuất báo cáo chi tiết tại Analytics reports (P049).

## Acceptance and test contract
- Order trả phí online → entitlement; bị declined → không có quyền truy cập (B05 F-010/F-011).
- Order bank transfer ghi đúng tổng tiền phải trả, hiển thị hướng dẫn transfer, cho upload biên lai, chuyển sang `processing`, và chỉ cấp entitlement sau khi manager approve.
- Danh sách order của manager hỗ trợ filter `Processing` và hiển thị đầy đủ Order ID, learner, số tiền, ngày, status badge, và action review/edit hiển thị được, không bị cắt ở độ rộng desktop chuẩn.
