# Course, Chapter, & Lesson Logic for Admin

Vai trò **Administrator** là cấp quản lý cao nhất của hệ thống EverLMS, có toàn quyền (Full System Access) đối với mọi tiến trình và dữ liệu liên quan đến nội dung học tập.

## 1. Quyền hạn chung (CRUD)
- **Tạo mới (Create)**: Được phép tạo Course, Chapter, và Lesson vào bất kỳ lúc nào, cho bất kỳ giảng viên nào.
- **Chỉnh sửa (Edit)**: Có quyền can thiệp, chỉnh sửa bất kỳ Course/Chapter/Lesson nào trên hệ thống, bất kể nội dung đó do ai tạo.
- **Xóa (Delete)**: Administrator là một trong những role hiếm hoi được phép xóa Course, Chapter, Lesson (cùng với Manager).
- **Xuất bản (Publish)**: Xem xét thiết lập trạng thái `status` từ Draft sang Published để đưa khóa học lên hiển thị public.

## 2. Quản lý phân quyền và Settings
- Trực tiếp quản lý và gán quyền (Assign roles) cho Users, trong đó có việc gán một user cụ thể làm Giảng viên (Instructor) quản lý một khóa học.
- Quản lý các Master Data tác động đến Course như: Categories, Tags, Levels, và ngôn ngữ giảng dạy.
- Quản lý và phê duyệt các yêu cầu tạo/publish khóa học từ phía Instructor.

## 3. Quản lý Tracking và Cấp quyền truy cập (Enrollment)
- Xem báo cáo tiến độ (Progress reports) của mọi khóa học và mọi sinh viên.
- Được quyền bỏ qua mọi cổng thanh toán: Admin có thể "Manual Assign" một Course cho bất kỳ tài khoản Student nào thông qua backend quản trị, ép buộc ghi đè trạng thái enroll.
- Quản lý Cấp/Thu hồi điểm số bài Quiz kết thúc Lesson, ép buộc sinh viên hoàn thành (Mark Complete) hoặc thu hồi chứng chỉ.
