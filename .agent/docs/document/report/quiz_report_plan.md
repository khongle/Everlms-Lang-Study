# Quiz Report Plan (Phased & Detailed)

## Tổng quan
Báo cáo kiểm tra tập trung vào "Đánh giá chất lượng của đề thi". Không chỉ tổng điểm, mà phải khoan sâu vào độ khó từng câu hỏi (Question-level analytics) để giảng viên tối ưu hoá nội dung. Plan dưới đây được phân rã chi tiết.

---

## Phase 1: Database & Query Design (Thiết kế Dữ liệu)
**Mục tiêu:** Truyền tải thông kê phân bổ điểm (phổ điểm) và tính tỷ lệ đúng/sai theo từng câu.
- **Data structure cấp Quiz (Phổ điểm):** Truy vấn bảng `lms_quiz_result`. Nhóm giá trị `score` thành các bucket/interval: `0-20`, `20-40`, `40-60`, `60-80`, `80-100` để đếm số lượng học viên nằm trong mỗi dải điểm này.
- **Data structure cấp Hỏi (Question Analytics):** Query kết hợp bảng `lms_question` và `lms_question_response`.
  - Tính logic: `Tỷ lệ đúng của câu X = (Tổng số response đúng của câu X) / (Tổng số lượt trả lời câu X) * 100`.
- **Caching & Pre-calculation:** Queries ở cấp độ câu hỏi (Question-level) rất nặng. Cần tạo 1 Drupal Queue background job: Khi một quiz nộp bài, job sẽ cập nhật lại "Correct rate" lưu vào một trường ẩn trên entity Question thay vì tính toán (on-the-fly) mỗi lần refresh trang.

## Phase 2: Backend Service & Business Logic (Xử lý Backend Logic)
**Mục tiêu:** Điều phối Query nặng một cách trơn tru ra API.
- **Service `QuizAnalyticsManager`:**
  - Method `getQuizDistribution($quiz_id)`: Trả về mảng JSON phục vụ biểu đồ phổ điểm.
  - Method `getHardestQuestions($quiz_id, $limit = 5)`: Trả về Object các câu hỏi có độ Tỷ lệ đúng thấp nhất.
- **Access Control Custom Rule:** Instructor chỉ có quyền xem Analytics cho Quiz thuộc Course do họ tạo. Phải validate logic: `Quiz -> Lesson -> Chapter -> Course -> Author == Current User`.

## Phase 3: Administrative UI & Frontend (Giao diện Quản trị & Frontend)
**Mục tiêu:** Vẽ biểu đồ trực quan, UI chuyên nghiệp.
- **Quiz List Overview (Bảng cha):** Liệt kê các Quiz kèm số liệu cơ bản: ID, Lượt làm bài, Trung bình, Tỷ lệ Đậu/Rớt.
- **Analytics Dashboard (Bảng con - Drill down):**
  - **JS Chart Integration:** Tích hợp thư viện Chart.js hoặc ApexCharts (Khuyến nghị cài qua Drupal Library System) cho biểu đồ Cột (Bar Chart) thể hiện "Score Distribution". Gọi AJAX GET dữ liệu từ controller để render JSON lên biểu đồ.
  - **Question Difficulty Table:** Bảng danh sách "Các câu hỏi thường làm sai nhất". Bảng này cung cấp ID, Content ngắn, Lượt sai / Lượt đúng. Khuyến khích chỉnh đề (Action button).

## Phase 4: Export & Automation (Xuất dữ liệu & Email)
**Mục tiêu:** Báo cáo tổng thể cho Giám đốc Đào tạo.
- **Item Analysis Export:** Thêm action "Export Item Analysis CSV" cho các chuyên gia học thuật (Academic experts) đánh giá mức độ uy tín của Quiz. File CSV xuất ra chứa toàn bộ danh sách từng câu hỏi và tỷ lệ Pass/Fail riêng biệt.
- **Notification Trigger (Tuỳ chọn):** Bắn thông báo (Drupal Message / Email) cho Instructor nếu thấy "Có một Quiz liên tục bị tạch (Fail rate > 80% trong tuần qua)" do có khả năng đề thi bị sai đáp án gốc.

## Phase 5: Testing & QA (Kiểm thử)
**Mục tiêu:** Bảo chứng logic tính toán phổ điểm.
- **Data Generation:** Dùng test script tạo 1 Quiz chứa 5 câu hỏi, chạy giả lập (Mockup data) 100 lượt answers với kết quả chuẩn bị sẵn.
- **Verify Assertions:** Kiểm tra biểu đồ phổ điểm có phân bổ số lượng đúng bucket không. Bảng câu hỏi khó nhất có show đúng câu cố tình bị gán đáp án sai không.
- **Verify Frontend:** Test JS Chart load trên Mobile và PC xem có bị vỡ bố cục hay chậm render.
