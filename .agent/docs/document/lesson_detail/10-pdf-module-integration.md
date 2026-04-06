# Nâng Cấp Hiển Thị PDF bằng Drupap PDF Module (PDF.js)

## 1. Phân Tích Lỗi Sai Hiện Tại
Việc dùng thẻ `<iframe src="...pdf">` (Native Browser Viewer) là cách "chữa cháy" phổ thông nhất trên mạng. Tuy nhiên nó vướng phải các nhược điểm chết người đối với một hệ thống LMS chuyên nghiệp:
- **Xấu & Thiếu Đồng Nhất:** Giao diện bị phụ thuộc 100% vào trình duyệt của người dùng (Chrome hiển thị một kiểu, Safari một kiểu, Điện thoại lại là kiểu khác).
- **Phèn:** Thanh công cụ mặc định (Download, Print, Zoom) của trình duyệt trông cực kỳ "lệch tông" so với bộ giao diện Glass UI xịn xò của EverLMS.
- **Rủi ro bản quyền:** Sinh viên hiển nhiên thấy luôn phím Download bự chà bá để tải tài liệu nội bộ về máy.

## 2. Giải Pháp Triệt Để (Theo gợi ý của anh)
Sử dụng Module **PDF** chính chủ của Drupal (https://www.drupal.org/project/pdf). Module này tích hợp sức mạnh của engine **PDF.js** (do Mozilla phát triển - đây là engine xịn nhất thế giới hiện nay để đọc PDF trên nền web).

**Ưu điểm vượt trội:**
- Giao diện đồng nhất trên mọi trình duyệt/thiết bị Mobile. Đẹp, mượt mà và cực kỳ chuyên nghiệp.
- Cho phép tùy biến ẩn/hiện thanh công cụ (Có thể giấu luôn nút Tải Về & In Ấn để bảo vệ chất xám của Khóa Học).
- Nhúng thẳng vào trang (Inline Display) mà không bị vỡ Layout.

## 3. Các Bước Triển Khai Thực Tế

### Bước 1: Cài đặt Module & Thư Viện Lõi
1. Chạy lệnh tải module: `composer require drupal/pdf`
2. Module này yêu cầu phải có bộ thư viện Javascript gốc của `pdf.js` nằm trong thư mục `/libraries/pdf.js` của Drupal. Mình sẽ dùng lệnh wget/curl để tải bộ build gốc về đúng vị trí này.
3. Bật module: `ddev drush en pdf -y`

### Bước 2: Thay Đổi Cấu Hình Hiển Thị (Manage Display)
Trong cấu hình Entity của `lms_lesson`, ta sẽ vào tab **Manage Display**, tìm đến trường `field_attachments` (Nơi chứa file PDF).
- Đổi Format hiển thị từ `Generic File` (Link tải về) thành `PDF: Default`.
- Cấu hình tham số của PDF.js ngay trong Form (Tắt nút Download, thiết lập chiều rộng/cao của khung đọc).

### Bước 3: "Dọn Rác" File Twig Cũ
Xóa ngay đoạn code HTML Iframe `<iframe src="{{ file_url(lesson.field_attachments...) }}">` cùi bắp trong file `lms-lesson-view.html.twig`.
Thay vào đó, chỉ cần gọi code chuẩn của Drupal:
```twig
{{ content.field_attachments }}
```
Từ lúc này, sức mạnh của Module PDF sẽ tự động bung giao diện PDF.js siêu nét và ăn nhập 100% vào bộ Glass UI của trang Khóa Học!
