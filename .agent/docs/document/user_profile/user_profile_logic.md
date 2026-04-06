# Logic Trang Hồ Sơ Người Dùng (User Profile: `/user/{uid}`)

## Tổng Quan (Overview)
Trang Hồ sơ Người dùng đóng vai trò như một Dashboard cá nhân học tập (Learning Dashboard) dành cho từng học viên. Thay vì sử dụng bộ hiển thị mặc định khô khan của Drupal, giao diện đã được "chế" lại qua theme để bóc tách, tổng hợp và hiển thị những chỉ số học tập quan trọng nhất (Thống kê, Khóa học gần đây, Bài Test) của riêng người dùng đó.

---

## 1. Ghi Đè Routing & Template (Hook Suggestions)
- **Hàm `lms_theme_theme_suggestions_user_alter()`:**  
  Mỗi khi người dùng truy cập vào đường dẫn gốc của profile (`entity.user.canonical` - ví dụ: `/user/8`), hàm này sẽ "bắt cóc" luồng chạy và ép hệ thống phải sử dụng duy nhất file giao diện `user--full.html.twig` tĩnh mà chúng ta thiết kế. Bất chấp cài đặt View mode trong Admin là gì.

## 2. Tiền Xử Lý Dữ Liệu Backend (`lms_theme_preprocess_user()`)
Hàm này chịu trách nhiệm biến hình trang Profile thành một cỗ máy Query mạnh mẽ đi gom nhặt dữ liệu từ các Entity mở rộng:

- **Khóa học đã đăng ký (Courses Enrolled):** Đếm đếm số lượng bản ghi `lms_user_course` (Lịch sử học) của user này. Truyền qua biến `courses_enrolled`.
- **Khóa học đã hoàn thành (Courses Completed):** Giống như trên nhưng lọc thêm điều kiện `field_status = 'completed'`. Truyền qua biến `courses_completed`.
- **Chứng chỉ (Certificates):** Nếu có module chứng chỉ (`lms_certificate`), đếm số lượng huy hiệu chứng chỉ đã nhận.
- **Trung bình điểm bài Test (Quiz Average) & Quizzes gần đây:**  
  - Truy vấn vào Entity `lms_quiz_result`.
  - Lấy 5 bài test dạo gần đây nhất (Recent), trích xuất Tựa đề bài test, Điểm số, Đánh giá Đậu/Rớt (Giả định >= 80% là đậu), Ngày làm bài.
  - Lấy toàn bộ bài để tính trung bình cộng (Average Score) gán thành `quiz_average`.
- **Khóa học đang học dở (Active Courses):**  
  - Tương tự lấy 3 dòng `lms_user_course` gần nhất.
  - Load ngược lại (Reverse lookup) từ ID của khóa học lấy được Ảnh bìa (Thumbnail), Tựa đề, URL, và % Tiến độ học tập (`field_progress`). Đổ mảng vào biến `active_courses`.

## 3. Frontend Template (`user--full.html.twig`)
Cấu trúc trang được chia làm **1 Header nổi bật** và **1 Khung Grid chia 2 Cột**:

**A. Header (Nửa trên cùng):**
- Hiển thị Ảnh đại diện (`user_picture`), nếu không có tự động thế bằng icon FontAwesome `<i class="fas fa-user"></i>`.
- Thuật toán Ưu tiên Tên: Nếu User có nhập "First Name" "Last Name" -> In ra tên xịn (VD: Emily Chen). Nếu để trống -> Rớt xuống lấy tên tài khoản cơ bản. In kèm Chức danh (`Job Title`).

**B. Cột Trái (Main Details):**
- **Bio (Tiểu sử):** In nội dung giới thiệu, rỗng thì báo *This user has not provided a biography yet.*
- **Info Grid (Thông tin cơ bản):** Nơi dàn trận các trường siêu dữ liệu (Meta fields): Email, Vị trí, Công ty, Cài đặt thông báo, Mạng Xã hội.
- **Recent Courses:** Thẻ thông báo tiến trình khóa học với thanh cuộn Tiến độ (Progress Bar) được đổ gradient hiện đại `#667eea` to `#764ba2`. 
- **Recent Quizzes:** Hiển thị 5 bài test vừa thực hiện, màu chữ đậu rớt được render thành `#43e97b` (Xanh) hoặc `#f5576c` (Đỏ).

**C. Cột Phải (Learning Statistics Sidebar):**
- Đây là cái hộp Thống Kê tĩnh. In ra các chỉ số đã lấy từ Backend: 
  - Tổng khóa đã học, Khóa hoàn thành, Chứng chỉ, % Điểm trung bình.
  - Mỗi hàng đều có Icon đại diện.
  - Ở dưới đáy có nhãn **"Member Since"** format lại ngày tham gia cực đẹp (`member_since_timestamp|format_date(...)`).

--- 
📝 *Mọi tài nguyên từ Query logic trên backend cho tới Frontend UI đã được rà soát và nắm bắt trọn vẹn, sẵn sàng lên dĩa Cải tiến (UI Overhaul)!*
