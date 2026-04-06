# Kế Hoạch Triển Khai Giao Diện (UI Plan) - Khối Introduction Trang Chủ

## Mục Tiêu (Objective)
Hiện tại bạn đã sử dụng AI Agent tạo thành công Content Type `Introduction` và import các bài viết khởi tạo (như "About SilverCare Deutschland", "Why Choose SilverCare DE Training?", v.v.). Kế hoạch này sẽ phác thảo cách "kéo" các bài viết này ra hiển thị ngay trên Trang chủ (Homepage) với tư cách là các khối giới thiệu xen kẽ đẹp mắt, kế thừa phong cách Premium (Kính mờ, Bóng tự sáng) của nền tảng EverLMS.

## 1. Phương Pháp Lấy Dữ Liệu (Backend & Cấu trúc)
Thay vì code cứng (Hardcode) trực tiếp nội dung vào file twig trang chủ, ta sẽ áp dụng tính Động (Dynamic) của hệ thống:
- **Drupal Views (Gợi ý ưu tiên):** Tạo một View tên là `Homepage Introductions` (Type: Block). View này sẽ Query tất cả Node type `Introduction` đã publish, sắp xếp theo ngày hoặc Weight. Sau đó đẩy Block này ra Region trang chủ.
- Vòng lặp View sẽ render từng dòng dữ liệu bằng cách gọi custom template của node hoặc override trực tiếp của View (`views-view-fields--homepage-introductions.html.twig`). Không phụ thuộc controller.

## 2. Thiết Kế Giao Diện (Frontend & Bố Cục)

Thiết kế sẽ đi theo hướng **Split-Screen Z-Pattern** (Bố cục chia nửa màn hình xen kẽ theo hình chữ Z), mang lại cảm giác dễ đọc và chuyên nghiệp:

### 2.1. Container & Khối Bao Quanh
Mỗi khối Introduction sẽ chạy full width. Màu nền sẽ được tinh chỉnh dựa trên tham số `field_background_color` mà Node đang giữ:
- `<section class="lms-intro-block lms-intro-bg--{{ bg_color }}">`
- **Màu nền (Background):** Áp dụng class để xuất ra các sắc thái như Trắng thuần, Xám tinh tế, Xanh bóng đêm (Midnight Navy) hoặc thậm chí là Glassmorphism (Kính mờ trộn Gradient) để liền mạch với Orbs của website.

### 2.2. Lưới Giao Diện (Grid Layout)
Lõi bên trong sẽ là `<div class="lms-intro-grid">` sử dụng CSS Grid (hoặc Flexbox 50/50).

- **Đảo Chiều Tự Động (Image Position Logic):** 
  Phụ thuộc vào biến `image_position` (`left` hoặc `right`).
  CSS sẽ áp dụng thuộc tính `flex-direction: row-reverse;` (hoặc thay đổi `order` grid) để tự động đẩy luân phiên bức ảnh sang Trái hoặc sang Phải mà không cần viết 2 file twig khác nhau.

### 2.3. Hào Quang Văn Bản (Text Content Area)
- Phụ đề (`field_subheading`): Chuyển thành chữ IN HOA nhỏ, giãn chữ siêu rộng (`letter-spacing: 2px`), màu chữ Gradient (Ví dụ: Từ Tím Lavender sang Đỏ Cam). Đóng vai trò như một mồi nhử thị giác.
- Tiêu đề (`title`): Trở thành thẻ `h2` cực đậm (`font-weight: 800`), kích thước tầm `2.5rem` - `3rem` để ghim trọng tâm câu chuyện.
- Đoạn văn (`body`): Nội dung chữ với text-color xám trung tính, Line-height 1.7 dễ đọc.
- Nút Hành Động (`field_call_to_action`): Khởi tạo một nút `.btn-primary` rực sáng với lớp Shadow phát quang (`box-shadow: 0 0 15px rgba(x,y,z,0.4)`). Khi Hover nhô lên (`translateY(-2px)`).

### 2.4. Khu Vực Hình Ảnh (Image Area)
- Không quẳng ảnh trần trụi. Bức ảnh sẽ được bọc (Crop) bằng viền bo góc lớn (`border-radius: 1.5rem`).
- **Hiệu ứng Kính lơ lửng:** Tại viền ảnh, ta bổ sung bóng mờ đổ xuống mặt sàn. Bức tranh có thể được bọc bằng một dải "Stroke" mỏng 1px mờ (RGBA 255, 255, 255, 0.1) giống vỏ Macbook trong suốt.
- Có thể kết hợp Animation CSS thả nổi nhẹ nhàng (`keyframes float`) lên và xuống theo biên độ 10px để giao diện "chuyển động" dù người dùng chưa tương tác.

### 2.5. Trải Nghiệm Mobile (Responsive)
- Breakpoint màn hình nhỏ `< 768px`: Hủy Flex 50/50, chuyển sang `flex-direction: column`.
- Luân phiên Đảo Hình (`order`): Đảm bảo trên điện thoại di động, dù set `left` hay `right`, **Bức Hình luôn hiện lên phía Trên**, Nội dung chữ nằm phía Dưới để thuận mắt cuộn ngón tay. Văn bản căn giữa toàn bộ (`text-align: center`).

---

## 3. Câu Hỏi Xác Nhận (Bạn duyệt là tiến hành)

1. Bạn có đồng ý với phương án dùng **Drupal View Block** ráp ra trang chủ không? Hay bạn đang viết riêng một file Twig `/Controller` cho trang chủ giống như trang `/categories`?
2. Về "Màu Nền" (Bg Color) từ AI, bạn muốn mình tự do Map (chuyển đổi) các màu Hex đó ra class màu sắc đẹp nhất có sẵn của file SCSS hệ thống, hay là bám 100% mã màu Hex AI tạo vào thẻ `style="background: {color}"`?
