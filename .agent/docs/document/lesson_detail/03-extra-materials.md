# Tài Liệu Bổ Sung và Giao Việc (Extra Materials)

Ngoài nội dung cốt lõi của bài giảng (Video/Văn bản), hệ thống có thiết kế riêng các công cụ để giáo viên (Teacher) đính kèm thêm tài liệu mở rộng cho sinh viên.

## 1. Khu vực Tài liệu Đính kèm (Attachments / Resources)
- **Backend:** Thực thể `Lesson` có một trường `field_attachments` dạng File upload. Trường này có cấu hình `cardinality` (số lượng thẻ) là Vô Hạn (Unlimited). 
- **Cấu hình file:** Cho phép tải lên rất đa dạng các tệp tin chuẩn: `pdf`, `doc`, `docx`, `ppt`, `pptx`, `xls`, `xlsx`, `zip`. Giáo viên có thể gửi PDF bài tập, bộ slide PowerPoint bài giảng, thư viện assets dạng zip...
- **Frontend:** Ở bên dưới cùng của bài học (ngay dưới Video và Nội dung mô tả chi tiết), trang web sẽ tự động sinh ra một khu vực có ranh giới mang tên **"Resources"**. Các tài liệu được hiển thị theo dạng danh sách đạn (bullet) đi kèm icon Download. 
- **Cách sinh viên dùng:** Khi người dùng bấm vào mũi tên Download `(icon fa-download)`, file sẽ được mở thẻ tab mới hoặc tự động kích hoạt tiến trình tải về máy.

## 2. Phần Mô tả Chi tiết (Description area)
- Ngay cả khi bài học có loại là `Video` (Video Type), giáo viên vẫn có thể lợi dụng trường `field_content` (Rich Text) để điền thêm thông tin chữ.
- Thông tin văn bản này sẽ được in ngay bên dưới trình phát Video, đóng vai trò như:
  - Tóm tắt ý chính của Video.
  - Link trích xuất/thao khảo ngoài hệ thống.
  - Yêu cầu và bài tập về nhà bổ sung.

## Khuyết điểm hiện tại / Tính năng có thể thảo luận:
- *"Sinh viên không thể nộp file ngược lại cho giáo viên"*: Bài học chỉ đang ở dạng bài học một chiều (Teacher -> Student). Nếu muốn giáo viên "giao thêm bài làm", sinh viên cần một chỗ để Upload File phản hồi (Assignment Upload/Submission). Hiện tại chưa có Entity/Trường nào giải quyết nhu cầu nộp bài tập kiểu tự luận (thay vì bài trắc nghiệm Quiz máy chấm tự động).
