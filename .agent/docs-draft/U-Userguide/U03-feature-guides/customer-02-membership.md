# Feature Guide: Customer — Membership & Subscription

**Feature ID:** FEAT-04 (order-membership)
**Available since:** v0.1.0 (checkout subject tới payment gateway caveats — xem [customer-01-checkout.md](customer-01-checkout.md))
**User roles:** Authenticated
**Prerequisites:** —

## What It Does

Membership cho phép user đăng ký 1 plan (monthly/annual/lifetime) để truy cập catalog courses exclusive hoặc giảm giá cho mua khóa riêng lẻ. Plans được admin định nghĩa qua `MembershipPlan` entity.

## Plan Structure

| Attribute | Example |
|-----------|---------|
| **Name** | Pro Monthly, Pro Annual, Lifetime |
| **Interval** | monthly / yearly / one-time (lifetime) |
| **Price** | VND amount |
| **Courses included** | All / selected categories / specific course IDs |
| **Discount %** | Giảm giá khi mua khóa ngoài scope (e.g., 20%) |
| **Trial period** | N days free trial (optional) |

## How to Use

### Bước 1: Xem Plans

1. Main menu → **Membership** → `/membership`.
2. Plan comparison table hiển thị tính năng của từng plan.
3. **Popular** badge đánh dấu plan được khuyến nghị.

### Bước 2: Subscribe

1. Click **Subscribe** trên plan mong muốn.
2. Nếu plan có **Free Trial** — checkbox accept terms → activate trial (không cần thẻ nếu policy cho phép).
3. Ngược lại → checkout như [customer-01-checkout.md](customer-01-checkout.md).
4. Sau payment success → `UserMembership` entity tạo với:
   - `status = active`
   - `starts_at = now()`
   - `ends_at = now() + interval` (null cho lifetime)
   - `auto_renew = true` (tùy plan)

### Bước 3: Sử dụng quyền lợi

- Khóa thuộc scope → nút **Enroll with Membership** (không mất phí).
- Khóa ngoài scope có discount — giá giảm hiển thị trên course detail.
- Badge **Member** trên profile (nếu bật).

### Bước 4: Quản lý subscription

- `/my-account/membership`:
  - Status: active / cancelled / expired / on_hold.
  - Next renewal date (nếu auto_renew).
  - Payment history.
  - **Cancel subscription** button — active đến cuối kỳ, không auto-renew.
  - **Upgrade / Downgrade** — chuyển plan; pro-rate calculation (TBD).
  - **Resume** (nếu đã cancel nhưng chưa ends_at).

### Bước 5: Expiration

- Email reminder gửi 7 ngày trước `ends_at` (nếu auto_renew off hoặc cancelled).
- Khi expires → `status = expired`; mất quyền access các khóa exclusive.
- Khóa đã enrolled trước đó **vẫn giữ** (lifetime access per course policy).

## Configuration Options (user)

| Option | Default | Description |
|--------|---------|-------------|
| Auto-renew | On (tùy plan) | Tự gia hạn khi đến kỳ |
| Email reminders | On | Nhắc 7/3/1 ngày trước hết hạn |
| Receipts by email | On | Email hóa đơn cho mỗi renewal |

## Tips & Best Practices

- **Trial free trước annual:** Nếu plan có trial, thử monthly trước để đánh giá content.
- **Check cancellation policy:** Cancel không refund phần kỳ còn lại; vẫn dùng đến hết kỳ.
- **Hạn chế spam subscription:** System block re-subscribe trong 24h sau cancel (admin setting).

## Known Limitations

- Upgrade/Downgrade pro-ration logic chưa complete (tracked TBD).
- Không hỗ trợ family/team plan trong MVP.
- Auto-renew dựa trên Stripe Subscription API — phụ thuộc FIND-001 fix.
- Gift subscription (tặng plan cho user khác) — roadmap v0.3.0.

## Related

- Feature spec: `B-Requirements/B03-features/04-order-membership.md`
- Workflow: `B-Requirements/B04-business-workflows/WF-005-membership-subscription.md`
- Entity: `C-Design/C04-entities/04-commerce-entities.md` (MembershipPlan, UserMembership)
- Checkout: [customer-01-checkout.md](customer-01-checkout.md)
- Admin: `U08-admin-guide.md` §Membership Plan Setup
