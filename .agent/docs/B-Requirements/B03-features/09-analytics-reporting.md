# Feature: Analytics & Reporting

## 1. Tổng quan

Dashboard analytics cho admin, manager, và instructor. Hiển thị KPIs về enrollment, revenue, learning progress, và user engagement. Tích hợp Google Analytics.

---

## 2. Admin Dashboard

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| AD-01 | Overview KPIs | Total users, active users, total courses, total revenue |
| AD-02 | Enrollment trends | Biểu đồ enrollment theo ngày/tuần/tháng |
| AD-03 | Revenue dashboard | Doanh thu theo period, payment method, course |
| AD-04 | Top courses | Khoá học nhiều enrollment nhất |
| AD-05 | Course completion | Tỷ lệ hoàn thành trung bình |
| AD-06 | User activity | Daily/Monthly active users |
| AD-07 | Quiz performance | Tỷ lệ pass/fail tổng thể, average scores |
| AD-08 | New registrations | Users mới đăng ký theo thời gian |

---

## 3. Instructor Dashboard

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| ID-01 | My courses stats | Enrollment, completion, rating cho mỗi course |
| ID-02 | Student progress | Danh sách students + progress cho mỗi course |
| ID-03 | Quiz analytics | Score distribution, common wrong answers |
| ID-04 | Revenue share | Doanh thu chia sẻ (nếu áp dụng) |
| ID-05 | Engagement metrics | Video views, lesson completions, forum activity |

---

## 4. Reports

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| R-01 | Course report | Enrollment, completion, revenue per course |
| R-02 | User report | User progress, quiz results, certificates |
| R-03 | Revenue report | Tổng hợp doanh thu, breakdowns |
| R-04 | Quiz report | Results distribution, question-level analytics |
| R-05 | Activity report | Login frequency, lesson access patterns |
| R-06 | Export | Export tất cả reports dạng CSV/Excel/PDF |
| R-07 | Scheduled reports | Email reports tự động hàng tuần/tháng |

---

## 5. Google Analytics Integration

| # | Yêu cầu | Chi tiết |
|---|---------|---------|
| GA-01 | GA4 integration | Tích hợp Google Analytics 4 |
| GA-02 | Event tracking | Track: enrollment, course completion, quiz submission, payment |
| GA-03 | E-commerce tracking | Revenue tracking qua GA Enhanced E-commerce |
| GA-04 | Custom dimensions | Course category, user role, membership tier |
| GA-05 | Admin configuration | Admin cấu hình GA tracking ID |
