# User Report Plan (Phased & Detailed)

## Tổng quan
Báo cáo người dùng theo dõi chi tiết tiến trình của học viên: số khoá học đã tham gia, tiến độ hoàn thành, kết quả Quiz, và các Certificate. Dưới đây là kế hoạch đi sâu vào các giai đoạn thực thi nghiêm ngặt.

---

## Phase 1: Database & Query Design (Thiết kế Dữ liệu & Query)
**Mục tiêu:** Lấy dữ liệu 360 độ của User từ nhiều entity mà không làm nghẽn CSDL.
- **Core Queries:** Query tập trung vào entity User.
- **Aggregation Joins:**
  - `lms_progress`: `COUNT` khoá học trạng thái `in_progress` và `completed`.
  - `lms_quiz_result`: `COUNT` bài test, tính `AVG(score)`.
  - `lms_certificate`: `COUNT` số lượng chứng chỉ.
- **Data Pre-calculation (Cron-based):** Không nên Join tất cả vào lúc tải trang do lượng User rất lớn, dẫn tới timeout. *Chiến lược:* Sinh ra bảng `lms_user_report_stats` hoặc lưu Data object trong Redis. Bảng này sẽ được update background mỗi khi một User hoàn thành 1 quiz hoặc khóa học.

## Phase 2: Backend Service & Business Logic (Xử lý Backend & Logic)
**Mục tiêu:** Xử lý luồng truy xuất và tổng hợp thông qua Service Container.
- **Service `UserReportManager`:**
  - Cung cấp hàm `getOverviewList($filters, $page, $limit)` cho trang danh sách tổng hợp.
  - Cung cấp hàm `getUserDetailStats($user_id)` cho màn hình Drill-down chi tiết 1 sinh viên.
- **Access Control:** Module này chỉ giới hạn cho quyền `administrator` hoặc `manager`. Các route khai báo trong `routing.yml` cần validate quyền truy cập bằng route requirement.
- **Lọc Dữ liệu (Filters):** Lọc theo `Role` học viên (loại trừ các Admin user), lọc theo `Date Range` (khoảng ngày đăng ký), và trạng thái *Inactive* (Không login quá 30 ngày dùng thông số `access` time của bảng users).

## Phase 3: Administrative UI & Frontend (Giao diện Quản trị & Frontend)
**Mục tiêu:** Tạo trải nghiệm điều hướng danh sách chung và phân tích cá nhân sâu.
- **Overview Grid (Bảng Tổng Quan):**
  - Cột: User ID, Name/Email, Last Login, Total Enrolled, Completed Courses, Avg Quiz Score, Certificates.
  - DataTables plugin (Client-side search) nếu phân trang qua API JSON, hoặc chuẩn Drupal Tableselect.
- **Drill-down Detail Page (Màn hình Chi Tiết User):**
  - Hiển thị Profile card cơ bản của User.
  - Khu vực 1: Danh sách các khóa học User đang học (kèm thanh progress bar %).
  - Khu vực 2: Lịch sử thi Quiz thi (Tên Quiz, Lần thử số mấy, Điểm, Kết quả, Ngày thi).
  - Khu vực 3: Bằng cấp / Chứng chỉ sinh viên có thể tải về.

## Phase 4: Export & Automation (Xuất dữ liệu & Lên lịch)
**Mục tiêu:** Gửi báo cáo định kỳ và tải dữ liệu offline.
- **Data Export:** Trang tổng quan hỗ trợ xuất CSV danh sách người dùng. Xử lý xuất CSV cần giới hạn query batch 500 rows/lần rồi append vào file để tránh Memory Limit của PHP.
- **Scheduled Alerts (Inactivity Watch):** Thiết lập một cron job tự động lọc các User có trạng thái "Inactive trên 30 ngày", gửi email cảnh báo về cho Manager hoặc gửi thẳng email khích lệ (Reminder) cho User đó ("Đã lâu không thấy bạn học...").

## Phase 5: Testing & QA (Kiểm thử)
**Mục tiêu:** Đảm bảo data chính xác với tình hình thực tế của user.
- **Data Accuracy Test:** Thực hiện unit test: Gán 1 user làm 2 bài thi, 1 rớt (30/100) 1 đậu (90/100). Đảm bảo `Avg Quiz Score` hiển thị đúng là 60 điểm.
- **Performance QA:** Sinh tự động (Generate) bằng Drush Devel 50,000 Dummy Users có activity records để kiểm tra tốc độ load của trang Overview, phát hiện các dead-lock query.
