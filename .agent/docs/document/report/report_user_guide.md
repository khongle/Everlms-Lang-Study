# Hướng Dẫn Sử Dụng Báo Cáo EverLMS (Analytics & Reports)

Tài liệu này hướng dẫn quản trị viên (Administrator, Manager) cách truy cập và sử dụng các tính năng Báo cáo & Phân tích (Analytics) cốt lõi của hệ thống EverLMS, bao gồm **Báo cáo Khóa học (Course Report)**, **Báo cáo Người dùng (User Report)**, và **Báo cáo Bài kiểm tra (Quiz Report)**.

Tất cả các báo cáo đều nằm trong module `lms_analytics` độc lập, vì vậy sẽ không làm ảnh hưởng đến hiệu suất hệ thống chính. Các chức năng tính toán chuyên sâu đã được phân luồng xử lý riêng dưới Database.

---

## 1. Báo Cáo Khóa Học (Course Analytics & Report)

Báo cáo khóa học cung cấp cái nhìn tổng quan về doanh thu và độ tương tác (Enrollment, Completion Rate) của từng khóa học hiện có trên hệ thống.

- **Đường dẫn (URL):** `/admin/lms/reports/courses`
- **Quyền yêu cầu:** Cần có quyền giám sát/admin về khóa học (`administer lms_course`).

### Tính năng chính
1. **Thống kê tổng quan The Course:** Xem nhanh giá bán, danh mục, trạng thái xuất bản, số lượng học viên đang học (Active) và số người đã hoàn thành (Completed).
2. **Tỷ lệ hoàn thành (Completion Rate):** Thanh Process (màu xanh lá/cam) trực quan cho tỷ lệ phần trăm số học viên đã tốt nghiệp khóa học so với tổng số lượng ghi danh (Enrollments).
3. **Tổng doanh thu (Total Revenue):** Tính tổng tiền của tất cả các đơn hàng **đã hoàn thành (completed)** có chứa khóa học này.
4. **Bộ lọc (Filters):** Cho phép tìm kiếm chính xác Tên khóa học và trạng thái xuất bản (Published/Draft).
5. **Xuất dữ liệu (Export CSV):** Nút **"Export to CSV"** ở góc trên cùng bên phải màn hình. Toàn bộ thông tin hiển thị trên bảng sẽ được stream tải xuống máy tính dưới định dạng file `.csv` (Tương thích tốt với MS Excel với chuẩn mã hóa UTF-8).

---

## 2. Báo Cáo Người Dùng (User Analytics & Report)

Báo cáo người dùng theo dõi và đánh giá chi tiết lực học của từng học viên trên toàn nền tảng, cho phép Manager biết học viên nào hoạt động tích cực hay kém tích cực.

- **Đường dẫn (URL):** `/admin/lms/reports/users`
- **Quyền yêu cầu:** Cần có quyền quản lý thông tin User (`administer users`).

### Tính năng chính
1. **Lịch sử hoạt động (Last Active):** Xem chính xác ngày giờ học viên lần cuối cùng đăng nhập hoặc có tương tác.
2. **Tiến trình học (Enrolled & Completed Courses):** Hiển thị rõ học viên đã mua bao nhiêu khóa, mức độ hoàn thành các khóa đó như thế nào.
3. **Đánh giá năng lực (Quizzes Taken & Avg. Score):** Số bài thi (quiz) học viên từng tham gia ở mọi khóa + Điểm số trung bình trải dài các bài thi đó (Rất hữu ích để đánh giá năng lực học viên).
4. **Đạt được Chứng chỉ (Certificates):** Thống kê số lượng chứng chỉ mà hệ thống đã cấp phát cho học viên do pass các bài thi hoặc khóa học bắt buộc.
5. **Bộ lọc Cảnh báo (Inactive Filter):** Tính năng thiết yếu lọc ra những tài khoản đã **"Inactive cho trên 7 ngày, 30 ngày, hoặc 90 ngày"** ở mục "Inactive for".
6. **Xuất dữ liệu (Export CSV):** Tương tự, hỗ trợ nút bấm để tải thẳng toàn bộ Data theo các bộ lọc mà người dùng vừa Request.

---

## 3. Báo Cáo Bài Kiểm Tra (Quiz Analytics & Report)

Phân tích chất lượng các bài kiểm tra được đưa lên hệ thống để đánh giá xem độ khó này có phù hợp để tạo tính thách thức hay không, hay các câu hỏi đang bị lỗi đề bài. Bao gồm 2 phần là màn hình List (Danh sách chung toàn quiz) và Detail (Chi tiết câu hỏi khó).

