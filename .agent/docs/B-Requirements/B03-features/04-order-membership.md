# Feature: Order & Membership System

## 1. Tổng quan

Hỗ trợ bán khoá học đơn lẻ (one-time purchase) và subscription/membership plans. Tích hợp payment gateway: PayPal, Stripe, với khả năng mở rộng cho các gateway khác.

---

## 2. Order Management

### 2.1 Order Creation
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| O-01 | Shopping cart | Thêm nhiều khoá học vào giỏ hàng |
| O-02 | Checkout flow | Step: Review Cart → Login/Register → Payment → Confirmation |
| O-03 | Coupon/Discount | Áp dụng coupon code (% hoặc fixed amount) |
| O-04 | Tax calculation | Tuỳ chọn cấu hình thuế theo region |
| O-05 | Invoice generation | Tự động tạo invoice PDF |
| O-06 | Order confirmation | Email xác nhận đơn hàng |
| O-07 | Guest checkout | Cho phép checkout không cần đăng ký (tuỳ chọn) |

### 2.2 Order Administration
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| OA-01 | Order listing | Filter: status, date range, payment method, amount |
| OA-02 | Order status | Pending → Paid → Completed / Refunded / Cancelled |
| OA-03 | Manual order | Admin tạo đơn hàng thủ công (gratis enrollment) |
| OA-04 | Refund processing | Refund qua payment gateway hoặc manual |
| OA-05 | Order export | Export danh sách đơn hàng CSV/Excel |

---

## 3. Membership / Subscription

### 3.1 Membership Plans
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| M-01 | Plan creation | Tạo plan: name, description, price, duration (monthly/yearly/lifetime) |
| M-02 | Course access | Gắn courses vào plan (all courses hoặc specific set) |
| M-03 | Plan tiers | Multiple tiers: Basic, Premium, Enterprise |
| M-04 | Trial period | Thời gian dùng thử (days) |
| M-05 | Plan comparison | Trang so sánh plans |

### 3.2 Subscription Management
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| SM-01 | Auto-renewal | Tự động gia hạn subscription |
| SM-02 | Cancellation | User tự huỷ subscription |
| SM-03 | Upgrade/Downgrade | Chuyển đổi giữa các plan |
| SM-04 | Expiry notification | Thông báo trước khi hết hạn (7 ngày, 3 ngày, 1 ngày) |
| SM-05 | Grace period | Thời gian gia hạn sau khi hết hạn (configurable) |

---

## 4. Payment Integration

### 4.1 Payment Gateways
| # | Gateway | Tính năng |
|---|---------|----------|
| PG-01 | **Stripe** | Card payment, subscription billing, webhooks |
| PG-02 | **PayPal** | PayPal checkout, subscription billing |
| PG-03 | **Expandable** | Plugin architecture cho thêm gateway mới |

### 4.2 Payment Features
| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| PF-01 | Secure checkout | PCI DSS compliant, tokenized payments |
| PF-02 | Payment history | Lịch sử thanh toán cho user và admin |
| PF-03 | Failed payment retry | Tự động retry khi payment fail (subscription) |
| PF-04 | Currency support | Multi-currency (USD, VND...) |
| PF-05 | Revenue reports | Dashboard doanh thu theo ngày/tuần/tháng |

---

## 5. Pricing & Promotions

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| PP-01 | Course pricing | Fixed price per course |
| PP-02 | Bundle pricing | Giảm giá khi mua bundle (nhiều courses) |
| PP-03 | Coupon system | Tạo coupon code: % off, fixed, one-time/unlimited, expiry date |
| PP-04 | Flash sale | Giảm giá theo thời gian (start/end date) |
| PP-05 | Free courses | Khoá học miễn phí không qua checkout |
