# Kế hoạch Triển khai Logic Testimonials (Trang chủ)

## Mục tiêu
Thay thế dữ liệu giả (mock data) hiện tại của khối "What Our Students Say" bằng dữ liệu thực tế từ hệ thống đánh giá của học viên.

## Phân tích Hiện tại
1. **Entity `Rating`**: Module `lms_rating` đã có entity `Rating.php` để lưu trữ đánh giá của học viên bao gồm các trường cốt lõi:
   - `owner` (người tạo)
   - `field_course` (khóa học)
   - `field_score` (điểm 1-5)
   - `field_review` (nội dung)
   - `field_approved` (trạng thái duyệt)
2. **Controller**: Màn hình trang chủ `CoursePublicController::homepage()` đang dùng một mảng `$testimonials` mock gán cứng hoàn toàn.
3. **Template**: `lms-homepage.html.twig` lấy dữ liệu đã load sẵn từ Controller, lặp qua và in ra nên không đụng chạm đến lớp UI.

## Các bước Thực hiện (Đã Triển Khai)

### ✅ Bước 1: Cập nhật Entity Rating (Bổ sung cờ Featured)
- Sửa đổi file `web/modules/custom/lms_rating/src/Entity/Rating.php`.
- Bổ sung định nghĩa trường `field_is_featured` (chuẩn kiểu Boolean) trong `baseFieldDefinitions()`.
- **Lý do**: Không phải học viên nào gửi đánh giá hoặc được Approved cũng đều xứng đáng xuất hiện ở mặt tiền trang chủ. Chúng ta cần cờ `is_featured` để Admin "chấm" và tự tay đẩy những review tốt nhất lên trang chủ.
- Chạy lệnh cập nhật (Entity Updates / hook_update_N) để cập nhật Schema Database.

### ✅ Bước 1.5: Xây dựng Form Đánh giá cho Học viên (Frontend Form)
- **Vấn đề**: Module `lms_rating` hiện tại chỉ mới định nghĩa `Entity` (Database), chưa hề có Controller hay Form để user thao tác gửi đánh giá.
- **Giải pháp**:
  - Tạo `RatingForm.php` ở `src/Form/RatingForm.php` hoặc nhúng Block Form vào cuối màn hình khóa học/chi tiết khóa học.
  - Cho phép người dùng (cần check quyền enrollment) chọn số sao (1-5) và nhập dòng review.
  - Khi submit, lưu Entity `lms_rating` mới với `field_approved = FALSE` (hoặc `TRUE` tùy config duyệt tự động) và `field_course` map với ID khóa học.

### ✅ Bước 1.8: Xây dựng Trang Quản Lý Đánh Giá (Admin View)
- **Vấn đề**: Học viên gửi đánh giá xong thì hiện đang không có chỗ nào để Admin/Manager "duyệt" hay gắn cờ "Featured". 
- **Giải pháp**:
  - Tạo một Drupal View (dạng Table hoặc Grid ở đường dẫn `/admin/lms/ratings`).
  - Trang này sẽ hứng toàn bộ Ratings (Entities_lms_rating) để Admin đọc nội dung, check sao.
  - Tích hợp thêm Custom Controller/Route hoặc Bulk Operations để Admin click "Approve" (Duyệt) và click "Mark as Featured" (Đưa lên trang chủ). 

### ✅ Bước 2: Viết logic lấy dữ liệu (Fetch Data) tại Controller
- Bỏ đoạn mock data `$testimonials` tại `web/modules/custom/lms_course/src/Controller/CoursePublicController.php` (ở `homepage()`).
- Sử dụng Entity Query cho kho lưu trữ `lms_rating`:
  - Lọc `field_approved = TRUE`.
  - Lọc `field_is_featured = TRUE`.
  - Sắp xếp mới nhất đưa lên đầu `sort('created', 'DESC')`.
  - Lấy lượng phần tử vừa đủ cho Carousel, tầm `range(0, 10)`.

### ✅ Bước 3: Build & Map Data sang định dạng UI yêu cầu
- Loop qua từng Rating Load được:
  - Bắt thông tin **Author** (Học viên) qua relation `owner`.
    - `name`: Ghép từ `field_first_name` và `field_last_name` của User (hoặc fallback về Display Name).
    - `role`: Lấy từ thông tin Profile `field_job_title` của Học viên (nếu có bổ sung).
    - `avatar`: Lấy Base URL từ `user_picture` của Account. Nếu trống, render một URL Avatar placeholder (từ UI Avatars) làm đẹp mặt tiền mặc định.
  - Bắt thông tin **Đánh giá**:
    - `content`: Trường `field_review`.
    - `rating`: Trường `field_score`.
- Gói gọn gàng trả vào mảng chung `[]` để view có thể bắt trọn và truyền tới `lms-homepage.html.twig`.

### ✅ Bước 4: Tối ưu Caching (Critical)
- Dữ liệu ở trang chủ được cache nặng. Gắn Cache Tag `lms_rating_list` hoặc Cache Tag theo List các Entity Rating để trang chủ tự động nhả cache ngay khi Admin gắn cờ Featured cho một Rating mới, giúp người dùng luôn xem được phản hồi xịn xò nhất ngay lập tức.
