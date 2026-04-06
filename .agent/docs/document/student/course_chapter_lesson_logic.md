# Course, Chapter, & Lesson Logic for Student

Vai trò **Student** là người dùng cuối thụ hưởng nội dung. Logic của Student xoay quanh việc Truy cập, Học tập (Learning), và Tính toán tiến độ (Progress).

## 1. Trạng thái Truy cập (Access & Enrollment)
- Phải có trạng thái Enroll để bắt đầu tính Progress. Trạng thái này có được qua 3 luồng:
  - Bấm Enroll trực tiếp đối với các khóa đánh dấu là `Free`.
  - Check-out qua cổng thanh toán với các khóa `Paid`.
  - Tự động lấy quyền qua đặc quyền đăng ký gói `Membership`.
- Có thể xem trước nội dung Lesson mà không cần Enroll nếu Lesson đó được thiết lập `field_free_preview = true`.
- Bị hạn chế luồng học nếu khóa học cài đặt `prerequisites` (buộc phải đạt chapter trước mới unlock được chapter sau) hoặc `drip_days` (bài được tự khóa lại, mở ra sau N ngày kể từ lúc Enroll).

## 2. Tương tác và Hoàn thành Lesson
Để hoàn thành 1 khóa học thì cấp độ tính toán Progress diễn ra từ bé đến lớn (Cascade Logic):
1. **Bài học (User Lesson Tracker)**: 
   - Bài giảng *Video*: Được đánh dấu Complete tự động khi học sinh xem qua mốc 90% thời lượng.
   - Bài giảng *Text*: Học sinh phải click thủ công nút bấm "Mark as Complete". 
   - Bài tập *Quiz/H5P/SCORM*: Hệ thống đánh dấu Complete sau khi học sinh đạt điểm pass bài kiểm tra.
2. **Chương (User Chapter Tracker)**:
   - Hệ thống tự động tính: `(Số lượng lesson đã complete / Tổng bài trong chapter) * 100`.
   - Lên 100% đánh dấu đã hoàn thành chương đó.
3. **Khóa học (User Course Tracker)**:
   - Trung bình cộng (AVG) của toàn bộ các Chapters.
   - Phải học đủ toàn bộ, làm bài Final Quiz nếu có. Đạt 100% thì khóa học kết thúc.

## 3. Hoàn thành và Nhận chứng nhận
- Khi Progress Course đạt 100% và qua mọi kì thi cần thiết, học sinh sẽ được tự động kích hoạt Cấp phát bằng (Certificate template).
- Quá trình Membership Subscription nếu xảy ra lỗi trừ tiền hết hạn Grace Period (3 ngày), học viên sẽ lập tức bị thu hồi (Revoke Course Access) quyền học lại khóa học đó.

## 4. Logic "Continue Learning" (Continue URL)
Khi học viên bấm vào nút "Continue Learning" (bất kỳ đâu từ Course Detail, Dashboard, hoặc My Courses), hệ thống sẽ tính toán **target_lesson_id** theo các ưu tiên sau:
1. **Bài học tương tác gần nhất (Last Accessed)**: Truy vấn trong bảng `lms_user_lesson` với `field_status = 'in_progress'`, được sắp xếp theo thời gian `changed` mới nhất. Nếu tìm được, hệ thống sẽ đưa thẳng người dùng đến bài học này.
2. **Bài học tiếp theo sau bài đã hoàn thành**: Nếu bài học quan tâm gần nhất đã chuyển trạng thái `completed`, hệ thống sẽ lấy bài học ngay sau nó trong bộ giáo trình (`curriculum order`).
3. **Bài học đầu tiên chưa hoàn thành (First Uncompleted - Fallback)**: Quét toàn bộ giáo trình và loại bỏ những bài đã có bản ghi `completed`, bài học đầu tiên còn sót lại sẽ là đích đến.
4. **Bài học đầu tiên của khóa (First Lesson)**: Nếu học viên chưa bao giờ tương tác khóa học, hoặc đã hoàn thành 100% tất cả các bài, sẽ fallback về index `0` của danh sách bài giảng.
