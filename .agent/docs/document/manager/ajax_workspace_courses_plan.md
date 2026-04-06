# Kế hoạch Triển khai AJAX Search & Lọc Khóa học cho Manager Workspace

## Mục tiêu
1. Sửa lỗi giao diện: Chữ `Search courses...` bị đè lên icon kính lúp do thiếu padding.
2. Xóa nút `Apply Filters` (hoặc ẩn đi).
3. Triển khai chức năng lọc và tìm kiếm tự động (Auto-search) thông qua AJAX khi người dùng nhập chữ hoặc thay đổi dropdown, áp dụng cho `/workspace/courses` mà không làm tải lại trang (No page reload).

## Giải pháp chi tiết

### 1. Khắc phục UI (Icon & Nút Apply Filters)
- **Tập tin:** `web/modules/custom/lms_course/templates/lms-admin-courses.html.twig`
- **Sửa CSS input:** Thêm `!important` vào inline style `padding-left: 2.5rem !important;` để tránh bị file `css_fix.css` ghi đè (override) làm mất khoảng cách padding trái đối với text.
- **Xóa nút:** Loại bỏ thẻ `<button type="submit">Apply Filters</button>` ra khỏi cấu trúc HTML.
- **Thêm ID cho Form:** Đặt ID cho form filter (VD: `id="lms-course-filter-form"`) để dễ dàng truy vấn DOM.

### 2. Tách Template Bảng Dữ Liệu
Để AJAX request có thể chỉ trả về đoạn mã HTML của cái Bảng (table) mà không kèm cả header/sidebar, ta cần tách template table này ra.
- **Tập tin mới (NEW):** `web/modules/custom/lms_course/templates/lms-admin-courses-table.html.twig`
- **Nội dung:** Cut/Paste toàn bộ vùng `div.lms-admin-card` (chứa table danh sách khóa học và thanh phân trang) sang file mới này.
- **Sửa file cũ (Dữ lại):** Gọi file con vào lại thông qua `{% include '@lms_course/lms-admin-courses-table.html.twig' %}` và đưa vào một block thẻ div bọc ngoài wrapper có `id="lms-course-table-container"` để làm vùng chứa update nội dung AJAX.
- **Cập nhật Hook:** Ở file `lms_course.module`, khai báo thêm `hook_theme` cho `lms_admin_courses_table`.

### 3. Cập nhật Controller trả về HTML AJAX
- **Tập tin (MODIFY):** `web/modules/custom/lms_course/src/Controller/AdminController.php` ở method `courseList()`.
- **Logic:**
  - Nhận diện request AJAX qua tham số (VD: `$request->query->get('ajax') == '1'`).
  - Nếu là AJAX: Render mảng giao diện của `#theme => 'lms_admin_courses_table'` (sử dụng service `renderer`) thành một chuỗi mã HTML thô. Trả trực tiếp chuỗi html này về object `\Symfony\Component\HttpFoundation\Response($html)` để response trả về dữ liệu nhanh, hoàn toàn bỏ qua việc generate layout hệ thống.
  - Nếu KHÔNG là AJAX: Load mảng giao diện của `#theme => 'lms_admin_courses'` như bình thường.

### 4. JavaScript điều khiển AJAX Fetch API
- **Tập tin:** Bổ sung đoạn mã tag `<script>` dưới cùng hoặc thư viện của file `lms-admin-courses.html.twig`.
- **Logic thực thi:**
  - Lắng nghe event `input` trên ô input *Search* bằng kỹ thuật `debounce` (~500ms délai) để chống spam request Call liên tục lên Server nếu user gõ phím quá nhanh.
  - Lắng nghe event `change` trên các thẻ `select` dropdown (Status, Category, Instructor).
  - Hàm cập nhật dữ liệu AJAX `fetchCourses()`:
    1. Đọc tất cả giá trị query từ Form.
    2. Gửi phương thức GET `fetch('/workspace/courses?search=...&status=...&ajax=1')`.
    3. Đợi nhận chuỗi text HTML trả lại từ máy chủ.
    4. Thay đổi hiển thị trên Grid bảng thông qua `document.getElementById('lms-course-table-container').innerHTML = response_html`.
    5. Cập nhật lịch sử URL cho Browser Browser bằng `history.pushState(null, '', new_url_no_ajax)` để nếu user có bấm phím F5 hay copy gửi tin nhắn link màn hình này thì các giá trị lọc vẫn ăn khớp chính xác.

## Đợi Tín Hiệu Hành Động
Ngưng code lại. Chờ người dùng xác nhận và review kế hoạch. Nếu đồng ý thì mới bắt đầu các phase thi công và ghi files.
