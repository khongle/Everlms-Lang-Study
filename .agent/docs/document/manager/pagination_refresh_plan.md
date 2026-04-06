# Kế hoạch Triển khai Reset Khoá học và Tích hợp Phân trang (Pager)

## Mục tiêu
1. Bổ sung nút "Refresh / Clear Filters" để người dùng dễ dàng xóa các lựa chọn lọc đang có và đưa bảng danh sách về trạng thái gốc.
2. Sửa lỗi thanh Phân trang (Pagination) ở dưới cùng bảng danh sách hiện tại đang là dummy HTML. Làm cho nó hoạt động thực sự qua kết nối hệ thống Pager mặc định của Drupal.
3. Số lượng khóa học: Giới hạn 10 items mỗi trang. Phân trang nên nằm rõ ràng ở bên dưới bảng dữ liệu (ngay vị trí cũ).

## Các bước thực hiện

### 1. Nút Refresh Filter
- **File:** `lms-admin-courses.html.twig`
- **Thực thi:**
  - Thêm một nút `<button type="button" id="lms-course-reset-btn" class="lms-admin-filter__select" style="cursor: pointer; color: #fff; background: rgba(255,255,255,0.05); min-width: max-content;"><i class="fas fa-sync-alt" style="margin-right:0.5rem"></i> Refresh</button>` ngang hàng với thanh filter (bằng cách mở rộng div hoặc chèn nút vào vị trí trống bên cạnh Instructor).
  - Bổ sung sự kiện JS: Lắng nghe click nút `reset-btn` -> `form.reset();` (xóa Dropdowns, Input Search) -> đổi URL trình duyệt về `/workspace/courses` trần -> Gọi hàm fetch AJAX data.

### 2. Tích hợp Hệ thống Pager Drupal (Backend)
- **File:** `AdminController.php` (ở function `courseList`) & `lms_course.module`
- **Thực thi:**
  - Cập nhật hàm truy vấn EntityQuery bằng cách nối đuôi phương thức `->pager(10)` sau `accessCheck(FALSE)`. Phương thức này sẽ tự tính toán tổng Records và chia trang.
  - Khai báo mảng biến kết xuất: `$build['#pager'] = ['#type' => 'pager'];`.
  - Để phục vụ cho AJAX lấy Table, cần truyền biển `#pager` này qua theme `lms_admin_courses_table`.
  - Ở hook module `hook_theme`, cập nhật biến `pager` là NULL cho `lms_admin_courses_table`.

### 3. Tút lại HTML Phân trang (Frontend Table)
- **File:** `lms-admin-courses-table.html.twig`
- **Thực thi:**
  - Xóa toàn bộ khối `<div class="lms-pagination">...</div>` hardcoded đang có 1,2,3... Next.
  - Chèn thẻ Twig `{{ pager }}` vào khối `lms-admin-footer` (vị trí cũ dưới gốc). Theme hệ thống sẽ tự sinh ra mã `<ul>` Drupal đúng chuẩn. Dùng CSS/Inline tút lại 1 chút cho giống phong cách Darkmode nếu nó chưa đẹp.

### 4. Đấu nối Phân trang với AJAX JavaScript
- **File:** `lms-admin-courses.html.twig` (Khối `<script>`)
- **Thực thi:**
  - Lắng nghe sự kiện `click` trong phạm vi `#lms-course-table-container`.
  - Nếu click rơi vào thẻ `<a>` thuộc về khu vực phân trang (`.pager__items a` hoặc `nav.pager a`), cản lại luồng duyệt web chuẩn (`e.preventDefault()`).
  - Lấy địa chỉ liên kết (href) của thẻ phân trang đó và Fetch data.
  - Sửa đổi tham số `fetchCourses(overrideUrl = null)`: Nếu có truyền Url đè, ta ưu tiên sử dụng Url từ đường link trang, thêm query `ajax=1` và Fetch. Giúp bảng load trực tiếp qua Trang 2, 3 bằng AJAX.

## Tình trạng Review
Ngưng triển khai Code. Mong chờ thao tác Review/Approval của user.
