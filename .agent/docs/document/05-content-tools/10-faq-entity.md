# EverLMS — Quản lý Hỏi Đáp (FAQ Custom Entity)

> Tài liệu hướng dẫn thiết kế và khai thác dữ liệu của hệ thống Hỏi & Đáp `lms_faq`, phục vụ cho cả Trang Danh Sách Khóa Học và Trang Nền Tảng (Global Page).

## 1. Bản Cất Trúc Dữ Liệu (Schema)
Vì hệ thống FAQ thường phức tạp hơn các bản tin hiển thị thuần, đội ngũ đã xây dựng **Custom Entity** mang tên `lms_faq` với các Field thiết kế "thửa riêng":
1. `question` (Text): Câu hỏi.
2. `field_answer` (Text format): Câu trả lời chi tiết.
3. **`field_scope` (List String)** - CHÌA KHÓA PHÂN LOẠI:
   - Option `global`: Dành cho trang FAQ Nội Quy / Hệ Thống chung (General).
   - Option `course`: Dành riêng cho giao diện bên trong 1 Khóa học (Course specific).
4. `field_course` (Entity Reference): Móc ngoặc FAQ này vào một mã học phần `lms_course` nhất định (Bắt buộc đi kèm khi scope=`course`).
5. `field_category` (Taxonomy `faq_category`): Phân loại câu hỏi thành các nhóm.
6. `weight` (Integer): Sắp xếp thứ tự (Ví dụ: -10, 0, 5, 20...). Mặc định là hiển thị tăng dần theo Weight.

## 2. Đường Dẫn Tương Tác
- Vì là Custom Entity, Giao diện Admin không nằm ở Node/Content mà sẽ truy cập qua liên kết chuyên biệt: 
  - Thêm mới: `/manage/faq/add`
  - Sửa bài: `/manage/faq/{id}/edit`
  - Danh sách: `/manage/faq`

## 3. Lấy Dữ Liệu (Backend Database Query)
Để không bị lẫn lộn giữa hàng ngàn Data, Query PHP gọi câu hỏi sẽ phụ thuộc vào Trang mà người dùng/Frontend đang đứng:

### Ở trang Chi tiết Khóa Học (Course Page)
Hệ thống sẽ bốc đúng ID khóa học hiện hành `lms_course->id()` và đối chiếu với 2 trường `scope` và `course`:
```php
$faq_ids = \Drupal::entityTypeManager()->getStorage('lms_faq')
  ->getQuery()
  ->condition('field_scope', 'course')
  ->condition('field_course', $course_id)
  ->sort('weight')
  ->execute();
```

### Ở Trang FAQ Hệ Thống Của Nền Tảng (Global Page)
Trang Web FAQ Chung sẽ chỉ quét các câu hỏi mang mác `global` (Mặc kệ field_course có bị Admin click nhầm hay không):
```php
$global_faqs_ids = \Drupal::entityTypeManager()->getStorage('lms_faq')
  ->getQuery()
  ->condition('field_scope', 'global')
  ->sort('weight')
  ->execute();
```

## 4. Drush AI Clean
Nhóm Dữ liệu FAQ đã được khai báo loại thủ công `['lms_faq']` vào vòng quản trị của Dumper `ContentToolsManager.php`. Lệnh `ddev drush everlms:clean -y` sẵn sàng tống khứ mọi dữ liệu tạm trong tích tắc.

## 5. Import tự động (Drush)

`ContentToolsCommands::importFaqs()` ✅ đã implement trong `lms_content_tools`.

- Tự động resolve `field_course` bằng cách lookup `lms_course` theo `course_title` khi `scope=course`
- Nếu scope=`global` thì `field_course` không bắt buộc
- `weight` ưu tiên lấy từ JSON field, fallback về index của vòng lặp

JSON format được AI generate:
```json
{
  "faqs": [
    {
      "question": "Làm sao để lấy chứng chỉ?",
      "answer": "Bạn cần hoàn thành 100% video và bài kiểm tra cuối khóa.",
      "scope": "course",
      "course_title": "React.js Fundamentals",
      "weight": 0
    },
    {
      "question": "Hệ thống có hỗ trợ thanh toán Momo không?",
      "answer": "Có, chúng tôi hỗ trợ Momo, VNPay và thᮣ tín dụng.",
      "scope": "global",
      "weight": 1
    }
  ]
}
```

File đặt tại: `.agent/generated/faqs/faqs.json`

Chạy import:
```bash
ddev drush everlms:import --type=faqs -y
```
