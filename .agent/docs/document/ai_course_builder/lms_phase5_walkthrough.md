# AI Course Builder (Trình Sinh Khóa Học AI) - Hướng dẫn & Tổng quan

**Giai đoạn**: 5
**Trạng thái**: Hoàn thành
**Phụ thuộc**: `lms_ai`, `lms_course`, `lms_lesson`

## 1. Tổng quan Kiến trúc Kỹ thuật
Bệ phóng tối thượng của hệ sinh thái AI trong nền tảng EverLMS - Trình **Xây Dựng Khóa Học** tự động. Đây là khối máy phát tối tân với độ chịu đựng rủi ro cao bậc nhất được hình thành nên từ Liên kết giữa: Form AJAX của Drupal, Lớp bảo vệ Schema JSON, và Luồng xử lý Cấp dưới (Background Queue Workers).

### Các thành phần lõi hoạt động:
- **`CourseBuilderForm.php`**: Form Wizard hiện diện thông qua địa chỉ `/manage/courses/ai-builder`. Cung cấp phương thức chèn trực quan và thanh lịch gạt bỏ sự nặng nhọc nhồi đúc thủ công xưa cũ bằng khối thiết kế qua 3 chỉ số đầu vào: Khái niệm Yêu cầu (Topic), Đối tượng Học Tập (Target Audience), và Giọng điệu của Chuyên gia (Tone).
- **`CourseBuilderService.php`**: Bất thình lình khi người Quản lý bấm chuột "Xây Dựng Khóa", khối động cơ Service được gọi để mào đầu khung prompt thượng hạng cho OpenAI, cưỡng chế nó phải gửi về chuẩn Cấu trúc Mảng đa lớp đại diện cho chuỗi Vòng tròn Sinh học: `Sections -> Lessons -> Content Outline`. Ngay sau đó Framework tự ném vào các bản `Course` và chuỗi `Lesson` lên tầng giao tiếp Cơ Sở Dữ Liệu (`Database Storage`) ở dưới định dạng **Bản nháp (Draft)**.
- **`CourseContentGenerator.php` (Đội máy cày Queue Worker)**: Để chống chọi với hiểm họa Vỡ mạch Timeout từ PHP (Ở trường hợp nó sinh ra cả trăm Bài học trong 1 lúc), khối lượng tải để ghi Dữ liệu Text HTML được gom vào chiếc "Giỏ Hàng Chờ" của Drupal (`lms_ai_content_generator`). Phi đội Worker (Đội máy cày) này sẽ cắm mặt làm việc miệt mài với từng bài qua lịch trình Cron ngầm. Bọn chúng tự sinh Văn Bản Sách giáo khoa 1 cách vô hạn, đính nó vào từng Bài học và âm thầm thay đổi Trạng Thái từ Draft sang Publish để rao tin!

---

## 2. Hướng dẫn Dành Cho Khối Quản trị (Admin)
Không cần dính một mớ mã lằng nhằng nào hết! Builder Thông minh sẽ auto lấy Khóa Token API đã thiết lập chặt trong nền Giai đoạn 3 nằm bên bảng `lms_ai.settings`.
Việc độc nhất phải quan tâm là hãy kiểm chứng quá trình Lệnh `Cron` của máy chủ Server đang vận hành (Ví dụ: vòng quay mỗi 5 phút kích nhịp 1 lần theo lệnh Drush: `drush cron`), bởi có thế đám "Đội máy cày" Worker ở Background mới được trả tiền để sản xuất nội dung sinh nhai tự động cho Cỗ Máy.

---

## 3. Hướng dẫn Móc Khoá Siêu Khóa Học (Dành Cho Giảng Viên)

1. Đi vào khu Quản Trị Hệ sinh Thái: **Manage > Courses** (`/manage/courses`).
2. Tới bấm cái nút màu sáng bật lên nhất: **"✨ Build with AI"**.
3. Định nghĩa Khối Hình hài Học Liệu:
   - **Tựa đề (Topic)**: Cực kì dễ hiểu: "Bí kíp Phân tích Ngôn ngữ Python Tiết kiệm Thời gian năm 2024"
   - **Mục tiêu tiếp thị (Audience)**: "Sinh viên ĐH Kỹ thuật", "Mẹ bỉm sữa", v.v
   - **Sắc điệu (Tone)**: Click nhẹ vào ô "Cảm xúc Dâng tràn (Casual & Enthusiastic)" để bài mang tiếng cười!
4. Đẩy cò Súng: Nhấn **"Build My Course! 🚀"**.
5. Kẹt lại mất khoảng tầm 30 tới khoảng 45 giây đếm ngược. Hệ thống trí Khôn lúc này tự động gánh vác việc vẽ ra lộ trình của Chuyên mục Module học!
6. Bùmm! Trang F5 sẽ đập nguyên hệ thống phân cảnh toàn bộ các module và khối lesson chi chít lên khung Màn hình Course Edit - Toàn bộ được định danh ở Trạng thái Placeholder (Đã đặt sẵn chỗ).
7. Bây giờ thì hãy tạm đóng màn hình đi rót Tách Cà phê. Ở phương sau Hệ Máy Chủ đang ngày đêm hoạt động qua Cron, bạn sẽ thấy nó đang gõ từng trang sách của Lesson đầy ắp những Chữ Text cực kỳ bài bản và auto tự Kích Phóng các Bài Viết Lên Lên Web!
