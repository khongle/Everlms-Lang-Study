# C12 Workflow - WF-004 Order checkout & payment

**Object:** order

## Business source
[B04 WF-004](../../B-Requirements/B04-business-workflows/WF-004-order-checkout-and-payment.md)

## Modules
[lms_order](../C05-modules/lms_order.md), [lms_payment](../C05-modules/lms_payment.md), [lms_stripe](../C05-modules/lms_stripe.md)

## Related features (B03)
[F-010](../../B-Requirements/B03-features/F-010-orders-and-checkout.md), [F-011](../../B-Requirements/B03-features/F-011-payments-and-gateways.md)

## State model
`lms_order.field_status`:
| State | Meaning |
| ----- | ------- |
| draft (cart) | items added |
| submitted (pending) | checkout submitted, awaiting payment |
| in_review (processing) | bank transfer receipt uploaded; waiting manager verification |
| approved_or_completed (paid/completed) | payment captured → entitlement granted |
| rejected_or_returned (cancelled/failed) | payment failed/cancelled |
| archived (refunded) | refunded (manual) |

## Transitions
| From | To | Trigger |
| ---- | -- | ------- |
| draft | submitted | buyer xác nhận checkout |
| submitted | in_review | biên lai bank transfer đã upload |
| in_review | approved_or_completed | manager xác nhận thanh toán |
| submitted | rejected_or_returned | gateway declined/cancelled |
| approved_or_completed | archived | hoàn tiền thủ công |

## Permission model
- Initiator: buyer.
- Responsible: payment gateway (capture); manager xác nhận bank_transfer.
- Admin: manager sửa/hoàn tiền order.

## Manager review contract
- Upload biên lai bank transfer chuyển order sang `processing`; lúc này chưa được cấp entitlement course.
- `/workspace/orders` phải cho manager filter trực tiếp các order `processing`.
- Danh sách order của manager phải hiển thị đầy đủ Order ID, learner, số tiền, ngày, status badge, và action review/edit hiển thị rõ ở độ rộng desktop chuẩn.
- Manager approve chuyển order sang `completed`; đây là thời điểm entitlement course được cấp.

## Notifications
Xác nhận order + hướng dẫn nộp biên lai gửi buyer. Buyer chọn bank transfer upload biên lai; manager xác nhận sẽ chuyển order sang completed và cấp entitlement. ⚠️ Chưa có đối soát qua webhook (R-002) — các gateway online dựa vào synchronous return, còn bank transfer vẫn xử lý thủ công.

## Round-2 QA evidence
Đã verify end-to-end với buyer non-member thực tế: `julia.nowak` (uid 71) mua course trả phí 90 ($45) qua **Bank Transfer** → order 154 `processing` (đã upload biên lai, **chưa cấp entitlement**) → manager `Jenny Pham` filter `/workspace/orders?status=processing`, review qua `ApproveOrderForm`, set **Completed → order 154 `completed`**, và entitlement được cấp (`lms_user_course` 218 cho uid 71/course 90). Danh sách manager hiển thị đầy đủ Order ID / learner (họ tên đầy đủ) / số tiền / ngày / status badge / action Edit. Các phương thức thanh toán: Stripe, PayPal, Bank Transfer (VietQR); chip "Autofill" ở Stripe test-mode là artifact của Stripe Elements (không xuất hiện khi dùng live key), không phải code của app. Các fix từ round-1 (WF-004-001..003) vẫn giữ nguyên hiệu lực. Screens: `.agent/docs/E-Quality/E00-workflows/round-2/_screens/004-order-checkout-and-payment/`.
