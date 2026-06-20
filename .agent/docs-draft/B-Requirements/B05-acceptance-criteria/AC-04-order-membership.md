# AC-04 — Order & Membership

**Feature ID:** F-004
**Epic:** EPIC-D — Commerce & Membership
**Epic Owner:** Student / Admin
**Related Workflow:** [WF-004 — Order Management](../B04-business-workflows/WF-004-order-management.md), [WF-005 — Membership Lifecycle](../B04-business-workflows/WF-005-membership-subscription.md), [WF-009 — Coupon Flow](../B04-business-workflows/WF-009-coupon-flow.md)
**Feature Doc:** [B03 F-004 Order & Membership](../B03-features/04-order-membership.md)

---

## Acceptance Criteria

### AC-D01 — Đơn thành công cấp quyền học

**Priority:** Must
**Given** Student hoàn thành thanh toán cho 1 paid course
**When** Payment gateway gửi success webhook
**Then** Order status = completed trong ≤30 giây
**And** UserCourse record tạo; student có thể access course ngay

### AC-D02 — Đơn fail không cấp quyền

**Priority:** Must
**Given** Payment gateway trả về failure event
**When** System xử lý failed payment webhook
**Then** Order giữ status=pending; không tạo UserCourse record
**And** Student thấy thông báo payment failed với link retry

### AC-D03 — Webhook idempotency

**Priority:** Must
**Given** Payment gateway gửi cùng 1 webhook event 2 lần (retry behavior)
**When** Cả 2 webhooks được nhận bởi system
**Then** Order state chỉ transition 1 lần
**And** Không có duplicate UserCourse records hoặc duplicate emails
**And** Webhook log ghi nhận duplicate với trạng thái "skipped"

### AC-D04 — Coupon validation & application

**Priority:** Must
**Given** Student có coupon code hợp lệ (chưa hết hạn, còn usage limit)
**When** Student nhập code ở checkout
**Then** Discount áp dụng đúng: percentage = floor(original * rate), fixed = original - discount
**And** Nếu có max_discount cap: discount không vượt cap
**And** Validation inline nếu code invalid (với reason cụ thể)

### AC-D05 — Membership access control

**Priority:** Must
**Given** Student A có active membership; Student B membership expired
**When** Cả hai truy cập cùng membership-only lesson
**Then** Student A xem được; Student B thấy "Renew Membership" wall
**And** Access check real-time (không cache stale membership status)

### AC-D06 — Membership lifecycle qua scheduler

**Priority:** Must
**Given** Membership với recurring plan và end_date là ngày mai
**When** Cron job chạy vào ngày expiry
**Then** Nếu payment success: end_date extend thêm 1 billing period
**And** Nếu payment fail: grace period bắt đầu; sau grace period: status=expired, access revoked
**And** Student nhận email reminder 7 ngày trước và notification khi expired

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| Order với coupon code vừa hit limit khi checkout | Validate tại submit time; race condition: first-come first-served với lock |
| Membership plan bị admin deactivate khi student có active membership | Existing memberships không bị ảnh hưởng; no new purchases |
| Refund request cho membership | Prorate hoặc full refund theo policy; access revoked |
| Student mua cùng course 2 lần | Second purchase blocked hoặc warning: "Already enrolled" |
| Cart với free + paid items | Free items enroll free; paid items go through payment |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Performance | Order completion ≤30s sau webhook |
| Security | Webhook signature verification bắt buộc |
| Reliability | Idempotency key ensure no duplicate processing |
| Availability | Payment flow degraded gracefully nếu gateway timeout |
