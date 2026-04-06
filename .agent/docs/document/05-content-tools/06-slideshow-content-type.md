# EverLMS — Quản lý Slideshow (Content Type)

> Tài liệu hướng dẫn quá trình chuyển đổi và quản lý cấu trúc hiển thị Slideshow ngoài Trang Chủ.

## 1. Bối cảnh
Trước đây, các hình ảnh và nội dung trượt (Slideshow Banners) trên Trang Chủ được cấu hình dưới dạng **Custom Block Content** với bundle là `slideshow_item`. Nhược điểm của dạng này:
- Quản trị viên khó tìm thấy và quản lý do vị trí nằm sâu trong Giao diện cấu hình Block.
- Lệnh dọn dẹp hệ thống `everlms:clean` xóa toàn bộ block sẽ dẫn đến mất nhầm block giao diện khác.

## 2. Mô hình mới (Node Content Type)
Slideshow hiện tại đã được tái cấu trúc thành một **Content Type** riêng biệt tên là `slideshow` (Node). Nó bao gồm 5 trường dữ liệu chuẩn (Fields):
1. `field_slide_image` (Image — `type: image`, managed file)
2. `field_slide_link` (Link)
3. `field_slide_heading` (Text/String)
4. `field_slide_subheading` (Text/String)
5. `field_slide_position` (List String: Left/Center/Right)

Cách thêm Slideshow mới:
- Vào màn hình **Content** -> **Add Content** -> **Slideshow**.
- Điền các thông tin của Slide, đính kèm hình ảnh và chọn trạng thái **Published**.

## 3. Hoạt động Dọn Dẹp (Drush Clean)
Hàm `ContentToolsManager` đã được mở rộng để phân biệt giữa các nhóm `entity_type` và `bundle`. Khi bạn chạy:
```bash
ddev drush everlms:clean -y
```
Hệ thống sẽ lọc chính xác các Node (Bài viết) có bundle là `slideshow` để dọn dẹp mà không chạm vào các bài viết (Blog/Article) hoặc Block giao diện tĩnh của hệ thống.

## 4. Import tự động (Drush)

`ContentToolsCommands::importSlideshows()` ✅ đã implement trong `lms_content_tools`.

JSON format được AI generate:
```json
{
  "slideshows": [
    {
      "title": "Welcome to EverLMS",
      "image_filename": "slide-welcome.png",
      "image_alt": "Welcome to EverLMS training portal",
      "heading": "Khám phá khóa học",
      "subheading": "Hơn 100+ khóa học chất lượng cao",
      "position": "center",
      "cta_link": "internal:/courses"
    }
  ]
}
```

File đặt tại: `.agent/generated/slideshows/slideshows.json`

Chạy import:
```bash
ddev drush everlms:import --type=slideshows -y
```

## 5. Pipeline Ảnh AI (Image Generation Pipeline)

### Tổng quan
Ảnh được sinh bởi AI tool và lưu vào thư mục tạm `.agent/images/slideshows/` trước khi được copy tự động vào Drupal file system.

### Luồng xử lý
```
AI generate ảnh
    ↓
Lưu vào .agent/images/slideshows/{filename}.png
    ↓
ddev drush scr web/copy_generated_images.php
    (copy → web/sites/default/files/generated/)
    ↓
drush everlms:import --type=slideshows
    (đọc image_filename → tạo File Entity → gán vào field_slide_image)
    ↓
Node Slideshow được lưu với ảnh hoàn chỉnh ✅
```

### Cách import script xử lý ảnh
```php
// Trong importSlideshows()
if (!empty($item['image_filename'])) {
    $uri = 'public://generated/' . $item['image_filename'];
    $realpath = \Drupal::service('file_system')->realpath($uri);
    if ($realpath && file_exists($realpath)) {
        $file = \Drupal::service('file.repository')->writeData(
            file_get_contents($realpath),
            $uri,
            \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE
        );
        $fields['field_slide_image'] = [
            'target_id' => $file->id(),
            'alt'       => $item['image_alt'] ?? $item['title'],
        ];
    }
}
```

### Ảnh AI cho Slideshow (SilverCare DE context)
| Filename | Nội dung ảnh |
|----------|-------------|
| `slide-welcome.png` | Professional healthcare portal welcome — modern UI |
| `slide-dementia-course.png` | Nurse and elderly patient, warm empathetic care |
| `slide-certificate.png` | Certificate and graduation, professional achievement |
