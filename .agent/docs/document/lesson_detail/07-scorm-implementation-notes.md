# Ghi Chú Yếu Trọng Khi Triển Khai SCORM Player (Phase 1)

Trong quá trình tích hợp SCORM Player cho mục đích Demo (và cũng là bản lề cho hệ thống thật sau này), chúng ta đã rà soát và xử lý thành công 4 bài toán kỹ thuật "kinh điển" của chuẩn SCORM. Dưới đây là tài liệu lưu trữ để Dev team tham khảo cho các Phase tiếp theo:

## 1. Cơ chế bung nén (Extraction)
- SCORM bản chất là một file `.zip` chứa HTML/JS/Flash.
- **Giải pháp hiện tại:** Khi Entity `lms_lesson` được Save (thông qua `hook_entity_insert` và `hook_entity_update`), hệ thống sẽ tự động dùng class `ZipArchive` giải nén vào thư mục `public://scorm_extracted/{lesson_id}`.
- Để tránh rác file cũ khi giáo viên upload đè SCORM mới, hệ thống được thiết lập tự động xoá sạch (`deleteRecursive`) thư mục ID đó trước khi giải nén sượt mới.

## 2. Tìm kiếm File chạy chính (LMS Launch File)
- **Vấn đề:** Không phải SCORM nào cũng chạy bằng `index.html`. Các Tool như Articulate Storyline thường dùng `index_lms.html`, `story.html` hoặc các tên tùy biến khác.
- **Giải pháp:** Hệ thống đã được bổ sung thuật toán đọc file `imsmanifest.xml` (trái tim của SCORM) thông qua Regex. Nó sẽ trích xuất tham số `href` của thẻ `<resource... adlcp:scormtype="sco">` đầu tiên.
- Sau khi tìm được tên file chuẩn, hệ thống tự động sinh ra một file `index.html` trung gian chứa lệnh thẻ `<meta http-equiv="refresh">` chuyển hướng trình duyệt tới file tự động tìm được. Nhờ đó, Twig ở Frontend mạc định chỏ `<iframe src=".../index.html">` luôn luôn chạy trúng đích.

## 3. Vấn đề bộ nhớ đệm (Browser Caching)
- **Vấn đề:** Trình duyệt lưu Cache cực kỳ gắt gao các file HTML tĩnh, dẫn đến việc sửa Lesson update SCORM mới nhưng Frontend vẫn hiển thị SCORM giao diện cũ.
- **Giải pháp:** Bổ sung Cache Buster (bùa chống cache ảo) vào đường dẫn src của iframe: `<iframe src=".../index.html?v={{ random() }}">`. Ép trình duyệt luôn tải file chuyển hướng mới nhất mỗi khi load trang Lesson.

## 4. Quản lý Tracking API (Giả lập SCORM API)
- **Vấn đề (Đặc biệt quan trọng):** SCORM 1.2 và SCORM 2004 khi khởi chạy sẽ lùng sục lên Parent Window để tìm Object Javascript tên là `API` (hoặc `API_1484_11`). Nếu không tìm thấy (vì LMS chưa code Tracking Engine), Content sẽ văng Pop-up lỗi báo *"Unable to acquire LMS API"* rồi dừng chạy.
- **Giải pháp Tạm thời (Phục vụ Demo):** Đã nhúng một đoạn mã JS **Dummy API** vào ngay trước Iframe trong file Twig `lms-lesson-view.html.twig`.
  - Object này chứa đầy đủ các hàm Mock (hàng giả) như `LMSInitialize()`, `LMSGetValue()`, `LMSSetValue()` luôn luôn trả về `"true"`.
  - Content bị đánh lừa là LMS đang hoạt động bình thường, và Player sẽ chạy cực mượt.
- **Nhiệm vụ cho Phase Kế Tiếp (Phase Tracking):** Cần thay đoạn script Mock này bằng **1 thư viện LMS SCORM xịn** (VD: SCORM API wrapper của Rustici hoặc tự build bằng JS), hàm `LMSSetValue` thay vì in ra `console.log` sẽ dùng Ajax gán Dữ liệu (Điểm số, Thời gian, Trạng thái Hoàn thành) bắn thẳng về Server để lưu vào table `lms_progress_log` (hoặc Entity `QuizResult` tùy thiết kế DB).
