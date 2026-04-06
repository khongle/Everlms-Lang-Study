# Kế hoạch Thiết kế Mega Menu Khối Quản trị (EverLMS)

> Dựa trên yêu cầu mới nhất, chúng ta sẽ **tập trung hoàn toàn vào Hình số 2 (Mega Dropdown Menu)**. Menu này sẽ chỉ trực tiếp cho các tài khoản có Role hạng nặng là `administrator` hoặc `manager` nhìn thấy. Các tính năng khác (Sub-nav Hình 3 và Off-canvas Hình 1) tạm thời gác lại.

## User Review Required

> [!IMPORTANT]
> **Chốt Phương án Data Source (Hardcode Twig):** 
> Vì Menu này là đặc quyền riêng cho Admin và Manager, mình đề xuất **Hardcode (viết cứng) toàn bộ cấu trúc Cột và Link thẳng vào file giao diện Twig**. 
> - **Ưu điểm:** Tối ưu hiệu năng, Tốc độ render cực nhanh (không tốn 1 câu Query nào xuống vòng lặp DB của Drupal Tree), cực kỳ dễ gán Icon FontAwesome, và Code SCSS Lưới (Grid) sẽ không bao giờ bị vỡ Layout bởi người dùng thao tác sai.
> - Nếu Sếp duyệt định hướng chốt phương án Hardcode Twig này, mình sẽ ốp Code xuống luôn ạ!

## 1. Phân tích chức năng (Mega Account Menu)

**Mega Menu "My Account":** Khi Admin/Manager click vào Avatar / Tên User ở góc phải Top Header, thay vì xổ ra 1 cột List bình thường, một bảng điều khiển trung tâm (Dashboard Lưới) khổng lồ mang hơi hướng Dark Theme sẽ xổ xuống.

- Cấu trúc CSS Grid chia làm 4 Cột lớn rõ rệt (Tùy chỉnh khi code thật):
  - **Cột HỌC TẬP (Personal/Student):** Bảng tổng quan cá nhân, Khóa học của tôi, Chờ Thanh toán, Thành tích.
  - **Cột NỘI DUNG (Content Hub):** Dashboard Khóa học, Bài viết (Blog/News), Kho câu hỏi FAQ, Bộ Từ Vựng.
  - **Cột GIẢNG LỚP (Instructor):** Lớp tôi dạy, Học viên của lớp, Xem điểm, Chứng nhận.
  - **Cột QUẢN TRỊ (System Admin):** Quản lý Users, Quản lý Roles, Nguồn Lực (Finance/Orders), **Switch User**.
- *Chú ý:* Thẻ link nào cũng sẽ đi kèm 1 icon 14px xinh xắn bên cạnh để tăng mức độ chuyên nghiệp.

## 2. Chiến lược Code (Proposed Changes)

Mục tiêu thay đổi hoàn toàn nằm ở lớp Frontend Layer. Học viên (Student) hoàn toàn mù tịt và không thấy code HTML này.

#### [NEW] `web/themes/custom/lms_theme/scss/components/_mega-menu.scss`
Tạo file SCSS chuyên trang trí "Mega Menu Account":
- `position: absolute` neo ngay dưới My Account. 
- Thiết lập `display: grid` với các cột chia đều `grid-template-columns: repeat(4, 1fr)` (hoặc responsive auto-fit).
- Background chuẩn Dark Mode (Màu nền `var(--oc-n-800)` như hình mẫu).
- Mọi hiệu ứng Đổ bóng (Box-shadow cường độ cao), Fade-in mượt.

#### [MODIFY] `web/themes/custom/lms_theme/scss/main.scss`
- Import component SCSS vào luồng Bundle chính `@import 'components/mega-menu';`.

#### [MODIFY] `web/themes/custom/lms_theme/templates/navigation/menu--account.html.twig`
(Hoặc Override file `page.html.twig` / `block--system-menu-block--account.html.twig` - Tùy thuộc vị trí My Account đang đứng ở đâu trong Layout).
- Bao trọn toàn bộ khối HTML bằng câu lệnh kiểm duyệt quyền:
  `{% if 'administrator' in user.roles or 'manager' in user.roles %}`
- Viết thẳng HTML lưới 4 Cột vào trong khối IF này. Học viên (`student`) sẽ thoát khối này và chỉ render dropdown 1 Cột truyền thống (Đăng xuất, Xem Hồ sơ...).

## 3. Kế hoạch triển khai & Kiểm thử (Verification Plan)

### Bước 1: Khởi tạo Cấu trúc SCSS
- Cắm CSS Grid. Đảm bảo UI khớp 100% tỷ lệ Dark Mode, viền bo tròn và khoảng cách tương phản giống y như ảnh số 2.

### Bước 2: Tích hợp HTML/Twig & Nhúng Phân quyền
- Tìm đúng file block chứa Header User Profile của EverLMS.
- Viết khối điều kiện IF check `administrator` / `manager`.
- Nhúng toàn bộ mã HTML tĩnh của Mega Menu kèm các icon FontAwesome.

### Bước 3: Ráp Link Đích (Routing)
- Thay thế các thẻ `#` tạm thành URL/Route chuẩn của Drupal trỏ tới trang Quản trị thật. (Ví dụ: `path('open_switcher.frontend')`, `url('entity.user.collection')` ...).

### Bước 4: Kiểm thử bằng "Open Switcher" (Role Test)
- **Tài khoản test:** Quản đốc gốc (Manager).
- **Hành động 1:** Nhìn thấy Mega Menu, check thử các cột, bấm 1 link xác minh có trỏ đúng không.
- **Hành động 2:** Dùng `Open Switcher` mạo danh (Switch To) thành 1 tài khoản `Student`.
- **Hành động 3:** F5 lại, kiểm tra, Mega Dropdown KHỔNG LỒ buộc phải biến mất, không để lại dấu vết.
- **Hành động 4:** Trở về tài khoản Quản đốc (Switch Back) -> Mega Menu quay trở lại hùng vĩ như cũ.
