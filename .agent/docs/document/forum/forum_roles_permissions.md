# Phân quyền Tính năng Diễn đàn (Forum Roles & Permissions)

Dưới đây là bảng phân quyền chi tiết cho các thao tác trên Forum (Diễn đàn) đối với các nhóm người dùng (Roles) chính trong hệ thống EverLMS: `Student` (Học viên), `Teacher/Instructor` (Giảng viên), và `Admin/Manager` (Quản trị viên).

## 1. Bảng Trách nhiệm & Quyền hạn (Access Matrix)

| Chức năng (Feature) | Student (Authenticated) | Teacher (Instructor) | Admin / Manager |
|-----------------------------------------------|-----------------------------|---------------------------|-----------------------|
| Xem danh sách diễn đàn chung (`/forum`) | ✅ Có | ✅ Có | ✅ Có |
| Xem danh sách diễn đàn khoá học | ✅ Chỉ khoá đã tham gia | ✅ Chỉ khoá đang dạy | ✅ Có |
| Đọc bài viết (View Topic) | ✅ Có | ✅ Có | ✅ Có |
| **Tạo bài viết mới (Create Topic)** | ✅ Có | ✅ Có | ✅ Có |
| **Trả lời bài viết (Reply)** | ✅ Có | ✅ Có | ✅ Có |
| Chỉnh sửa bài viết/trả lời của **mình** | ✅ Có | ✅ Có | ✅ Có |
| Chỉnh sửa bài viết/trả lời của **người khác** | ❌ Không | ❌ Không | ✅ Có |
| Xoá bài viết/trả lời của **người khác** | ❌ Không | ❌ Không | ✅ Có |
| **Đánh dấu Câu trả lời hay nhất (Mark Best Answer)** | ✅ Có (Chỉ topic mình tạo) | ✅ Có (Topics trong khoá mình) | ✅ Có |
| Ghim bài viết lên đầu (Pin Topic) | ❌ Không | ✅ Có (Topics trong khoá mình) | ✅ Có |
| Khoá bài viết (Lock Topic) | ❌ Không | ✅ Có (Topics trong khoá mình) | ✅ Có |

## 2. Giải pháp Áp dụng vào Code Hiện Có
Hiện tại, code `lms_forum` mới chỉ khai báo quyền quản trị (`administer lms_course`). Điều này có nghĩa là **Student mặc định đang bị từ chối quyền tạo Topic/Reply**.

Để Student có thể tạo được:
1. **Cách 1 (Nhanh nhất)**: Cần bổ sung file `lms_forum.permissions.yml` để định nghĩa các quyền như `create lms_forum_topic`, `create lms_forum_reply` và cấp (assign) nó cho Role `authenticated` trên trang cấu hình Permission của Drupal.
2. **Cách 2 (Mở rộng quy mô)**: Cần tạo lớp `ForumTopicAccessControlHandler.php` trong thư mục `src/` để tự động cho phép mọi tài khoản `Authenticated` tạo Topic mà không cần tick chọn Permission thủ công, đồng thời kiểm tra đúng logic "Khoá học đã Enroll".

> 👉 NẾU bạn muốn Student ĐĂNG BÀI ĐƯỢC NGAY LẬP TỨC: báo lại để mình viết tự động file phân quyền và class AccessHandler cho bạn nhé!
