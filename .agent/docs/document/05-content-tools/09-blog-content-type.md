# EverLMS — Quản lý Bài viết (Blog Content Type)

> Tài liệu hướng dẫn quá trình khởi tạo thuật cấu trúc dữ liệu của các Bài viết Tin tức (News/Blog/Articles) được chuẩn hóa trên nhân Drupal Core thay vì dùng Custom Entity.

## 1. Bối cảnh
Trước đây, những bài viết trên hệ thống (Articles) được quản lý thông qua Entity tuỳ biến `lms_article` của module `lms_blog`. Tuy nhiên, để tối ưu khả năng SEO (URL Alias, Meta thẻ), cũng như giúp quản trị viên (Admin) quen với thao tác kéo thả của Drupal Core Form, nhóm đã quyết định cấu hình lại Blog thành một **Standard Node Content Type**.

## 2. Kiến trúc Data (Schema)
Content Type sinh ra có tên hệ thống (Machine name) là `blog`. Đi kèm Vocabulary `blog_tags` để gắn nhãn phân loại.
Các Filed tính từ trên xuống dưới trên giao diện nhập liệu:
1. `title` (Node core): Tiêu đề Bài đăng. Tự động sinh `URL Alias` dạng `/blog/[title]` nhờ plugin Pathauto tích hợp ngầm.
2. `field_thumb`: Hình ảnh thu nhỏ (Thumb) đại diện cho Bài viết ở các danh sách. (`type: image`, managed file)
3. `body` (Node core): Nội dung hoàn chỉnh của bài viết bằng HTML format, bao gồm cả Summary.
4. `field_blog_tags`: Kết nối tới Danh mục phân loại bài viết. Cấu hình Autocomplete tự động tạo Tag mới nếu Admin gõ chữ chưa tồn tại.

## 3. Hoạt động dọn dẹp hệ thống (AI Core Cleanup)
Chu trình dọn dẹp nội dung mẫu `ddev drush everlms:clean` bằng Service `ContentToolsManager.php` đã được can thiệp.
- Nhóm `articles` trước đây truy quét bằng `lms_article` đã bị gán lại thành `node:blog`.
- Nhờ vậy, mọi hoạt động chạy test của Content Tools hay Drush đều sẽ chỉ xóa sạch các Bài đăng Node Blog rác mà không gây lỗi Cache.

## 4. Import tự động (Drush)

`ContentToolsCommands::importBlogs()` ✅ đã implement trong `lms_content_tools`.

- Tự động tạo Tag mới trong vocabulary `blog_tags` nếu chưa tồn tại
- Hỗ trợ cả `content` key (AI output) lẫn `body` key
- Hỗ trợ `image_filename` để gắn ảnh thumbnail tự động

JSON format được AI generate:
```json
{
  "blogs": [
    {
      "title": "Xu hướng E-learning 2026",
      "image_filename": "blog-elearning-trends.png",
      "image_alt": "E-learning trends 2026 overview",
      "content": "<p>Trong năm 2026, AI Agent sẽ thống trị...</p>",
      "summary": "Tóm tắt ngắn về e-learning 2026",
      "tags": ["E-learning", "AI", "Xu hướng"]
    }
  ]
}
```

File đặt tại: `.agent/generated/blogs/blogs.json`

Chạy import:
```bash
ddev drush everlms:import --type=blogs -y
```

## 5. Pipeline Ảnh AI (Image Generation Pipeline)

### Luồng xử lý
```
AI generate ảnh thumbnail (16:9 hoặc 4:3)
    ↓
Lưu vào .agent/images/blogs/{filename}.png
    ↓
ddev drush scr web/copy_generated_images.php
    (copy → web/sites/default/files/generated/)
    ↓
drush everlms:import --type=blogs
    (đọc image_filename → tạo File Entity → gán vào field_thumb)
    ↓
Blog Node được lưu với thumbnail hoàn chỉnh ✅
```

### Cách import script xử lý ảnh
```php
// Trong importBlogs()
if (!empty($item['image_filename'])) {
    $uri = 'public://generated/' . $item['image_filename'];
    $realpath = \Drupal::service('file_system')->realpath($uri);
    if ($realpath && file_exists($realpath)) {
        $file = \Drupal::service('file.repository')->writeData(
            file_get_contents($realpath),
            $uri,
            \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE
        );
        $fields['field_thumb'] = [
            'target_id' => $file->id(),
            'alt'       => $item['image_alt'] ?? $item['title'],
        ];
    }
}
```

### Ảnh AI cho Blog (SilverCare DE context)
| Filename | Nội dung ảnh |
|----------|-------------|
| `blog-german-care.png` | German healthcare facility, professional nursing staff |
| `blog-dementia-guide.png` | Compassionate elderly care, memory care unit |
| `blog-international-nurses.png` | Diverse international nursing team in Germany |
