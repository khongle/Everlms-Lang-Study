# Bản Kế Hoạch Chi Tiết: Triển khai Zoom Class, LMS Blog & AI Provider

Tài liệu này là bản kế hoạch kỹ thuật chuyên sâu (Low-Level Technical Plan) để triển khai các Module mới còn thiếu trên nền tảng **EverLMS**.
**LƯU Ý QUAN TRỌNG:** Theo yêu cầu mới nhất, module **Zoom Class (`lms_zoom`)** sẽ là ưu tiên cao nhất được code **ĐẦU TIÊN**. Sau đó đến Module **Blog/News**, và tiếp theo mới quy hoạch nền tảng **AI Generator**.

*(Lưu ý: Không bao gồm source code thực thi, chỉ cung cấp blueprint.)*

---

## BƯỚC 1: XÂY DỰNG MODULE ZOOM CLASS (ƯU TIÊN 1)

Thay vì tích hợp chung chạ, tính năng lớp học trực tuyến sẽ được tách ra thành module độc lập `lms_zoom`.

### Phase 1.1: Trục Lõi API Client
1. **API Client (`lms_zoom.api_client`)**:
   - Authentication: Setup Server-to-Server OAuth.
   - Các API wrappers: tạo Meeting (`createMeeting`), lấy thông tin (`getMeeting`), lấy bản ghi (`getRecordings`).
2. **Meeting Manager (`lms_zoom.meeting_manager`)**:
   - Móc nối (mapping) giữa một Lesson (nơi `type` = `live_session`) và ID của Zoom Meeting.
   - Point Webhook nhận từ Zoom để làm công cụ Auto-Attendance (Tự động cập nhật điểm danh vào `UserLesson`).

### Phase 1.2: UI/UX Màn Hình Lesson
1. **Quản trị (Instructor Admin)**: 
   - Thêm tuỳ chọn "Live Zoom Class" khi tạo Bài học (Lesson).
   - Khi Instructor nhấn Save, backend tự động Call API tới Zoom để tạo phòng và ném link Join về DB.
2. **Giao diện học viên (Student UI)**:
   - Hiển thị đồng hồ đếm ngược (Countdown timer) tới giờ học.
   - Nút **Join Class** chỉ sáng lên trước 15 phút.

---

## BƯỚC 2: XÂY DỰNG MODULE BLOG/NEWS (ƯU TIÊN 2)

Bài viết Blog/News sẽ là một **Custom Entity** (`lms_article`) để đồng bộ kiến trúc Admin với danh sách Khoá học.

### Phase 2.1: Cấu trúc Module (`lms_blog`)
1. **Entity Definition (`LmsArticleEntity.php`)**:
   - Base fields: `title`, `content`, `slug`, `status`, `published_at`, `seo_title`, `seo_description`.
2. **Quản lý UI Admin**:
   - Dựng Route List `/manage/blog`.
   - Dựng Form tạo bài viết `/manage/blog/create`.

---

## BƯỚC 3: XÂY DỰNG AI PLATFORM LÕI (`lms_ai`)

Hệ thống AI cốt lõi đóng vai trò như một thư viện nội bộ cấp phát AI cho toàn hệ thống EverLMS (Zoom, Blog, Course).

### Phase 3.1: Provider Pattern
1. Dùng Drupal Plugin API (`AiProviderManager`).
2. Tích hợp OpenAI Plugin kèm logic validate Data Schema JSon (Structured Outputs).
3. Thiết lập Admin Config `/admin/config/lms/ai-settings` để cấu hình API Key và Rate Limit.

---

## BƯỚC 4: ỨNG DỤNG AI VÀO ZOOM CLASS & BLOG

Sau khi có `lms_ai`, rải các widget AI trợ lực vào các chức năng đã làm ở Bước 1 & Bước 2.

### Phase 4.1: AI for Zoom Class
1. **Auto-Agenda**: Khi giảng viên gõ tiêu đề buổi học, một nút "✨ Generate Agenda" gọi OpenAI sinh ra khung nội dung.
2. **Smart Transcript Summary**: Chạy Background Cron -> tải file **Zoom Cloud Recording VTT (phụ đề)** -> Đẩy vào `lms_ai` tóm tắt -> Tự động dán Tóm tắt (Key take-aways) vào chi tiết bài học cho học viên ôn tập lại.

