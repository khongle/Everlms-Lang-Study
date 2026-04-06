# Mô tả Tổng quan
Phát triển tính năng "Generate Lessons Detail" (Tạo tự động mô tả bài học) bên trong module EverLMS AI Engine. Tính năng này sẽ quét qua file `ai_curriculum.json`, tìm những bài học (lesson) chưa được tạo nội dung (`is_detail_generated` vẫn đang trống), gửi Prompt cho AI để viết mô tả, và cuối cùng lưu kết quả vào thẳng cơ sở dữ liệu Drupal (`lms_lesson` entity) lẫn file JSON.

## Yêu cầu kiểm tra từ người dùng (Review Required)
> [!IMPORTANT]
> Vui lòng xem bản Plan này để đảm bảo rằng các biến động như `{{course_title}}` (Tên khóa học), `{{chapter_title}}` (Tên chương) và `{{lesson_title}}` (Tên bài học) là ĐÃ ĐỦ dữ kiện để AI có thể viết được Mô tả bài học (Lesson Description) cho bạn chưa nhé. 

## Các thay đổi dự kiến (Proposed Changes)

### 1. Cập nhật Giao diện Form & Cấu hình
- **File: `web/modules/custom/lms_ai/src/Form/AiEngineUserForm.php`**
  - **[MODIFY]**: Chèn khung soạn thảo văn bản `lesson_detail_prompt` (Textarea) vào bên trong mục `$form['course_builder']['lesson_actions']`, kèm chung với các liên kết tiện ích "Load Default | Reset | Clear".
  - **[MODIFY]**: Thêm nút Action **"Generate Lessons Detail"** vào mục `lesson_actions` và gắn liền với hàm AJAX `::ajaxGenerateLessonsDetail` để xử lý logic chạy ngầm.
  - **[MODIFY]**: Cập nhật hàm `submitForm()` để nó có thể lưu nội dung Prompt của bài học vào bảng cấu hình `lms_ai.engine.settings`.

### 2. Xử lý Logic Sinh bài (AJAX Generation Logic)
- **File: `web/modules/custom/lms_ai/src/Form/AiEngineUserForm.php`**
  - **[MODIFY]**: Viết hàm xử lý ngầm `ajaxGenerateLessonsDetail()`.
    - Đọc file `ai_curriculum.json`.
    - Lấy nội dung mẫu Prompt từ form (`lesson_detail_prompt`).
    - Dùng vòng lặp quét qua cây hệ thống: `$data` -> `courses` -> `chapters` -> `lessons`.
    - Nếu thấy `$lesson['is_detail_generated']` bị trống (nghĩa là chưa tạo).
    - Tiến hành thay thế tự động các biến: `{{training_program}}`, `{{langcodes_list}}`, `{{langcode}}`, `{{course_title}}`, `{{chapter_title}}`, và `{{lesson_title}}` vào Prompt.
    - Ép AI phải trả về định dạng chuẩn JSON (Strict JSON).
    - Gọi API Provider (OpenAI/Gemini).
    - Giải mã nội dung JSON do AI trả về để lấy field `lesson_description`.
    - Load `lms_lesson` entity bằng cái `$lesson['id']` có sẵn trong JSON.
    - Cập nhật giá trị vào data field của entity (Thường là `field_description` hoặc `body`) rồi Save lại vào Database.
    - Cập nhật mảng trên RAM: gán `$lesson['is_detail_generated'] = TRUE` và `$lesson['lesson_description']` bằng text vừa tạo ra.
    - **Lưu Tức Thì (Incremental Save):** Ghi ngay mảng File `$data` vừa cập nhật đè lại file `ai_curriculum.json` ngay trong lòng vòng lặp. Để nhỡ Request có bị Timeout/Đứt cáp thì những bài học đã lưu xong sẽ không bao giờ bị mất hoặc bị bắt phải chạy lại.
    - Tạm dừng 2 giây (`sleep(2)`) giữa các bài học liên tiếp để tránh bị OpenAI/Google khóa IP vì Rate Limits.

### 3. Prompt Mặc định (Default Prompt)
- **File: `web/modules/custom/lms_ai/prompt/lesson_detail.txt`**
  - **[NEW]**: Tạo sẵn một file Text rỗng hoặc mẫu Prompt cơ bản cho phần Bài học nếu file chưa tồn tại (Hoặc bạn có thể tự tạo tay một file txt vào folder này luôn nha).

## Kế hoạch Test / Nghiệm thu (Verification Plan)
### Chạy test tự động (Automated Tests)
- Chạy lệnh `ddev drush cr` để xóa Cache cho hệ thống Drupal báo nhận giao diện Form mới.
- Kiểm tra trực tiếp trên trang cấu hình `/admin/config/lms/ai` xem phần "Lesson Actions" đã bung ra đúng như hình ảnh bạn yêu cầu chưa.

### Kiểm tra bằng tay (Manual Verification)
1. Thử nhập một dòng text bất kỳ vào ô "Generate Lessons Detail Prompt" và bấm "Save Configuration" xem nó có báo thành công và giữ lại mã Text không.
2. Bấm nút màu sáng "Generate Lessons Detail".
3. Mở file `ai_curriculum.json` ra đối chiếu kiểm tra xem các Lesson chưa tạo đã có trường `lesson_description` với giá trị bên trong chưa. Và check luôn trên URL hoặc Edit Trang của bài học trên Drupal xem Entity đã có Content chưa.
