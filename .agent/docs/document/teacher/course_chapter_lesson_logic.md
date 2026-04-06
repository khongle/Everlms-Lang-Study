# Course, Chapter, & Lesson Logic for Teacher (Instructor)

Vai trò **Instructor/Teacher** là hạt nhân sản xuất nội dung, có quyền khởi tạo và theo dõi lớp học nhưng bị giới hạn một số quyền phát hành (Publish) và xóa (Delete).

## 1. Khởi tạo Khóa học (CRUD)
- **Tạo mới (Create)**: Được quyền tạo Course, cũng như thêm các Chapter và Lesson.
- **Chỉnh sửa (Edit)**: Chỉ được phép chỉnh sửa **khóa học của chính mình** (Own courses). Không thể can thiệp vào Course do giảng viên khác tạo.
- **Xóa (Delete)**: **Không có quyền xóa** khóa học (Delete course). Nếu cần thiết phải báo cáo Admin/Manager.
- **Xuất bản (Publish)**: **Không có quyền Publish**. Tất cả các Course/Chapter/Lesson mà Giáo viên tạo đều mặc định ở trạng thái tĩnh `Draft`. Khi hoàn thiện, cần báo cáo Manager duyệt để lên sóng.

## 2. Quản lý AI & Tạo Nội Dung
- Giáo viên có đặc quyền dùng công cụ sinh nội dung AI. 
  - Khi tạo khóa học, Instructor có thể yêu cầu AI tự động sinh ra Course Outline, Chapter Name, Text Lessons và tự sinh Quizzes.
  - Sau đó, Instructor có trách nhiệm phê duyệt lại nội dung từng bài được sinh ra từ AI trước khi làm thủ tục Request Publish.
- Soạn thảo đa định dạng Lesson: Tự gán các bài Video (Youtube/SCORM/H5P/Vimeo) và cấu hình điều kiện Unlock (ví dụ tính năng Drip content - tự mở khóa sau X ngày Enroll).

## 3. Theo dõi Học Viên
- Cấu hình yêu cầu bắt buộc của Lesson: Setting bài Quiz qua câu hỏi, chấm điểm (Grade Quiz) cho sinh viên thuộc khóa học của mình.
- Xem báo cáo (View Reports) về Progress của học sinh: Bao nhiêu người hoàn thành Lesson, bao nhiêu gửi bài Quiz trên lớp do mình quản lý (Own students).