### Phase 4.2: AI for Blog
1. Nhúng widget **"✨ AI Blog Writer"** vào form tạo Blog (`lms_article`).
2. Trưởng ban biên tập chỉ cần gõ Topic + Keywords -> AJAX bắn về Frontend điền sẵn Tiêu đề, Nội dung HTML, và thông số SEO.

---

## BƯỚC 5: TRIỂN KHAI AI COURSE BUILDER (VÍCH ĐÍCH)

Ứng dụng tinh hoa AI để đẻ ra nguyên một khung Khóa học cực lớn.

### Phase 5.1: Màn hình Create Course Wizard
1. Thêm nút "✨ Generate Course with AI" vào Course Form gốc.
2. Form AJAX 3 bước: Nhập Input Topic -> Hiển thị Tree Chapters/Lessons JSON cho Admin review -> Lưu xuống Data base.

### Phase 5.2: Content Queue Worker (Xử lý ngầm)
1. Bỏ qua việc chờ API sinh content cho 50 bài học cùng lúc (tránh HTTP Timeout).
2. Lưu vỏ Entity rỗng -> PUSH vào `Drupal Queue`.
3. Daemon cắn Queue tải text bài học và Quiz Questions cho từng Lesson cách nhau 3s/lần.

---

## BƯỚC 6: PHỤ LỤC (KIẾN TRÚC & PHÂN QUYỀN BỊ THIẾU SÓT)

Cập nhật lại các thiết kế gốc của EverLMS do chưa có 3 module mới này:
1. `system-architecture.md`: Push `lms_blog` (vào Content Layer), `lms_zoom`, `lms_ai` (vào Integration Layer). Tổng số custom modules thành 26.
2. `by-entities/README.md`: Thêm `lms_article` (Hỗ trợ JSON:API cho App).
3. `roles-permissions.md`: Cấp quyền Quản lý Blog cho Editor/Instructor. Quyền xài AI chỉ cấp cho Admin.

---

## QUY TRÌNH KIỂM THỬ & LÀM TÀI LIỆU BẮT BUỘC (QA & DOCUMENTATION)

Để đảm bảo tính ổn định và chuyên nghiệp cho EverLMS, **sau khi hoàn thành bất kỳ một thẻ Task hay Module nào ở trên, hệ thống đều bắt buộc phải trải qua bước QA và Document trước khi báo cáo hoàn thành.**

1. **Verify Unit & Logic**: Kiểm tra lại các hook, service có handle exception hay null pointer không.
2. **Verify Database Entity**: Xác minh entity mới (`lms_article`, `lms_zoom`) được lưu vào DB với đúng cấu trúc field, không bị lỗi schema validation.
3. **Verify API/AJAX**: Test API trả về (từ OpenAI / Zoom) với dữ liệu thực tế, kiểm tra giao diện load Modal không bị crash hoặc loop.
4. **Báo cáo Walkthrough**: Dev (Antigravity) sẽ phải cung cấp log, kết quả test và mô tả `walkthrough.md` xác nhận module đã chạy mượt mà không có Fatal Error.
5. **Review & Documentation**: Bắt buộc tạo tài liệu (document file `.md`) và lập hướng dẫn sử dụng (User Guide / Developer Guide) cho chức năng vừa làm xong, lưu vào folder `docs/` để đồng bộ với kiến trúc. Chỉ sau khi làm xong tài liệu thì mới được phép nhảy sang Bước (Phase) tiếp theo.

---

## Hướng Dẫn Thực Hiện Ngay (Next Action)
Hệ thống **ưu tiên tuyệt đối** cho tính năng Live Session.
Hành động code tiếp theo (của dev):
1. Bootstrap module `lms_zoom`.
2. Tích hợp SDK/API Client của Zoom với Server-to-Server OAuth.
3. Code Field Integration cho `Lesson` entity.
