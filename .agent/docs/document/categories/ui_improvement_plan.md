# Kế Hoạch Cải Thiện Giao Diện Trang Danh Mục Khóa Học (Categories UI/UX Plan)

## Mục Tiêu (Objective)
Biến trang hiển thị Danh Mục Khóa Học (`/categories`) hiện tại từ "OK" lên một đẳng cấp "Xuất xắc" (Premium). Thiết kế lại toàn bộ luồng giao diện sao cho nó đồng bộ với bộ "Glassmorphism & Neon Flow" vừa áp dụng thành công trên trang Danh sách khóa học (Course Catalog). Đặc biệt chú trọng cải tiến thanh tìm kiếm (Search Bar) để nó không còn bị chìm nghỉm vô danh mà phải trở thành vũ khí trải nghiệm xịn xò.

---

## 1. Cải Tiến Cấu Trúc Bố Cục (Layout Improvements)

**(A) Header đập tan sự dập khuôn**
- Không để tiêu đề `Course Categories` nằm chênh vênh với thanh tìm kiếm nữa. 
- Sắp xếp Header thành một khối trung tâm (Hero Section):
  - **Tiêu đề:** Nằm chính giữa với Typography cỡ lớn bự, gradient nhẹ.
  - **Đoạn mô tả ngắn (Subtitle):** Bổ sung dòng "Khám phá các khóa học phù hợp với định hướng của bạn."
  - **Thanh tìm kiếm:** Mở rộng to bự nằm chình ình giữa màn hình với biểu tượng Kính lúp bo tròn tinh xảo bên trái & Phím tắt (e.g., `Ctrl + K`) giả định bên phải.
  - **Background:** Đổ một vệt sáng Gradient (Glow) phía trong nền đằng sau thanh search tạo cảm ứng trọng điểm mắt nhìn.

**(B) Tái Cấu Trúc CSS Grid**
- Nâng cấp lưới từ cố định `minmax(320px, 1fr)` thành mượt mà linh động hơn.
- Gia tăng khoảng cách (Gap) giữa các thẻ từ `1.5rem` lên `2rem` cho khung thẻ thoải mái tản nhiệt.

## 2. Nâng Cấp Card Component (Premium Glassmorphism Cards)

**(A) Bề mặt (Surface)**
- **Glassmorphism:** Giống như Card khóa học, màu nền sẽ chuyển từ khối hộp rắn `var(--lms-card-bg)` thành nền bán trong suốt: `background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px);`.
- **Viền sáng (Borders):** Viền mỏng 1 pixel (light border layer) màu Gradient đổ viền tạo cảm giác trong vắt.

**(B) Hiệu Ứng Rê Chuột (Hover Interactions)**
- Tôn trọng hiệu ứng `translateY(-4px)` nhưng cần bổ sung cường độ Shadow. 
- Khi người dùng Hover, bên dưới cái card sẽ lan tỏa luồng sáng màu Neon cùng màu hạt giống với biểu tượng danh mục.
- Biểu tượng `icon` đang ở dạng tĩnh sẽ được thêm một **Animation cực kỳ lôi cuốn** (Ví dụ: Scale lớn lên 1.1x chút xíu rồi trở về nhịp nhàng).

**(C) Biểu Tượng & Thumbnail (Iconography & Badges)**
- Các hình Thumbnail (Ảnh vuông 3rem x 3rem) quá nhỏ (như trong ảnh AI for Business). Mình sẽ thổi form Icon này bớt bo tròn lại hoặc cho nó chảy rủ xuống thành Background nền trong suốt (Decal layout). Nhưng do Thumbnail do người dùng úp lên, tốt nhất là phóng to nó lên một góc (ví dụ: `4rem x 4rem`) và nhét vào cái viền bảo vệ.
- Chuyển thẻ Badge `5 Courses` thành kiểu dáng `Neon Pill` với viền tự phát sáng: `background: rgba(99, 102, 241, 0.1); color: #818cf8; border: 1px solid rgba(99,102,241, 0.3);`.

## 3. Cải Tiến Thanh Tìm Kiếm Trực Tiếp (Search Bar UI/UX)

- **Search Bar Mới:** Chuyển từ việc code cứng nội tuyến (`style="..."`) sang CSS tĩnh chuyên nghiệp.
- Mở rộng vùng tương tác của thẻ `input`, gỡ cái viền đen xì ra đổi thành viền vát xéo, bên trong kèm nút `[X]` chức năng để clear nhanh (tính năng ẩn do javascript bắt).
- Tích hợp thêm viền Glow tím rực rỡ khi User đang gõ (focus mode).
- Tương tác với JS (Search Client Side): Không giật cục biến mất ngay, thêm vào lệnh Animation tắt dần (`opacity: 0`). Nếu kết quả rỗng (Trường hợp `<div class="lms-empty-state">`), cần đổi nó thành bộ mặt UI mới mẻ hơn thay vì Icon `fa-folder-open`.

## Kết Quả Dự Kiến
- Trang Category sẽ không còn nhàm chán như một Dashboard Quản trị, mà thực sự lột xác trở thành một trang Landing Page Trải nghiệm Người Khám phá.
- Đổi mới toàn diện chuẩn UI của Website trở nên siêu nhất quán từ Catalogue => Course Card => Categories Card.
