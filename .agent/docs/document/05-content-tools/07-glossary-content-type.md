# EverLMS — Quản lý Bảng Chú Giải Thuật Ngữ (Glossary)

> Tài liệu hướng dẫn quá trình khởi tạo và cấu trúc dữ liệu của Thuật ngữ/Từ vựng chuyên ngành (Glossary Content Type).

## 1. Bối cảnh
Chức năng Glossary đóng vai trò then chốt trong một hệ thống LMS (Đào tạo trực tuyến), giúp học viên nhanh chóng tra cứu khái niệm hoặc các từ lóng liên quan đến nội dung khóa học.

## 2. Mô hình hệ thống (Node Content Type)
Glossary được thiết kế thành một **Content Type** riêng biệt tên là `glossary` (Node). Nó bao gồm các trường dữ liệu tiêu chuẩn (Fields) như sau:
1. `title` (Tiêu đề mặc định): Chứa từ vựng hoặc thuật ngữ (Ví dụ: "ROI").
2. `body` (Nội dung mặc định): Chứa định nghĩa chi tiết, diễn giải.
3. `field_synonyms` (Text 255): Danh sách các từ đồng nghĩa hoặc các từ viết tắt chuyên ngành.
4. `field_course` (Entity Reference): Đóng vai trò con trỏ kết nối thuật ngữ này sang **1 Khóa Học (LMS Course)** bất kỳ có trong hệ thống (`target_type: lms_course`). Nó dùng để phân chia kho từ vựng theo môn học.

### Cách thêm Glossary mới:
- Truy cập vào giao diện web admin thông qua menu: **Content** -> **Add Content** -> **Glossary**.
- Điền Tên thuật ngữ, nội dung mô tả, gán từ đồng nghĩa và tùy chọn chọn một khóa học bằng tính năng Autocomplete.
- Đặt trạng thái bài đăng thành **Published** rồi tiến hành lưu (Save).

## 3. Hoạt động Dọn Dẹp (Drush Clean)
Hệ thống AI Content Tools đã khai báo `glossaries` làm một trong những nhóm `entity_type` quan trọng được quản lý và ưu tiên. 
Khi cần dọn dẹp kho từ điển rác (sinh ra trong quá trình thiết lập test) bằng lệnh:
```bash
ddev drush everlms:clean -y
```
Hệ thống sẽ lọc chính xác các Node mang nhãn bundle `glossary` (`node:glossary`) để dọn dẹp xóa bỏ mà không chạm vào hay ảnh hưởng tới cấu hình Cập nhật hay Slide show ngoài trang chủ.

## 4. Import tự động (Drush)

`ContentToolsCommands::importGlossaries()` ✅ đã implement trong `lms_content_tools`.

- Hỗ trợ cả 2 key: `term` (AI output) hoặc `title`
- Tự động tìm kiếm và gán `field_course` nếu có `course_title` trong JSON

JSON format được AI generate:
```json
{
  "glossaries": [
    {
      "term": "Asynchronous Learning",
      "definition": "Học tập không đồng bộ...",
      "synonyms": "Học qua video, Tự học",
      "course_title": "React.js Fundamentals"
    }
  ]
}
```

File đặt tại: `.agent/generated/glossaries/glossaries.json`

Chạy import:
```bash
ddev drush everlms:import --type=glossaries -y
```
