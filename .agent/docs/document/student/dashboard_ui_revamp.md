# Student Dashboard UI Revamp

## Vấn đề hiện tại
Giao diện Dashboard của Sinh Viên (role `student`) đang bị lỗi hiển thị (layout xếp dọc thay vì hiển thị dạng lưới ngang) và các thẻ khoá học (Course Card) trong mục "Continue Learning" chưa được đẹp mắt, thiếu thông tin (thumbnail, Nút thao tác).

Nguyên nhân chính là do:
1. Thiếu class CSS lưới cho wrapper của KPI (`grid-kpi` bị sai chính tả so với CSS gốc `lms-kpi-grid` hoặc `grid-kpis`).
2. Danh sách khoá học "Continue Learning" đang hiển thị quá đơn giản (chỉ có title dạng html thô và progress bar), thiếu ảnh bìa (thumbnail) và CSS Card hoàn thiện.

## Kế hoạch Cập nhật

### 1. Sửa lỗi Layout thống kê (KPIs)
- Edit file `web/modules/custom/lms_progress/templates/lms-dashboard.html.twig`.
- Đổi class container từ `grid-kpi` thành `lms-kpi-grid` để hiển thị 4 KPI theo dạng lưới ngang (Responsive grid tuỳ kích thước màn hình).
- Đảm bảo các class tĩnh `lms-kpi-card` được giữ nguyên hoặc gán đúng với thư viện style `lms_theme`.

### 2. Nâng cấp thẻ khoá học (Continue Learning Cards)
Để các thẻ "Continue Learning" đẹp và bắt mắt như thiết kế ban đầu:
- Cập nhật file `UserPortalController.php` (method `dashboard()`). Cần phải lấy ra url của custom field ảnh bìa (`field_thumbnail`) của `recent_courses` rồi đưa vào biến array để truyền sang views.
- Tại `lms-dashboard.html.twig`, xây dựng lại khối HTML thẻ khoá học đang học. Đồng bộ với thiết kế Card chung:
  - Hiển thị Ảnh bìa Khoá học.
  - Hiển thị Category (nếu có).
  - Tên Khoá học in đậm.
  - Thanh Progress Bar Gradient.
  - Nút bấm `"Continue Learning"`.

### Danh sách các file bị ảnh hưởng
- `[MODIFY] web/modules/custom/lms_progress/src/Controller/UserPortalController.php`
- `[MODIFY] web/modules/custom/lms_progress/templates/lms-dashboard.html.twig`
