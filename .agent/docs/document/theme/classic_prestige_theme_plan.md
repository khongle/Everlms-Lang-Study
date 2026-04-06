# Kế hoạch triển khai Theme (Implementation Plan): "Classic Prestige" cho EverLMS

## Mục tiêu tổng quan
Xây dựng một giao diện (theme) Drupal HOÀN TOÀN MỚI và ĐỘC LẬP mang phong cách "Châu Âu Cổ Điển" (Classic European / Dark Academia). Theme mới này sẽ tồn tại song song với theme cũ (`lms_theme`), giúp quản trị viên có thể chuyển đổi qua lại giữa 2 giao diện một cách dễ dàng và an toàn từ trang Appearance của Admin.

## Chi tiết các bước thực hiện

### 1. Khởi tạo cấu trúc giao diện mới (New Theme Skeleton)
- **Thư mục chứa:** `web/themes/custom/lms_classic`
- **File hệ thống:** Tạo file `lms_classic.info.yml` khai báo Tên Theme là **EverLMS Classic Prestige**.
- **Kế thừa (Base Theme):** Kế thừa cấu trúc region của theme hệ thống mặc định (ví dụ Olivero/Stable9) hoặc clone chính xác các block layout regions từ `lms_theme` sang để khi switch theme, các block (như menu, footer) không bị vỡ hoặc mất vị trí.

### 2. Thiết lập Hệ thống Thiết kế (Design System & CSS/SCSS)
- **Màu sắc (Color Palette):**
  - **Màu nền (Background):** `Parchment Cream` (Kem giấy da, mã màu `#F9F8F3` hoặc `#F5F2E6`).
  - **Màu chủ đạo (Primary):** `Deep Burgundy` (Đỏ rượu trầm, mã `#800020`) hoặc `Navy Blue` (Xanh hải quân, `#0A192F`).
  - **Màu phụ đạo (Accent):** `Gold/Brass` (Vàng đồng thau, mã `#C5A059`) dùng cho các gạch nối, nút bấm hoặc viền mỏng.
  - **Màu chữ (Text):** Text chính đổi sang màu `Dark Charcoal` (`#2A2A2A`) để dễ nhìn trên nền kem.
- **Kiểu chữ (Typography):**
  - Import Google Fonts: Sử dụng font có chân `Playfair Display` cho TẤT CẢ các thẻ Heading (`H1`, `H2`, `H3`) nhằm tôn lên vẻ uy nghi, học thuật.
  - Thẻ Body (Nội dung) và nút bấm dùng `Lora` hoặc vẫn giữ `Inter` nhưng tinh chỉnh độ đậm.
- **Hiệu ứng đồ hoạ (UI Component Style):**
  - **XÓA BỎ** hoàn toàn các hiệu ứng công nghệ như Glassmorphism (trong suốt), Neon bóng mờ (Glow), bo góc quá tròn trịa (Radius lớn).
  - **THAY THẾ BẰNG:** Các khối (Cards, Banners) màu trắng solid/kem, viền mỏng 1px màu xám/vàng (`1px solid rgba(0,0,0,0.08)`), bo góc cực nhẹ (2px - 4px). Nút bấm chuyển sang dạng cổ điển (Fill màu burgundy hoặc viền vàng).

### 3. Cập nhật Twig Templates độc lập
- Tạo thư mục `web/themes/custom/lms_classic/templates/`
- Chép các file twig liên quan đến LMS (ví dụ: `lms-homepage.html.twig`, `lms-course-detail.html.twig`) từ module `lms_course` / theme cũ sang giao diện mới này.
- Loại bỏ các element đồ hoạ công nghệ bay lơ lửng (floating cards), thay cấu trúc hiển thị sang dạng cột tĩnh, phân chia khu vực rõ ràng như một tờ báo mạng danh giá hoặc tạp chí cổ điển.

### 4. Hướng dẫn cách Đổi Giao Diện sau khi hoàn thành
Sau khi theme được code và build xong:
1. Đăng nhập quyền Admin, vào mục **Appearance** (`/admin/appearance`).
2. Cuộn xuống khu vực *Uninstalled themes*.
3. Bấm **Install and set as default** tại theme `EverLMS Classic Prestige`.
4. Giao diện toàn hệ thống sẽ lập tức lột xác. Nếu muốn quay lại theme hiện tại, chỉ cần tìm `LMS Theme (cũ)` và set default trở lại.

## Review & Phê duyệt
Nếu bạn đồng ý với plan trên, mình sẽ bắt đầu tạo folder theme mới và viết code từng phần. Toàn bộ code sẽ được giữ hoàn toàn tách biệt ở thư mục `lms_classic`.
