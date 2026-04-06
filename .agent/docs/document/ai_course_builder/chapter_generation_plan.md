# Plan: Generate Chapters Detail via AI Engine

## 1. Goal Description
Triển khai phần "Chapter Actions" trong giao diện AI Engine (`AiEngineUserForm`). Tính năng này cho phép hệ thống tự động sinh và cập nhật thông tin (ví dụ: `description` chi tiết) cho các **Chapters (`lms_chapter`)** dựa trên prompt được người dùng định nghĩa. Tính năng này sẽ quét các Chapters thuộc các khóa học đang được chọn ở khối Settings và gửi tóm tắt lên AI Provider để sinh dữ liệu JSON tương ứng, sau đó parse và lưu vào Entity.

## 2. User Review Required
> [!NOTE]
> - Prompt mặc định từ screenshot mong đợi AI trả về JSON cấu trúc: 
>   `{ "chapter": [ { "{{langcode}}": { "chapter_title": "...", "chapter_description": "..." } } ] }`
> - Hệ thống sẽ lấy giá trị `chapter_description` từ chuỗi JSON trả về để cập nhật vào trường mô tả của Entity `lms_chapter`. Hãy xác nhận tên Machine Name thực tế của field chứa nội dung chapter trong database của bạn (nếu khác `field_description`).
> - Có cần chạy nền (queue worker) cho tiến trình này không, hay chạy trực tiếp trong AJAX request? Thường nếu sinh hàng loạt chapter thì chạy trực tiếp dễ bị vượt quá time limit của server.

## 3. Proposed Changes

### `lms_ai` Module

#### [MODIFY] `web/modules/custom/lms_ai/src/Form/AiEngineUserForm.php`
- **Giao diện (`buildForm`)**:
  - Bổ sung cấu trúc trong `$form['course_builder']['chapter_actions']`:
    - Thêm tuỳ chọn `$form['course_builder']['chapter_actions']['chapter_detail_prompt']` dạng textarea.
    - Thêm các liên kết `Load Default | Reset | Clear`.
    - Thêm thẻ Submit: `Generate Chapters Detail` sử dụng AJAX `::ajaxGenerateChaptersDetail`.
  - Pass biến `$default_chapter_prompt` lấy từ file `prompt/chapter_detail.txt` xuống mảng cấu hình `drupalSettings` để JS xử lý.

- **Lưu Cấu Hình (`submitForm`)**:
  - Lưu `chapter_detail_prompt` vào file thiết lập config chung `lms_ai.engine.settings`.

- **Xử Lý AJAX (`ajaxGenerateChaptersDetail`)**:
  - Gọi hàm `getCurriculumJsonData()` để lấy mảng data từ `ai_curriculum.json`.
  - Khởi tạo Provider AI: `$ai_manager->createInstance($active_provider);`.
  - Duyệt qua cây JSON: `Categories -> Courses -> Chapters`.
  - Kiểm tra nếu Chapter chưa được tạo chi tiết (`empty($chapter_data['is_detail_generated'])`):
    - Thay thế các biến động trong prompt mẫu: `{{chapter_name}}` thành `$chapter_data['chapter_title']`, `{{langcode}}`...
    - Gửi request AI lấy mảng JSON trả về.
    - Tìm và load thực thể `lms_chapter` dựa vào ID: `$chapter_data['id']`.
    - Cập nhật trường `field_description` cho Entity và `$chapter->save()`.
    - Cập nhật dữ liệu vào node JSON: `$chapter_data['is_detail_generated'] = TRUE` và `$chapter_data['chapter_description'] = ...`.
    - Ghi đè cập nhật file `ai_curriculum.json` ngay lập tức: `file_put_contents(...)`.
    - Lệnh sleep(2) để tránh hit rate limit của các AI API provider.
  - Quản lý trả về JSON Report đếm được bao nhiêu thành công, bao nhiêu lỗi.

#### [NEW] `web/modules/custom/lms_ai/prompt/chapter_detail.txt`
- Thêm file `.txt` chứa JSON template mặc định để JS tải lên form. Format giống thiết kế trong screenshot:
  ```json
  "chapter" : [
    {
      "{{langcode}}": {
        "chapter_title": "{{chapter_name}}",
        "chapter_description": "string"
      }
    }
  ]
  ```

#### [MODIFY] `web/modules/custom/lms_ai/js/lms_ai_engine.js` (nếu có logic Load Default)
- Đảm bảo bind class `.load-default` tương thích hoạt động điền text mẫu vào cho text area `chapter_detail_prompt`.

## 4. Verification Plan

### Manual Verification
1. Truy cập `/admin/everlms-ai-engine`.
2. Kiểm tra chọn Course cụ thể ở mục cài đặt chung hệ thống AI.
3. Kéo xuống phần `AI Course Builder` > `Chapter Actions`.
4. Nhấn `Load Default` để chèn prompt vào ô Textarea của Chapters.
5. Nhấn nút `Generate Chapters Detail`.
6. Chờ AJAX throbber xử lý và xem Alert kết quả thông báo.
7. Vào trang quản lý cấu trúc khoá học (Course Curriculum), mở Edit Chapter để xem trường mô tả (Description) đã đầy đặn theo ý muốn chưa.
