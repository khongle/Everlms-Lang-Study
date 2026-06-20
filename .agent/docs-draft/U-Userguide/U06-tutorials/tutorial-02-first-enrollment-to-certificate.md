# Tutorial: Từ Đăng ký đến Nhận Certificate

> End-to-end learner journey: register → enroll → học → quiz → certificate.
> Estimated time: **15 phút (đọc)**, thực thế tùy khóa dài bao lâu.

**Difficulty:** Beginner
**Prerequisites:** Không cần account.
**Features covered:** FEAT-01, FEAT-02, FEAT-03, FEAT-06, FEAT-08

## Goal

Hoàn thành 1 khóa miễn phí và lấy certificate công khai verify được.

## Steps

### Step 1: Đăng ký tài khoản

1. Mở trang chủ `/`.
2. Header → **Register** → điền email + password + name.
3. Submit → nhận email confirm → click link.
4. Login tự động sau confirm.

### Step 2: Browse & chọn khóa

1. Main menu → **Courses** (`/courses`).
2. Filter: Price = **Free**, Level = **Beginner**.
3. Chọn `Python Basics` (tutorial trước).
4. Đọc course detail: tabs Overview, Curriculum, Reviews.
5. Xem Preview video 1-2 min để đảm bảo phù hợp.

### Step 3: Enroll

1. Nút **Enroll Now** (khóa free).
2. Confirm → redirect tới **My Learning**.
3. Notification: "You've enrolled in Python Basics."

### Step 4: Bắt đầu học

1. `/my-learning` → click card `Python Basics` → **Resume**.
2. Lesson đầu tự động load (Chapter 1 / Lesson 1.1 - text).
3. Scroll đến cuối → hệ thống record completion (scroll ≥ 90%).
4. **Next** → Lesson 1.2 (video) → xem ≥ 80%.

### Step 5: Take Notes (optional)

1. Trong lesson → sidebar **Take Notes**.
2. Viết note markdown: `## Key points\n- Python is interpreted\n- Dynamically typed`.
3. Với video → click **Attach timestamp** để note gắn vào mốc thời gian.

### Step 6: Làm Quiz

1. Tiếp tục đến Chapter 2, sau 2 text lessons sẽ gặp Quiz lesson.
2. Click **Start Quiz** → timer 5 phút bắt đầu.
3. Trả lời 5 câu → **Submit**.
4. Kết quả hiện ngay:
   - Score: 80% → **Pass** ✓
   - Review từng câu + explanation.

### Step 7: Hoàn thành khóa

1. Tiếp tục Chapter 3 → lesson cuối.
2. Sau khi lesson cuối complete → `UserCourse.status = completed`.
3. Notification bell đỏ: "Course completed! Certificate issued."

### Step 8: Lấy Certificate

1. User menu → **My Learning** → tab **Certificates**.
2. Card `Python Basics` → **Download PDF**.
3. PDF mở kèm:
   - Tên bạn.
   - Tên khóa.
   - Ngày cấp (today).
   - Verification code (`CERT-2026-XXXXXXXX`).

### Step 9: Verify public

1. Copy URL: `/verify/{code}`.
2. Mở tab ẩn danh (incognito) → paste URL.
3. Page hiển thị: valid ✓, tên, khóa, ngày. Không cần login.

### Step 10: Share (optional)

1. Certificate card → **Share to LinkedIn** button.
2. LinkedIn "Add to profile" flow tự động điền course info.

## Verification

- [ ] Tài khoản active.
- [ ] Progress course = 100%.
- [ ] Certificate có PDF downloadable.
- [ ] Verify URL public accessible without login.
- [ ] Notes đã lưu vào `/my-learning/{course}/notes`.

## Next Steps

- **Đăng ký khóa khác:** `/courses` → tiếp tục hành trình học.
- **Tham gia community:** [learner-06-participate-forum.md](../U03-feature-guides/learner-06-participate-forum.md).
- **Review khóa đã học:** `/my-learning` → Completed → **Leave a review**.
- **Subscribe Membership** để access nhiều khóa exclusive: [customer-02-membership.md](../U03-feature-guides/customer-02-membership.md).

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Email confirm không đến | Check spam; nếu SMTP chưa config, admin xem log để get link. |
| Enroll xong không thấy ở My Learning | Hard refresh. Nếu vẫn: kiểm tra `/my-orders` (nên `success`). |
| Video không phát | U05 §Video playback. |
| Quiz submit 500 | U05 §Quiz submit. |
| Certificate không issue | Admin verify khóa có `CertificateTemplate` attach không. |
