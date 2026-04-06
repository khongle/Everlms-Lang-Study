# Phân tích Các Loại Bài Học (Lesson Types)

Dựa trên cấu trúc thực tế của hệ thống (Entity `Lesson` trong `lms_lesson.module`), một bài học hiện tại có thể được cấu hình qua trường `field_type` với 7 loại khác nhau.

## 1. Video (Bài giảng Video)
- **Cấu hình:** Giáo viên có thể dán `field_video_url` (Link YouTube, Vimeo) hoặc tải file video trực tiếp lên `field_video_file` (định dạng mp4, webm, ogg).
- **Hiển thị (Frontend):** 
  - Giao diện người học (`lms-lesson-view.html.twig`) sẽ ưu tiên render ra một thẻ `<iframe>` bọc trong class `lms-learn__video-wrapper` để chạy video.
  - Các đường link YouTube/Vimeo sẽ được tự động parse sang dạng URL Embed tương ứng thông qua Controller `UserPortalController::lessonView()`.

## 2. Text (Bài giảng Văn bản)
- **Cấu hình:** Sử dụng trình soạn thảo văn bản Rich Text (WYSIWYG) tại trường `field_content`.
- **Hiển thị (Frontend):** Render trực tiếp mã HTML mà không hiển thị khung Video. Thiết kế sử dụng màu chữ xám nhạt (`#c9d1d9`) và background glass UI mờ để làm nổi bật văn bản trên nền tối.

## 3. Quiz (Bài Thi/Trắc Nghiệm)
- **Cấu hình:** Giáo viên sẽ chọn một bài thi tương ứng thông qua trường liên kết thực thể `field_quiz`.
- **Hiển thị (Frontend):** Hiển thị màn hình chờ (Placeholder) với icon dấu hỏi chấm lớn và một nút bấm xanh lá cây nổi bật **"Bắt đầu bài thi (Take Quiz)"**. Khi sinh viên bấm vào nút này, hệ thống sẽ chuyển hướng sang trang làm bài thi của module Quiz (`/quiz/take/{id}`).

## 4. Zoom Live (Lớp học trực tuyến)
- **Cấu hình:** Hệ thống có sẵn trường `field_zoom_meeting_id` và `field_zoom_schedule` (thời gian diễn ra).
- **Hiển thị (Frontend):** Mặc định đang hiển thị dạng hộp Placeholder *"Zoom content"*. Có thể phát triển thêm chức năng đếm ngược đến giờ học và nút Join Meeting.

## 5. SCORM (Gói bài giảng đóng gói chuẩn)
- **Cấu hình:** Tải lên file zip chuẩn SCORM vào trường `field_scorm_package`.
- **Hiển thị:** Mặc định đang ở dạng Placeholder. Tương lai cần code thêm block iframe để player SCORM chạy trực tiếp trên web.

## 6. H5P Interactive (Bài giảng tương tác H5P)
- **Cấu hình & Hiển thị:** Đang hiển thị ở dạng Placeholder *"H5p content"*. Cần tích hợp thư viện H5P render engine vào Drupal trong tương lai nếu muốn sử dụng mạnh mẽ tính năng này.

## 7. Mixed (Hỗn hợp)
- Mặc định sẽ load hiển thị Placeholder. Đây là loại bài giảng mở rộng chờ người dùng tuỳ biến (ví dụ: vừa có video, vừa có slide tương tác). Thường sẽ dùng text/html nhúng trực tiếp.
