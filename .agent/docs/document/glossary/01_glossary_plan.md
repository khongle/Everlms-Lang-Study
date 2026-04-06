# Glossary Entity & UI Implementation Plan

## 1. Goal Description
Xây dựng hệ thống Bảng thuật ngữ (Glossary) giúp học viên dễ dàng tra cứu từ khóa chuyên ngành, hỗ trợ mạnh mẽ cho SEO thông qua mạng lưới Internal Linking.

## 2. Backend Data Architecture
- **Taxonomy Vocabulary:** `glossary_category` (VD: AI, Frontend, Backend, DevOps).
- **Node Type:** `glossary` (Content type chính).
- **Core Fields:**
  - `title`: Tên thuật ngữ.
  - `body`: Phần giải nghĩa chi tiết (Text Formatted).
  - `field_glossary_category`: Entity Reference liên kết tới taxonomy `glossary_category`.
  - `field_related_terms`: Entity Reference tới các node `glossary` khác để giăng lưới link nội bộ.

## 3. Automation Scripts
- **Script:** `web/import_glossary.php`
- **Data source:** `.agent/docs/document/glossary/02_glossary_data.json` (Sẽ được tạo sau).
- **Nhiệm vụ:** Tự động khởi tạo content type, fields, taxonomy và nạp dữ liệu mẫu để quá trình phát triển nhanh gọn.

## 4. Frontend UI Architecture
- **Đường dẫn mục tiêu:** `/glossary`
- **Công nghệ (Đề xuất):** Ưu tiên sử dụng Custom Controller + Twig Template (tương tự như cách đã làm cho Homepage) để tự do thiết kế UI mang chuẩn Premium.
- **Tính năng UI:**
  - Header với Search Box lọc từ vựng (hiệu ứng Glassmorphism).
  - Dải thanh lọc A-Z (Alphabet filter) để tra thuật ngữ theo chữ cái.
  - Danh sách Grid/Card liệt kê các terms, gom nhóm theo category.
  
## 5. Khuyến nghị Tương lai
- Bổ sung Auto-linking (Tooltip): Khi soạn bài học (Lesson) có chứa từ khóa có trong Glossary, chữ đó tự động được bôi sáng và hiển thị giải thích ngắn gọn khi hover chuột.
