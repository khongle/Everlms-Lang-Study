# EverLMS — Quản lý Khối Giới Thiệu (Introduction Content Type)

> Tài liệu hướng dẫn quá trình khởi tạo và cấu trúc dữ liệu của các mảng Giới thiệu, tính năng đặc biệt (Features Header), Hero Layout.

## 1. Bối cảnh hệ thống
Các phần giới thiệu ngoài Trang Chủ hoặc Trang Chi Tiết thường chia ra 2 nhịp "Chữ môt bên - Hình một bên" để trình bày bắt mắt. Mặc dù Frontend có thể xử lý việc này (Text HTML tĩnh), nhưng việc đưa chúng vào thành một **Content Type** riêng trên hệ thống cho phép đội ngũ Content dễ dàng tinh chỉnh (A/B testing) chữ, màu sắc, lời kêu gọi nút bấm (CTA) mà không phải gõ code.

## 2. Kiến trúc Dữ liệu (Introduction Schema)
Hệ thống cấp phép hoạt động cho Content Type là `introduction` (Node) gồm 6 loại Field khác nhau đóng vai trò định tuyến Frontend:
1. `title` (Node core): Tiêu đề Header chính (Ví dụ: "Đồng hành vươn tầm chuyên gia").
2. `field_subheading`: Phụ đề ngược thường nằm ở mép trên (Ví dụ: "LỘ TRÌNH RÕ RÀNG").
3. `body` (Node core): Lời giải thích tổng quát nội dung đoạn văn.
4. `field_intro_image`: Hình ảnh minh họa chất lượng cao đi kèm. (`type: image`, managed file, Bắt buộc dùng tính năng Alt text)
5. Tín hiệu xử lý CSS Layout (`field_image_position`): `left` hoặc `right`. Hỗ trợ đổi cột nghịch chiều siêu nhanh.
6. Tín hiệu xử lý Màu nền (`field_background_color`): Trắng, Xám nhạt, Xanh tối, Primary Color. Tránh "tàng hình" khi các khối Introduction đặt chồng lên nhau.
7. Hành động Mời (Call to Action `field_call_to_action`): Khởi tạo một nút UI button nằm ngay dưới đoạn văn (Title và Link tới khóa học đăng ký).

## 3. Hoạt động với ContentTools (Clean)
Nhóm ưu tiên `introductions` được xếp trên đỉnh của hàm `ContentToolsManager.php`. Lệnh:
```bash
ddev drush everlms:clean -y
```
sẽ "rê" chính xác các Node loại này mang nhãn bundle `introduction` làm mồi nhắm để xóa sạch khỏi trần đời mà không ảnh hưởng tới Content khác.

## 4. Import tự động (Drush)

`ContentToolsCommands::importIntroductions()` ✅ đã implement trong `lms_content_tools`.

JSON format được AI generate:
```json
{
  "introductions": [
    {
      "title": "Về chúng tôi",
      "subheading": "Nâng tầm tri thức",
      "body": "EverLMS cung cấp giải pháp chuyển đổi số toàn diện...",
      "image_filename": "intro-about-us.png",
      "image_alt": "SilverCare DE nursing team in a modern facility",
      "image_position": "right",
      "background_color": "#f8f9fa",
      "cta_text": "Liên hệ",
      "cta_link": "internal:/contact"
    }
  ]
}
```

File đặt tại: `.agent/generated/introductions/introductions.json`

Chạy import:
```bash
ddev drush everlms:import --type=introductions -y
```

## 5. Pipeline Ảnh AI (Image Generation Pipeline)

### Luồng xử lý
```
AI generate ảnh (tỉ lệ 1:1 hoặc 4:3 — phù hợp layout 2 cột)
    ↓
Lưu vào .agent/images/introductions/{filename}.png
    ↓
ddev drush scr web/copy_generated_images.php
    (copy → web/sites/default/files/generated/)
    ↓
drush everlms:import --type=introductions
    (đọc image_filename → tạo File Entity → gán vào field_intro_image)
    ↓
Introduction Node được lưu với ảnh hoàn chỉnh ✅
```

### Cách import script xử lý ảnh
```php
// Trong importIntroductions()
if (!empty($item['image_filename'])) {
    $uri = 'public://generated/' . $item['image_filename'];
    $realpath = \Drupal::service('file_system')->realpath($uri);
    if ($realpath && file_exists($realpath)) {
        $file = \Drupal::service('file.repository')->writeData(
            file_get_contents($realpath),
            $uri,
            \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE
        );
        $fields['field_intro_image'] = [
            'target_id' => $file->id(),
            'alt'       => $item['image_alt'] ?? $item['title'],
        ];
    }
}
```

### Ảnh AI cho Introduction (SilverCare DE context)
| Filename | Nội dung ảnh |
|----------|-------------|
| `intro-about-us.png` | Professional German healthcare team smiling, modern facility |
| `intro-why-us.png` | International nurses with certificates, achievement theme |
