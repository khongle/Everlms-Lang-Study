# Kế Hoạch Tổng Thể: Nâng Cấp Trang Lesson Details (Theo Từng Phase)

Để đảm bảo chất lượng, dễ dàng kiểm thử và không làm gián đoạn mã nguồn hiện tại, tính năng Lesson Details sẽ được nâng cấp theo 4 Giai đoạn (Phases) rành mạch dưới đây:

## Phase 1: Nâng cấp Giao diện Lõi (Core UI/UX Revamp)
**Mục tiêu:** Cải thiện trải nghiệm nhìn và bố cục của trang học tập cho khớp với thiết kế Glass UI cao cấp.
- **Frontend / CSS:** 
  - Quy hoạch lại bố cục Sidebar (Danh sách bài học) bên phải và Main Content (Trình phát Video) bên trái. Đảm bảo Responsive hoàn hảo trên điện thoại.
  - Custom lại Iframe của Video (YouTube/Vimeo) để chiếm toàn màn hình, bo góc đẹp mắt (border-radius), có viền glass.
  - Làm đẹp nội dung Text (field_content) và Typography để sinh viên dễ đọc dài hạn.
  - Làm nổi bật nút mũi tên "Next/Prev Lesson" và trạng thái bài học (Active state).
- **Trạng thái:** Sẵn sàng code.

## Phase 2: Tối ưu Trải nghiệm "Mark Complete" và Tracking
**Mục tiêu:** Cải thiện dòng chảy tương tác (Interaction Flow) mượt mà mà không cần load lại trang.
- **Javascript / Backend API:** 
  - Hoàn thiện luồng bấm nút "Mark Complete" gọi AJAX -> Cập nhật tick xanh bên Sidebar -> Tự động chuyển thẳng sang bài tiếp theo.
  - Thiết lập nền tảng Tracking thời gian xem Video thực tế (Lưu số giây đang xem trên local/backend).
- **Trạng thái:** Cần chỉnh sửa `lms-lesson-view.html.twig`, nối API.

## Phase 3: Hiển thị Tài liệu Mở rộng (Attachments & Resources)
**Mục tiêu:** Xây dựng khu vực "Tài nguyên tải về" thu hút, rõ ràng.
- Xây dựng một khu vực Card riêng mang tên "Extra Materials" phía dưới video.
- Thay vì list gạch đầu dòng khô khan, tài liệu đính kèm (`field_attachments`) sẽ hiển thị thành các dạng Thẻ (Card) nhỏ, có icon định dạng (PDF, DOCX, ZIP) tương ứng, hiển thị nút Download nổi bật.
- Cải thiện luồng tải file bảo mật.
- **Trạng thái:** Phụ thuộc vào dữ liệu. Dễ thực hiện.

## Phase 4: Tính năng Q&A / Hệ thống Phản hồi Bài giảng (Khó nhất)
**Mục tiêu:** Sinh viên tương tác trực tiếp với giáo viên ngay bên trong bài học.
- **Backend Architecture:** Khởi tạo một Sub-module (ví dụ: `lms_lesson_qa` hoặc sử dụng hệ thống Drupal Comment Core) gán vào thực thể `lms_lesson`.
- **UI:** Thêm một panel / tab có tên **"Q&A / Thảo luận"** kế bên mục "Nội dung bài học" hoặc nằm ngay dưới video.
- Người học có thể post câu hỏi. Hệ thống gửi Email Notification cho giáo viên. Giáo viên reply => Học sinh nhận phản hồi.
- Bổ sung Text Format hỗ trợ chèn hình ảnh và code snippet trong khung chat.
- **Trạng thái:** Tính năng lập trình hoàn toàn mới, cần định hình DB.

---
**=> Anh xem qua các Phase nhé. Chúng ta sẽ bắt đầu từ Phase 1 (Nâng cấp giao diện Core UI/UX) trước luôn nhé anh?**
