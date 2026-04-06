---
description: Kế hoạch Bổ sung "Manage Content" vào Workspace Sidebar
status: Lên kế hoạch (Planning)
---

# Kế hoạch Hoàn thiện: Bổ sung Sidebar "Manage Content" & Xây dựng Màn hình List

Mục tiêu: Quy hoạch lại hệ thống quản lý Nội dung (Slideshow, Introduction, News) với một "Cửa vào" gài tại Admin Sidebar, và dự phóng trước kiến trúc cho màn hình Danh sách (List Page) theo đúng tiêu chuẩn xịn xò của Bảng Manage Users.

## 1. Phương hướng Thêm Menu vào Sidebar (Làm luôn đợt này)
- **Tệp chỉnh sửa**: `lms-admin-sidebar.html.twig`.
- **Vị trí chèn**: Bên dưới mục "Certificate" hoặc "Orders" để gom cụm Data.
- **Hiển thị (UI)**: 
  - Menu chính "Manage Content" (Icon `<i class="fas fa-layer-group"></i>`).
  - Dropdown Sub-menu:
    - **Slideshows** -> `/workspace/content/slideshows`
    - **Introductions** -> `/workspace/content/introductions`
    - **News** -> `/workspace/content/news`

## 2. Kiến trúc Màn hình Danh sách Nội dung (Tính năng các URL sẽ xây dựng kế tiếp)

Đứng ở vị trí các trang List (Ví dụ: Trang quản lý Slideshows), giao diện sẽ thừa kế bộ mã khung tuyệt đẹp của trang Quản lý User (Table Ajax, Filter Bar, Theming Dark Mode).

- **Thanh Công Cụ (Filter Bar)**: Hỗ trợ thanh tìm kiếm Text (Theo Title Nội dung) và bộ lọc Status (Published, Unpublished).
- **Cấu trúc Cột dữ liệu Bảng (Table Columns)**:
  - Checkbox (Cho Bulk Action)
  - Thumbnail (Ảnh đại diện nếu có)
  - Content Title (Tiêu đề nội dung)
  - Created Date (Ngày đăng bài)
  - **Status** (Trạng thái: Hiển thị `<span class="lms-status--published">Published</span>` hoặc `<span class="lms-status--draft">Unpublished</span>`)
- **Bộ Nút Hành Động Cá Nhân (Inline Actions)**: Ở tận cùng bên phải mỗi dòng dữ liệu:
  - Nút **View** (`<i class="fas fa-eye"></i>`): Xem trước nội dung bài viết ngoài Frontend.
  - Nút **Edit** (`<i class="fas fa-edit"></i>`): Nảy sang màn hình Form chỉnh sửa.
- **Bộ Nút Hành Động Hàng Loạt (Bulk Actions)**: Nằm ở thành tab bottom dưới bảng:
  - Khi tick chọn hàng loạt Checkbox, sếp có thể bấm xuất lệnh **"Publish"** hoặc **"Unpublish"** content đồng thời (Và dĩ nhiên cũng áp dụng Toast Notification báo lỗi nếu chưa Tick y như vừa làm ở User Table).

> [!IMPORTANT]
> **Quy trình Thực thi**
> Kế hoạch này được chia làm 2 Giai đoạn vững chắc:
> 1. **Giai đoạn 1**: Tôi sẽ dán khối giao diện Menu Sidebar sổ lên sổ xuống trước để sếp test cảm giác điều hướng.
> 2. **Giai đoạn 2**: Tôi sẽ gõ đống Code PHP Controller để Render ra trọn bộ 3 trang Bảng HTML Content List theo đúng 100% các tiêu chí Sếp gài cắm ở Phần 2.

## 3. Xác nhận Kế hoạch

Bản thiết kế đã tích hợp toàn bộ ý tưởng của sếp (Publish, Unpublish, View, Edit). Sếp **Chốt / Duyệt** đi để tôi xả đạn Giai đoạn 1 (Sidebar) ngay tắp lự!
