# Chiến Lược Sinh Dữ Liệu Hành Vi Người Dùng (User Data Generation)

## Mục Tiêu
Mục tiêu là tự động hóa quá trình tạo và nạp (import) các dữ liệu giao dịch và hành vi người dùng cốt lõi cho nền tảng EverLMS. Những dữ liệu này bao gồm việc theo dõi tiến độ học kì bài, kết quả bài Quiz, lịch sử mua hàng, thông báo hệ thống, ghi chú cá nhân và các gói membership đã mua. Sở hữu một hệ sinh thái người dùng "sống động" là yêu cầu bắt buộc để demo các trang Dashboard, phân tích hành vi học viên và tính năng báo cáo của Quản trị viên.

## Phạm Vi Các Dữ Liệu Bị Thiếu
Danh sách các thực thể đang hiển thị `0 count` khi thực thi thao tác "dọn dẹp" (`clean`) thông qua terminal:
1. **Student Notes** (`lms_note`): Ghi chú khóa học cá nhân
2. **Notifications** (`lms_notification`): Thông báo hệ thống
3. **Certificates** (`lms_user_certificate` - chứng chỉ được cấp phát chính thức)
4. **Quiz Results & Responses** (`lms_question_response`, `lms_quiz_result`): Kết quả làm bài thi
5. **User Progress** (`lms_user_course`, `lms_user_lesson`): Dấu chân truy cập và tiến độ
6. **Orders & Order Items** (`lms_order_item`, `lms_order`): Lịch sử e-commerce
7. **Memberships** (`lms_user_membership`): Đăng ký gói hội viên
8. **Wishlists** (`lms_wishlist`): Khóa học yêu thích

## Kế Hoạch Cấu Trúc Tổng Thể

### 1. Script Sinh Dữ Liệu Random (`.agent/scripts/generate_user_data.php`)
Biên soạn một file PHP hoàn toàn mới giữ chức năng như một "Bộ não", đọc cấu trúc Lõi (Modules Khóa học, User, Trắc nghiệm) đã được dựng sẵn từ Python `generate_content.py`. 
- **Luồng Thực Thi Vận Hành**:
  1. Phân tích các file JSON cơ sở (Danh sách User, Khóa Học, Các bài Quiz).
  2. Bốc ngẫu nhiên học viên (User) rồi gán đăng ký (enroll) ngẫu nhiên vào $N$ số lượng Khóa Học.
  3. **Tracking Tiến Độ**: Giả lập quá trình mở bài giảng (`lms_user_lesson`), và tính toán ngẫu nhiên % hoàn thành tổng của Khóa (`lms_user_course`).
  4. **Chấm Điểm Thi**: Đối với những bài Quiz đã vượt qua, tung xúc xắc và tạo phiếu điểm (`lms_quiz_result`).
  5. **Tặng Chứng Chỉ**: Nếu khóa học nào đạt đúng 100%, tự động bóp cò sinh giấy chứng nhận bằng chứng (`lms_user_certificate`).
  6. **Đơn Hàng Ảo**: Bấm mua ảo `lms_order` và assign ngẫu nhiên các Membership VIP (`lms_user_membership`).
  7. **Râu ria khác**: Rải random Ghi chú (`lms_note`), và nhét các Khóa học chưa mua vào Wishlist (`lms_wishlist`).
- **File Out-Put**: Script phóng ra các file JSON chi tiết cắm thẳng vào máy xuất thư mục `.agent/generated/user_data/`:
  - `notes.json`
  - `notifications.json`
  - `certificates.json`
  - `quiz_results.json`
  - `progress.json`
  - `orders.json`
  - `memberships.json`
  - `wishlists.json`

### 2. Khai Báo Manifest  (`.agent/generated/manifest.json`)
Gắn kèm mã xác thực của các file JSON mới này vào quy trình nạp thứ tự `import_order`. Bọn này bắt buộc phải nạp CUỐI CÙNG (sau khi đã có sẵn Users và Courses để đối chiếu User ID / Course ID).

### 3. Quy trình nạp Drush ở `ContentToolsCommands.php`
Bộ chỉ huy nạp dữ liệu `lms_content_tools` sẽ cần bổ sung khối lệnh "đòn bẩy":
- **Router File**: Hàm `importFile()` chuyên chặn các tệp tin xuất phát từ thư mục `user_data/`.
- **Hành Động Nhét Dữ Liệu (Import Methods)**: Viết thêm 7 cổng náp chính xác:
  - `importUserNotes(array $data)`
  - `importUserProgress(array $data)`
  - `importUserCertificates(array $data)`
  - `importUserOrders(array $data)`
  - `importUserMemberships(array $data)`
  - `importUserWishlists(array $data)`
  - `importQuizResults(array $data)`
- **Safety Tường Lửa**: Tất cả lệnh đều phải kiểm tra độ trùng lặp (`Idempotency`), để lỡ Drush chạy đè 2 lần cũng không bị sinh thêm bản sao ảo của Tiến Độ hay Chứng Chỉ.

## Hướng Triển Khai Tiếp Theo

1. Mã hóa kịch bản sinh liệu `generate_user_data.php`.
2. Áp các chỉnh sửa Manifest.
3. Kẹp các hàm Entity Manager tương ứng vào Drush Commander.
4. Chạy kiểm chứng chuỗi: `php generate_user_data.php` rồi táng lệnh bơm `ddev exec drush everlms:import --type=user_data`.
