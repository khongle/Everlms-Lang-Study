# Kế hoạch Bổ sung Trường Giới thiệu Khóa Học

## 1. Mục Tiêu
Bổ sung 3 field mô tả còn thiếu cho trang chi tiết từng khóa học:
- **What You'll Learn** – Học viên sẽ học được gì
- **Prerequisites** – Yêu cầu đầu vào
- **Who This Course Is For** – Đối tượng phù hợp

Content cần được sinh ra cho đủ 40 khóa học dựa trên ngữ cảnh trong file `seed-input.json`. Đồng thời cập nhật hàm import Drush (`ContentToolsCommands.php`) để map các field này vào Drupal entity với định dạng rich text (HTML).

## 2. Các Thay Đổi Dự Kiến

### Bước 1 – Sinh dữ liệu JSON (Script tự động)

Thay vì chỉnh tay 40 file JSON (= 120 block nội dung), ta viết một script PHP thông minh tại `.agent/scripts/patch_course_fields.php`. Script sẽ:
1. Đọc `seed-input.json` để lấy ngữ cảnh từng khóa
2. Dựa vào `title`, `description`, `level`, `category`, `keywords`, `notes` → Render HTML đặc thù riêng cho mỗi khóa
3. Ghi kết quả vào key mới trong `"course"` của từng file `course-XXX.json`

Ví dụ format JSON output:
```json
{
  "what_you_will_learn": "<ul><li>Hiểu rõ <strong>[Keyword 1]</strong>...</li></ul>",
  "prerequisites": "<ul><li>Kiến thức cơ bản về [Level]...</li></ul>",
  "who_this_course_is_for": "<ul><li>Điều dưỡng quốc tế làm việc trong lĩnh vực [Category]...</li></ul>"
}
```

### Bước 2 – Cập nhật Logic Import Drupal

#### [SỬA ĐỔI] `web/modules/custom/lms_content_tools/src/Commands/ContentToolsCommands.php`
Cập nhật hàm `importCourse()` để map 3 field mới. Machine name dự kiến:
- `field_what_you_will_learn`
- `field_prerequisites`
- `field_who_this_course_is_for`

Code bổ sung vào phần `$courseFields`:
```php
if (!empty($courseData['what_you_will_learn'])) {
  $courseFields['field_what_you_will_learn'] = [
    'value' => $courseData['what_you_will_learn'],
    'format' => 'full_html',
  ];
}
// Tương tự cho prerequisites và who_this_course_is_for
```

### Bước 3 – Cập nhật Entity đang tồn tại trong DB

Vì `importCourse()` hiện tại sẽ bỏ qua các khóa đã tồn tại (`Course already exists: @title — skipping.`), ta sẽ thêm logic cập nhật trực tiếp vào entity ngay trong script. Script sẽ:
- Load từng `lms_course` entity theo tên
- Set 3 field mới
- Gọi `->save()` để lưu — không cần xóa hay import lại từ đầu

> Cách tiếp cận này tương tự script fix thumbnail đã làm trước đó.

## 3. Câu hỏi cần xác nhận

> **QUAN TRỌNG:** Machine name của 3 field trong Drupal có đúng là:
> - `field_what_you_will_learn`
> - `field_prerequisites`
> - `field_who_this_course_is_for`
> 
> Nếu khác (ví dụ `field_course_prerequisites`), hãy báo lại để cập nhật.

## 4. Kế Hoạch Kiểm Tra

### Tự động
```bash
# Bước 1: Sinh HTML + cập nhật tất cả file JSON
php .agent/scripts/patch_course_fields.php

# Bước 2: Bơm dữ liệu vào Drupal ngay lập tức
ddev exec drush scr .agent/scripts/patch_course_fields.php
```

### Thủ công
- Vào `courses/entity-id/edit` trên UI
- Kiểm tra 3 Rich Text Editor đã được điền đầy đủ nội dung HTML đẹp chưa
