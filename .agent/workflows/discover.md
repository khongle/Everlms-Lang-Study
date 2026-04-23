---
description: Khám phá và phân tích chi tiết CSS/HTML từ một web template đích
---

# /discover - Web Template UI & UX Analyzer

$ARGUMENTS

---

## Mục đích

Kích hoạt chế độ DISCOVER để cào, đọc và phân tích cấu trúc HTML/CSS của một template web do người dùng cung cấp. Nhằm tạo ra một bộ **UI Design Guidelines** chuyên sâu cho Agent và Codebase, bao vây trọn các thẻ UI đặc trưng (Màu, Layout, Card, Modals...) để sau này ráp vào UI Frontend (Twig/React) không bị vỡ. Đồng thời tải kho mã nguồn HTML mẫu.

---

## Quy trình Thực Hiện

Khi Lệnh `/discover [URL MẪU TỔNG]` được gọi:

### 1. Thu Thập Dữ Liệu Thực (Data Aggregation)
- Agent sẽ đọc cấu trúc HTML (bằng `curl` hoặc API) của URL được ném vào.
- Trích xuất toàn bộ Menu thanh điều hướng (Sidebar/TopNav) để tìm kiếm các trang con UI Kits (Cards, Buttons, Tables, Progress, Modals, Advanced Forms...).
- Khởi tạo thư mục `.agent/docs/design/sample/` và chạy ngầm script tải 100% các file `[component].html` về làm thư viện mẫu tham khảo HTML.

### 2. Phân Tích Component & Phẫu Thuật CSS (CSS Surgery)
- **Layout & Base Schema:** Chỉ ra cách template xây dựng khung sườn (VD: `.app-wrapper`, `.sidebar-nav`, `.header-top`...).
- **UI Base:** Xác định class chính xác của `Buttons` (VD: `btn btn-light-primary`), `Badges` (VD: `badge text-bg-success`), kích thước `Avatars`, viền bo `Cards`.
- **Advanced UI & Modals:** Giải phẫu cấu trúc Form (các thẻ bọc `form-control`, `input-group`), Bảng dữ liệu (`table-responsive`), Popup Modal, Accordions, Nav Tabs.
- **Icons & Graphics:** Lưu lại thư viện Icon chính (Tabler, Phosphor, FontAwesome, Feather...) và cú pháp (VD: `ti ti-bell`, `fe fe-user`).

### 3. Xuất Bản Tài Liệu Chuẩn Hệ Thống (Publish Guidelines)
- Chuyển giao toàn bộ kết quả phân tích vào tệp trung tâm: `.agent/docs/ui_design_guidelines.md`. Tài liệu này BẮT BUỘC liệt kê chính xác các chuỗi CSS class và code HTML mẫu `<div class="...">...</div>`.
- Ghi chú các thẻ mẫu này để bất kỳ AI nào sau này implement Drupal Views / Controller / Blocks đều phải Override Twig và ốp đúng bộ CSS này.
- **Feedback lại cho User:** Tóm tắt ngắn gọn các Modules UI đã cào được và sẵn sàng thiết kế Mockup UI dựa trên dữ liệu mới.

---

## Nguyên tắc cốt lõi:
- **Zero Hallucination:** TUYỆT ĐỐI không thêu dệt và bịa đặt các CSS Class. Mọi Class đều phải được `grep_search` và thu thập thực tế từ code nguyên thủy tải về.
- **Giữ Gìn Mẫu Thực Phẩm:** Lưu kho `sample/` để có căn cứ mượn DOM HTML ngay lập tức mỗi khi Agent cần code module tương tự.
