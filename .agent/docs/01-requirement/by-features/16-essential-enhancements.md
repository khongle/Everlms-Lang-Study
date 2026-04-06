# Feature: Essential Enhancements (Tier 1 + Tier 2)

> 10 tính năng thiết yếu bổ sung, không phá vỡ cấu trúc hiện tại.

---

## Tier 1: Bắt buộc phải có

### 1. Abandoned Cart Recovery

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| ACR-01 | Cart tracking | Lưu cart state khi user thêm course nhưng chưa thanh toán |
| ACR-02 | Reminder email | Gửi email nhắc sau 1h, 24h, 72h |
| ACR-03 | Email template | Template configurable với course info, CTA button |
| ACR-04 | Coupon incentive | Tùy chọn kèm coupon giảm giá trong email nhắc lần 3 |
| ACR-05 | Admin stats | Thống kê: carts abandoned, recovered, recovery rate |
| ACR-06 | Disable option | Admin tắt/bật abandoned cart email |

**Implementation:** Cron job trong `lms_order` module. Không entity mới — dùng `lms_order` status `cart` + `field_reminder_sent`.

---

### 2. Wishlist

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| WL-01 | Add to wishlist | Button ❤️ trên course card + course detail |
| WL-02 | Remove | Toggle wishlist on/off |
| WL-03 | Wishlist page | `/my-wishlist` — danh sách courses đã save |
| WL-04 | Count display | Hiển thị wishlist count trên icon header |
| WL-05 | Notification | Notify khi wishlist course giảm giá hoặc sắp hết hạn ưu đãi |
| WL-06 | Anonymous | Prompt login khi anonymous click wishlist |

**Implementation:** Entity nhẹ `lms_wishlist` (user_id, course_id, created). Page đã có sẵn UP-16.

---

### 3. Course Preview / Free Lessons

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| FP-01 | Preview flag | `field_is_preview` boolean trên `lms_lesson` |
| FP-02 | Preview count | Instructor set bao nhiêu lessons free preview |
| FP-03 | Anonymous access | Anonymous/unenrolled users xem được preview lessons |
| FP-04 | Lock indicator | Non-preview lessons hiện 🔒 icon + "Enroll to access" |
| FP-05 | Video preview | Preview lessons hiện full video nhưng content gated |
| FP-06 | Course detail | Badge "X Free Lessons" trên course detail page |

**Implementation:** Thêm 1 field vào `lms_lesson`. Update access check trong lesson controller.

---

### 4. Social Login

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| SL-01 | Google login | Login/Register bằng Google account |
| SL-02 | Facebook login | Login/Register bằng Facebook account |
| SL-03 | LinkedIn login | Login/Register bằng LinkedIn (optional) |
| SL-04 | Account linking | Link social account với existing email account |
| SL-05 | Auto profile | Pull avatar, name từ social profile |
| SL-06 | Login page UI | Social buttons trên login + register page |

**Implementation:** Drupal contrib `social_auth` module. Không custom entity.

---

### 5. Course Bundles

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| CB-01 | Bundle entity | Name, description, thumbnail, included courses |
| CB-02 | Bundle pricing | Giá bundle < tổng giá individual courses |
| CB-03 | Savings display | Hiển thị "Save X%" so với mua riêng |
| CB-04 | Bundle page | `/bundles` listing + `/bundles/{slug}` detail |
| CB-05 | Checkout | Mua bundle → auto-enroll tất cả courses |
| CB-06 | Admin CRUD | Admin tạo/sửa/xóa bundles |
| CB-07 | Homepage | Feature bundles trên homepage "Popular Bundles" section |

**Implementation:** Entity mới `lms_bundle` trong `lms_order` module.

---

## Tier 2: Nên có

