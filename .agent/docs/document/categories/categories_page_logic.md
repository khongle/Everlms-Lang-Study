# Logic Trang Danh Mục Khóa Học (`/categories`)

## Tổng Quan (Overview)
Trang Danh mục Khóa học đúng như tên gọi, nó đóng vai trò là một danh bạ trực quan hiển thị tất cả các danh mục khóa học đang hoạt động trên nền tảng. Các danh mục được hiển thị dưới dạng các thẻ (cards) trong một lưới CSS (Grid) đáp ứng. Điểm nhấn lớn nhất là thanh tìm kiếm ở đầu trang giúp lọc kết quả phía máy khách (client-side) theo thời gian thực mà không cần tải lại trang.

## 1. Backend Controller (`CoursePublicController::categories`)
- **Đường dẫn (Route):** Mapped map từ route `lms_course.category` sang URL `/categories`.
- **Khai thác dữ liệu:** 
  - Lấy tất cả các Taxonomy term từ kho từ vựng `course_category` bằng cách dùng Helper do bạn viết sẵn.
  - Sau đó gọi lệnh Query vào Entity `lms_course` để đếm xem có bao nhiêu khóa học *đã xuất bản* (`status = TRUE`) gắn với mỗi ID danh mục (`field_category`).
- **Logic Lọc dữ liệu:** 
  - Chỉ những danh mục có chứa ít nhất 1 khóa học (`$count > 0`) mới được trả về Frontend. Các danh mục rỗng sẽ bị loại bỏ an toàn để tránh việc user click vào mà không có dữ liệu (dead-end).
- **Đóng gói Payload:** 
  - Vòng lặp duyệt qua các danh mục hợp lệ lấy được ảnh đại diện (Thumbnail - `field_category_thumbnail`). Nó cũng tự động convert Managed file thành URL tuyệt đối (Absolute URL).
  - Array `$active_categories` chứa 3 trường (`entity`, `course_count`, `thumbnail_url`) sẽ được quăng thẳng ra Twig template `lms_categories`.

## 2. Frontend Template (`lms-categories.html.twig`)
- **Cấu trúc Lập trình (Layout):** 
  - Toàn bộ trang được gói gọn bằng thẻ `<section class="lms-section">`.
  - Có thanh công cụ `lms-manage__header` chứa thẻ `<h1>` và một thanh Search linh hoạt (`#category-search-input`).
  - Khu vực chính hiển thị danh sách dạng thẻ bằng Grid CSS (`.lms-categories-grid`), tự động bẻ cột dựa trên kích cỡ màn hình bằng thuộc tính `repeat(auto-fill, minmax(...)`.
- **Cấu trúc Thẻ Danh Mục (`.lms-category-card`):**
  - Toàn bộ thẻ hoạt động như một thẻ `<a href="...">` lớn chuyển hướng tới trang chi tiết (`lms_course.category_detail`).
  - **Thumbnail:** Hiển thị `thumbnail_url` qua thẻ `<img>` xài `object-fit: cover`. Nếu danh mục không có avatar, nó dự phòng hiển thị một biểu tượng sách bằng FontAwesome (`<i class="fas fa-book"></i>`).
  - **Nội dung:** Tựa đề in bằng thẻ `<h3>`. Mô tả được lọc sạch mã HTML (stripped) và giới hạn độ dài bằng CSS `-webkit-line-clamp` (2 dòng).
  - **Huy hiệu:** Thẻ đếm số nằm ở dưới cùng góc trái, báo cho người dùng biết có bao nhiêu khóa học bên trong (Ví dụ: `<span class="lms-badge">5 Courses</span>`).

## 3. Tương tác Tìm kiếm Client-Side (Vanilla JS)
- Có một thẻ `<script>` nhỏ được nhúng trực tiếp ở đáy file Twig.
- **Sự kiện Lắng nghe:** Bắt sự kiện bàn phím `input` khi rõ chữ ở `#category-search-input`.
- **Hoạt động Tìm kiếm (Real-time Filtering):** 
  - Chuyển từ khóa thành chữ thường.
  - Vòng lặp duyệt qua **từng thẻ danh mục một** trên màn hình hiện tại.
  - Rút ruột lấy chữ ở phần Tiêu đề và Mô tả của mỗi thẻ.
  - Rà soát sự tồn tại hàm `.includes()`: Nếu thấy khớp từ khóa -> Giữ nguyên lệnh Style hiển thị `flex`. Nếu rớt đài -> Thay lệnh Style bằng `none` cất thẻ đi nhường chỗ. Mọi việc diễn ra rất chớp nhoáng mà không cần AJAX.

## 4. UI/UX & Styling (`_categories.scss`)
- Được thiết kế tối chuẩn xác theo bảng giao diện Nền tối (Dark Mode) (`--lms-card-bg`, `--lms-border-color`).
- **Hiệu ứng Hover:** Khi người dùng rê chuột vào, thẻ bị nhấc bổng 1 tý (`translateY(-4px)`), phủ bóng đè nặng hơn, đồng thời màu viền sáng lên trùng màu chủ đạo (`--lms-primary`). Đạt cảm giác cao cấp và tinh tế khi bấm.
