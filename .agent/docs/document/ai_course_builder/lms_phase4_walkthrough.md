# Tích hợp AI cho Zoom & Blog - Tài liệu & Hướng dẫn

**Giai đoạn**: 4
**Trạng thái**: Hoàn thành
**Phụ thuộc**: `lms_ai`, `lms_zoom`, `lms_blog`

## 1. Tổng quan Kiến trúc Kỹ thuật
Giai đoạn này đóng vai trò là nhịp cầu kết nối sức mạnh của "Nền tảng AI Lõi" (`lms_ai`) với các module tính năng thực dụng bên ngoài (`lms_zoom` và `lms_blog`).

### Trí tuệ Nhân tạo cho Blog (`lms_blog`):
- **Chèn Widget AI**: Vận dụng chuẩn Form Alter của hệ thống Drupal để gài khung **"✨ AI Blog Writer"** (Trợ lý Viết Blog) trực tiếp vào màn hình tạo Entity bài viết `lms_article`.
- **Drupal AJAX Automation**: Nhờ công nghệ tự động hóa AJAX, người dùng chỉ việc gõ Chủ đề mong muốn (Topic), chọn Từ khóa vàng (Keywords) và Âm sắc (Tone Giọng: Chuyên nghiệp/Thông thường/Thuyết phục). Khi bấm **"Generate Everything"**, một cấu trúc `#ajax` bất đồng bộ sẽ gọi thẳng vào core `lms_ai`.
- **Bóc tách JSON Thuần Khuyết**: Cỗ máy AI sử dụng sức mạnh Schema JSON ép OpenAI phải nhả về đúng định dạng có 4 trường thông tin: `title`, `content`, `seo_title`, `seo_description`. Sau đó luồng dữ liệu Ajax của Drupal sẽ tự auto-fill đoạn text này thẳng vào từng ô nhập liệu của Editor trên trình duyệt mà không cần phải F5 tải lại trang.

### Trí tuệ Nhân tạo cho Cửa Sổ Zoom (`lms_zoom`):
- **Tự sinh Lịch trình Agenda (Giáo án)**: Ngay tại khung cảnh của màn hình tạo Bài học (Lesson Creator), khi mà người dùng cuộn Form trường dữ liệu Type qua đúng giá trị `Zoom`, khu vực **"✨ Generate Zoom Agenda"** sẽ tự động trồi lên như một phép màu dựa vào API hiển thị `#states` của Drupal. Người dùng chỉ việc gõ Tên môn học và khởi động "Generate", Class Service của Zoom sẽ giúp tạo ra 1 đoạn danh sách đề cương đầy tính hấp dẫn thông qua mã HTML.
- **Tóm tắt Diễn biến Thông minh (VTT)**: Một cấu trúc hệ thống chạy ngầm `hook_cron()` được đưa vào vận hành. Cứ mỗi lần chạy nền, hệ thống tự lướt kho dữ liệu xem có Lớp học Zoom nào thuộc về thì quá khứ mà bị thiếu nhãn `<!-- AI_SUMMARY_DONE -->`. Nó sẽ kéo API bản VTT Transcript phụ đề về hệ thống, bơm vào OpenAI để "Nhặt Những Ý Chính" (Key Takeaway), và ở bước cuối cùng tự nối văn bản đó vào làm tư liệu để cho các học viên lỡ buổi học có thể vào mà đọc được ngay lập tức!

---

## 2. Hướng dẫn sử dụng tính năng
### Dành cho Giảng Viên / Biên Tập Viên:
- **Tăng Tốc Độ Xây Dựng Bài Viết Blog Lên Từng Tiết Giây**: Vào mục `/manage/blog/add`. Mở thẻ bên Trợ lý AI ở cột mảng. Gõ chủ trương (Ví dụ: "AI đang làm thay đổi công nghệ E-Learning ra sao rồi?"), ấn Create. Đợi một giây nhỏ là bạn đã sở hữu một Thẻ bài viết đong đầy đủ Văn bản, Cấu trúc Heading, cùng các Siêu dữ liệu SEO (SEO Title & Desc)!
- **Thiết Kế Cấu Trúc Khóa Học Zoom Tự Động**: Tại Cửa sổ bài học, điền tên Chuyên đề hấp dẫn và ấn "Generate Agenda". Đến khoảnh khắc ấn định lên và dạy. Sáng hôm sau tỉnh giấc, bạn sẽ được thưởng thức bộ tóm lược Tinh vi chi tiết của chính bài giảng của mình ở dưới cùng dòng tin gốc của Zoom! Mọi khâu thao tác được tự động xử lí và không cần con người mỏi tay!