### 6. Email Drip Campaigns

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| ED-01 | Welcome series | 3-email welcome sequence cho new users |
| ED-02 | Inactivity nudge | Nhắc user quay lại học sau 7/14/30 ngày inactive |
| ED-03 | Course completion | Congratulation email + suggest next course |
| ED-04 | Pre-expiry | Nhắc membership sắp hết hạn (7 ngày trước) |
| ED-05 | Admin templates | WYSIWYG editor cho email templates |
| ED-06 | Scheduling | Cron-based batch send, configurable send time |
| ED-07 | Unsubscribe | One-click unsubscribe link tuân thủ CAN-SPAM |

**Implementation:** Module mới `lms_email_campaign` hoặc mở rộng `lms_notification`.

---

### 7. Student Notes

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| SN-01 | Note per lesson | Ghi chú cá nhân cho từng lesson |
| SN-02 | Rich text | Basic formatting (bold, italic, lists) |
| SN-03 | Auto-save | Auto-save khi typing (debounce 2s) |
| SN-04 | Side panel | Panel bên phải lesson content, toggle show/hide |
| SN-05 | All notes view | `/my-notes` — xem tất cả notes theo course/lesson |
| SN-06 | Export notes | Download notes dạng PDF/TXT |
| SN-07 | Search notes | Tìm kiếm trong personal notes |

**Implementation:** Entity nhẹ `lms_note` (user + lesson + text + timestamps).

---

### 8. Calendar Integration

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| CAL-01 | Zoom sessions | Auto-add Zoom live sessions vào calendar |
| CAL-02 | Quiz deadlines | Add quiz deadline vào calendar |
| CAL-03 | iCal export | `/my-calendar.ics` endpoint — sync với Google/Apple Calendar |
| CAL-04 | Calendar view | Optional calendar view trong dashboard |
| CAL-05 | Reminders | Nhắc 1h trước live session |
| CAL-06 | Add to Calendar | Button "Add to Calendar" trên Zoom session page |

**Implementation:** iCal feed trong `lms_zoom` + `lms_base` module. Không entity mới.

---

### 9. Dark Mode

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| DM-01 | Toggle switch | Theme toggle button trên header (🌙/☀️) |
| DM-02 | System preference | Auto-detect `prefers-color-scheme` |
| DM-03 | Persistence | Save preference trong localStorage + user profile |
| DM-04 | Full coverage | Dark mode cho tất cả pages (public, portal, management) |
| DM-05 | Contrast ratio | WCAG AA contrast ratio compliance |
| DM-06 | Smooth transition | CSS transition khi switch theme |

**Implementation:** CSS custom properties (`--bg-primary`, `--text-primary`) + JS toggle. Theme-level only.

---

### 10. Instructor Revenue Dashboard

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| IR-01 | Earnings overview | Total earnings, this month, pending payout |
| IR-02 | Per-course revenue | Revenue breakdown theo từng course |
| IR-03 | Student analytics | Total students, new enrollments, completion rate |
| IR-04 | Payout history | Lịch sử thanh toán (nếu revenue sharing enabled) |
| IR-05 | Time filters | Filter theo date range: 7d, 30d, 90d, custom |
| IR-06 | Export | Export revenue report CSV |

**Implementation:** Page mới trong management portal. Dùng existing order/enrollment data.

---

## Entity Impact Summary

| Feature | Entity Change |
|---------|---------------|
| Abandoned Cart | Thêm `field_reminder_sent` vào `lms_order` |
| Wishlist | **NEW** `lms_wishlist` (user_id, course_id) |
| Free Preview | Thêm `field_is_preview` vào `lms_lesson` |
| Social Login | Không — dùng contrib module |
| Bundles | **NEW** `lms_bundle` (name, courses[], price) |
| Email Drip | Không entity mới — dùng cron + templates |
| Student Notes | **NEW** `lms_note` (user, lesson, text) |
| Calendar | Không — iCal export endpoint |
| Dark Mode | Không — CSS/JS only |
| Instructor Revenue | Không — dùng existing data |

> **3 entity mới nhẹ** + **2 field additions** = minimal impact.