### 3.1 Giao Diện Danh Sách Bài Kiểm Tra (Trang Overview)
- **Đường dẫn (URL):** `/admin/lms/reports/quizzes`
- **Quyền yêu cầu:** Cần có quyền giám sát hệ thống kiểm tra (`administer lms_quiz`).

Tại đây Admin có thể thấy toàn bộ các Quiz với thẻ: 
- Pass Threshold (Ngưỡng điểm để đậu)
- Total / Unique Attempts (Số lượt thử bài của tổng toàn thể / Lượt tham gia riêng biệt của người dùng thật).
- Avg. Score, Pass Rate (Trượt và Đậu).
- Tại bên phải mỗi dòng của bảng sẽ có nút **"Drill Down"** (Phân tích sâu)

### 3.2 Giao Diện Phân Tích Sâu (Item Analysis Detail)
- **Đường dẫn (URL):** `/admin/lms/reports/quizzes/{quiz_id}` (Truy cập bằng cách ấn **"Drill Down"** ở danh sách).

1. Màn hình phân tích cung cấp **Overview Cards**: Nhấn mạnh tổng số lượt tham gia, tỷ lệ Điểm trung bình và tỷ lệ đỗ.
2. Nhìn bằng mắt bảng **Score Distribution Bar Chart**: Phân loại theo 5 nhóm điểm (0-20, 21-40, 41-60, 61-80, 81-100). Đồ thị cột giúp giảng viên nắm bắt ngay học viên chủ yếu đạt được khoảng điểm nào.
3. **Hardest Questions (Bottom 10):** Chức năng *Item Analysis* quan trọng chỉ đích danh 10 câu trả lời có _Correct Rate_ (Tỷ lệ trả lời đúng) thấp nhất (Vd: chỉ 15% học sinh trả lời đúng câu này -> Giảng viên có thể check lại).
4. **Xuất Item Analysis (Export CSV):**  Tại màn hình Detail này, nếu bấm **Export Item Analysis**, hệ thống tải xuống file `quiz_{id}_item_analysis.csv` - phân tích toàn cục tỷ lệ trả lời đúng/sai của Mọi câu hỏi thuộc Quiz đó thay vì chỉ có Top 10 câu khó nhất.

## 4. Giao Diện Tích Hợp (Manager Workspace)

Thay vì phải truy cập giao diện backend mặc định của Drupal như Admin, người dùng có role **Manager** (Quản lý) sẽ xem báo cáo trực tiếp thông qua **Workspace Dashboard**.

- **Đường dẫn (URL):** `/workspace/reports`
- **Giao diện:** Tương thích hoàn toàn với nền giao diện Dark Theme (Chế độ tối) của hệ thống.
- **Tính năng:**
  - Cung cấp **Tabs Navigation** trực quan để di chuyển giữa 3 mục: _Course Analytics_, _Learner Analytics_ và _Quiz Analytics_ mà không cần reload trang.
  - Các chỉ số KPI tổng (Total Enrollments, Completions, etc.) luôn hiển thị cố định ở trên cùng.
  - Vẫn tích hợp sẵn nút xuất dữ liệu **Export to CSV**. Tải thẳng file báo cáo giống hệ thống Admin.
  - Ẩn các tính năng Deep Analysis không cần thiết (như bộ lọc đa tầng) để Manager tập trung vào overview.

---

## Cấu trúc Hệ Thống Dưới Chạy (Dành cho Developer)
Toàn bộ mã nguồn chịu trách nhiệm cho Báo cáo này nằm ở:

- **Module location:** `/web/modules/custom/lms_analytics/`
- **Services (Chứa logic và Aggregate Queries):**
  - `src/Service/CourseReportManager.php`
  - `src/Service/UserReportManager.php`
  - `src/Service/QuizAnalyticsManager.php`
- **Controllers (Điều hướng Dữ liệu ra Twig và Handle Export CSV):**
  - `src/Controller/CourseReportController.php`
  - `src/Controller/UserReportController.php`
  - `src/Controller/QuizReportController.php`
- **Templates (Giao diện UI/CSS Twig của Report):**
  - `/templates/lms-analytics-course-report.html.twig`
  - `/templates/lms-analytics-user-report.html.twig`
  - `/templates/lms-analytics-quiz-report-list.html.twig`
  - `/templates/lms-analytics-quiz-report-detail.html.twig`
