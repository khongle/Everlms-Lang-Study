# EverLMS - Phân tích Logic Hệ Thống Hiện Tại

Dự án EverLMS được xây dựng trên nền tảng **Drupal 11** (sử dụng PHP 8.3 với strict types và attributes). Hệ thống được module hóa cao với **23 custom modules** (chủ yếu bắt đầu bằng tiền tố `lms_`), quản lý thông qua Entity API cốt lõi của Drupal.

Dưới đây là danh sách phân nhóm Logic các module hiện hành của "con" EverLMS:

## 1. LMS Core & Content (Lõi E-learning & Nội dung)
Các module này định nghĩa cấu trúc nội dung từ lớn đến nhỏ của một hệ thống giáo trình:
- **`lms_base`**: Chứa các service dùng chung, utility functions, base entities, traits, interface cốt lõi của toàn bộ hệ thống.
- **`lms_course`**: Quản lý Khóa học (Course entity) - thực thể lớn nhất bao trùm các tài nguyên học tập.
- **`lms_chapter`**: Quản lý Chương (Chapter) - giúp phân chia Khóa học thành các phần nhỏ hơn.
- **`lms_lesson`**: Quản lý Bài học (Lesson entity) - nội dung chi tiết bài giảng (video, text, tài liệu).

## 2. LMS Assessment (Đánh giá & Khảo thí)
- **`lms_quiz`**: Cấu hình Bài trắc nghiệm / Bài kiểm tra cuối kỳ, bài test theo bài học.
- **`lms_question`**: Quản lý rổ Câu hỏi (Question bank) để nhúng vào bài Quiz.
- **`lms_certificate`**: Cấp phát và quản lý Chứng chỉ (Certificate) sau khi học viên hoàn thành khóa học đạt chuẩn.

## 3. LMS E-Commerce (Thương mại điện tử & Thanh toán)
- **`lms_bundle`**: Đóng gói nhiều Khóa học lại thành các gói (Bundle) để bán theo combo. 
- **`lms_order`**: Quản lý Đơn hàng (Orders) mua khóa học.
- **`lms_payment`**: Lớp Interface tổng xử lý thanh toán, lưu trữ giao dịch (Transactions).
- **`lms_stripe`**: Cổng thanh toán tích hợp thẻ qua Stripe.
- **`lms_paypal`**: Cổng thanh toán tích hợp qua PayPal.
- **`lms_wishlist`**: Cho phép user "Lưu danh sách yêu thích" các khóa học quan tâm.

## 4. LMS User Engagement (Tương tác & Xã hội)
- **`lms_forum`**: Diễn đàn thảo luận (hỗ trợ trao đổi giữa giảng viên và học viên hoặc học viên với nhau).
- **`lms_rating`**: Chức năng Đánh giá, chấm sao, và review khóa học, bài giảng.
- **`lms_faq`**: Quản lý các Câu hỏi thường gặp (Q&A) trong hệ thống/khóa học.
- **`lms_note`**: Chức năng Ghi chú cá nhân của học viên trên từng bài học (Lesson Notes).
- **`lms_badge`**: Hệ thống huy hiệu (Gamification) để tặng thưởng người học nhằm khích lệ.

## 5. LMS Progress & Tracking (Tiến độ & Phân tích)
- **`lms_progress`**: Tracking (Theo dõi) tiến độ khóa học/bài học, ghi nhận tiến độ % học tập của user.
- **`lms_analytics`**: Module phân tích, xuất báo cáo, Dashboard thống kê dành cho Admin và Giảng viên.

## 6. LMS Integration & Utilities (Tích hợp & Tiện ích mở rộng)
- **`lms_zoom`**: Tích hợp Zoom API cho các buổi Live Sessions / Webminar trực tuyến.
- **`lms_notification`**: Hệ thống thông báo tự động (push notifications, email alerts) gửi học viên khi có khóa học mới, bài chấm điểm, hoặc tương tác mới.
- **`lms_membership`**: (Có thể gói bán khóa học dạng Subscription hoặc cấp quyền ưu tiên).

### 💡 Tóm gọn luồng xử lý chính:
1. User truy cập hệ thống -> Xem danh sách **`lms_course`** -> Thêm vào giỏ hàng hoặc **`lms_wishlist`**.
2. Người dùng Checkout tạo **`lms_order`** -> Thanh toán qua **`lms_payment`** (`stripe`/`paypal`) -> Mở quyền truy cập theo **`lms_membership`** hoặc user.
3. User bắt đầu học -> Xem **`lms_chapter`**, học **`lms_lesson`** -> Track tiến độ tại **`lms_progress`**.
4. User làm bài kiểm tra ở **`lms_quiz`** (với các **`lms_question`**) -> Đạt yêu cầu hệ thống cấp **`lms_certificate`** cùng **`lms_badge`**. 
5. Sau khóa học user dùng **`lms_rating`** để đánh giá. Toàn bộ dữ liệu tổng hợp ra report tại **`lms_analytics`**.
