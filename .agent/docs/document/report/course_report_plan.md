# Course Report Plan (Phased & Detailed)

## Tổng quan
Báo cáo khóa học phục vụ cho Admin/Manager đánh giá hiệu suất của khoá học (Enrollment, Completion, Revenue). Dưới đây là plan chi tiết chia thành các phase thực thi chặt chẽ, không code vội vàng.

---

## Phase 1: Database & Query Design (Thiết kế Dữ liệu & Query)
**Mục tiêu:** Đảm bảo truy xuất dữ liệu từ 3 modules (Course, Progress, Order) chính xác và tối ưu hiệu năng.
- **Entity Queries & Joins:**
  - Base Query: Cần query trên bảng Custom Entity `lms_course`.
  - Joins với `lms_progress` (UserCourse) để đếm `COUNT(user_id)` làm tổng số enrollment. Tính tỷ lệ % record có `status = completed` chia cho tổng số.
  - Joins với `lms_order_item` đã được thanh toán (`order_status = paid`) để tính `SUM(total_price)` doanh thu cho khoá học.
- **Aggregation Logic:** Do khối lượng courses và enrollments có thể lớn, cần nhóm (GROUP BY) theo `course_id`.
- **Caching Strategy:** Sử dụng Cache API của Drupal. Các query tổng hợp này sẽ được lưu cache với tags: `course_list`, `user_course_list`, `order_list`. Bất kỳ khi nào có order mới hoặc user học xong, cache tag liên quan sẽ bị invalidate.

## Phase 2: Backend Service & Business Logic (Xử lý Backend & Backend)
**Mục tiêu:** Tách biệt logic tính toán khỏi Controller bằng Service.
- **Service `CourseReportManager`:**
  - Method `getCourseStats(array $filters = [], $limit = 50, $offset = 0)`
  - Parameter `$filters` hỗ trợ: `category_id`, `instructor_id`, `status` (Published, Draft), `date_range`.
  - Xử lý phân quyền (Access Control): Nếu User hiện tại là `Manager/Admin`, lấy toàn bộ. Nếu là `Instructor`, chỉ query các courses do họ đứng lớp (`instructor_id = current_user`).
- **Controller `CourseReportController`:**
  - Khai báo route `/admin/lms/reports/courses`.
  - Receive request, passing filters từ `$_GET` xuống `CourseReportManager`.
  - Trả về cấu trúc mảng render (Render Array) cho Twig, không xử lý HTML tại đây.

## Phase 3: Administrative UI & Frontend (Giao diện Quản trị & Frontend)
**Mục tiêu:** Hiển thị dữ liệu trực quan bằng Twig và Bootstrap/DataTables.
- **Filters Form:** Build filter form dùng Form API cơ bản của Drupal kèm theo auto-submit bằng Ajax để người dùng dễ thao tác.
- **Data Table Layout:**
  - Bao gồm các cột: Course ID, Course Title, Category, Instructor, Enrollments, Active, Completed, Completion Rate (%), Total Revenue.
  - Sử dụng sorting headers cho: Enrollments và Revenue.
- **Twig Template:** Tạo thẻ `table` với classes chuẩn admin (`admin-table`, `table-responsive`). Các số tiền (Revenue) phải được format qua filter Twig tiền tệ mặc định (VD: `|currency_format`).

## Phase 4: Export & Automation (Xuất dữ liệu & Tự động hoá)
**Mục tiêu:** Giải quyết Requirement R-06 và R-07.
- **Export Functionality (CSV/Excel):** Thêm nút "Export to CSV" trên Admin UI. Khi click, gọi riêng một route action `/admin/lms/reports/courses/export`. Sử dụng Batch API của Drupal nếu dữ liệu xuất vượt quá 1000 dòng để tránh timeout PHP.
- **Automation / Scheduled Task (Cron):**
  - Khai báo `hook_cron` hoặc viết custom cron plugin.
  - Định kỳ hàng tuần/tháng tổng hợp top 10/50 khoá học.
  - Gửi qua module Mail (ví dụ `symfony_mailer`), render nội dung email từ một file Twig HTML chuyên biệt.

## Phase 5: Testing & QA (Kiểm thử)
**Mục tiêu:** Đảm bảo chính xác con số tài chính và tiến độ.
- **Unit Test:** Viết PHPUnit test riêng cho `CourseReportManager` mô phỏng 5 users mua 2 khoá học, kiếm tra hàm `getCourseStats` trả về đúng doanh thu.
- **Manual QA:** Đăng nhập dưới quyền Admin vs Instructor để check phân quyền hiển thị (Instructor chỉ thấy khoá học của mình). Test bộ lọc tìm kiếm và test tải file CSV.
