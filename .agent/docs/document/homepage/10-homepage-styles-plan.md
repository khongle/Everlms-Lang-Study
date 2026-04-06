# EverLMS: Kế hoạch Triển khai Giao diện Trang chủ (Giai đoạn 1)

Mục tiêu là thiết kế, phát triển và tích hợp các phong cách thẩm mỹ trang chủ hoàn toàn khác biệt cho EverLMS. Điều này sẽ cho phép Quản trị viên dễ dàng chuyển đổi "Giao diện và Cảm nhận" (Look & Feel) của LMS ngay trên giao diện Cài đặt (Settings).

> [!IMPORTANT]
> **Quyết định Giai đoạn 1 (Proof of Concept)**: Thay vì làm 10 giao diện cùng lúc, chúng ta sẽ tập trung hoàn thiện và thử nghiệm tính năng chuyển đổi với **2 Phong cách đầu tiên**. Sau khi cơ chế hoạt động trơn tru 100%, các bản mở rộng còn lại sẽ được triển khai vào Giai đoạn sau.

## 2 Phong Cách Trang Chủ Khởi Điểm

1. **Modern Glassmorphism (Bản hiện tại - Mặc định)**: Nền tối sâu, các khối thẻ dạng kính mờ (frosted glass), điểm nhấn là các cạnh viền phát sáng rực rỡ. Lý tưởng cho các nền tảng SaaS và công nghệ hiện đại.
2. **Minimalist Clean (Mới - Tối giản Mạch lạc)**: Rất nhiều khoảng trắng, sử dụng kiểu chữ đen đậm cắt ngang nền sáng màu (trắng/xám nhạt), viền mỏng, không có hiệu ứng gradient. Tôn vinh sự rõ ràng và chuyên nghiệp.

## Chiến lược Triển khai Cơ chế Chuyển đổi

### 1. Phân quyền và Đóng mở Tùy biến Section (Responsive Sections)
Mỗi Style trang chủ sẽ không chỉ khác nhau về Màu rẽ nhánh (CSS) mà còn khác biệt về **Cấu trúc Dữ liệu**. Tùy thuộc vào Style được phân quyền, một số Section nhất định sẽ được hiển thị hoặc ẩn đi hoàn toàn.
- *Ví dụ:* Style 1 (Glassmorphism) sẽ hiện Mục "Trusted By 200+ Companies", trong khi Style 2 (Minimalist) sẽ ẩn bớt mục này để tối đa hóa không gian và tập trung thẳng vào Form Đăng ký (Lead).
- Kỹ thuật: Thiết kế File Twig độc lập cho mỗi Style, giúp Admin "tự do bật/tắt" các khối nội dung (Block) ở từng Style mà không ảnh hưởng lẫn nhau.

### 2. Tầng Dữ liệu (Controller)
File `HomepageController.php` vẫn sẽ là **Nguồn dữ liệu Duy nhất** (Single Source of Truth). Dù sử dụng phong cách nào, Controller đều sẽ gọi toàn bộ dữ liệu (Khóa học nổi bật, Banners, Danh mục, Nhận xét) và đẩy sang Twig template. Twig sẽ là người tự quyết định dùng hay bỏ tùy theo Layout.

### 3. Giao diện Cấu hình Admin (Theme Settings Engine)
Thiết lập 1 bảng Cấu hình siêu đơn giản thông qua tùy biến của Drupal UI (`theme_settings.php`):
- **Giao diện Quản trị**: Nằm tại `Appearance > Settings > LMS Theme`.
- **Thao tác**: Tạo một tùy chọn danh sách thả xuống (Dropdown) với tiêu đề **"Select Homepage Style"**. Quản trị viên chỉ việc chọn A hoặc B và ấn *Save*, hệ thống sẽ ngầm chuyển đổi trong sấp xỉ 1 giây.

### 4. Kiến trúc Giao diện (Twig Component-Based Routing)
File `lms-homepage.html.twig` chính sẽ đóng vai trò **Bộ chia tín hiệu (Router)** thay vì chứa Code đồ sộ:
```twig
{% set active_style = theme_get_setting('homepage_style') | default('glassmorphism') %}

{% if active_style == 'minimalist' %}
  {% include '@lms_theme/home/styles/style-2-minimal.html.twig' %}
{% else %}
  {% include '@lms_theme/home/styles/style-1-glass.html.twig' %}
{% endif %}
```

### 5. Kiến trúc Styling (CSS Variables)
Sử dụng Biến Hệ thống (CSS Custom Properties). Việc chuyển đổi sẽ gán 1 CSS Class định danh vào thẻ `<body class="layout-minimalist">`, kích hoạt tự động việc lột xác màu nền, font chữ và bán kính bo góc (border-radius) toàn Layout mà không xuất hiện tình trạng xung đột CSS.

---

## Kế hoạch Kiểm thử (Verification Plan)
1. Thêm chức năng trong `lms_theme.theme` và `theme-settings.php` để tạo ra Select Box ở Backend.
2. Tái cấu trúc file trang chủ hiện tại thành `style-1-glass.html.twig`.
3. Xây dựng giao diện UI Trắng mới thành `style-2-minimal.html.twig`.
4. Bật Admin switch sang style thứ 2. Hệ thống tải ngay lập tức giao diện Trắng tinh khôi, ẩn đi một vài khối và không gặp lỗi vỡ Layout.
