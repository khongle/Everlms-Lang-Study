# Kế hoạch xây dựng Entities & Fields cho Content Assistant

Tài liệu này định nghĩa cấu trúc dữ liệu cho 5 loại nội dung phục vụ cho việc xây dựng UI linh hoạt và quản trị nội dung CMS trên EverLMS.

## 1. Content: Slideshow (Banner Slider)
Giao diện: Hình banner full-width hoặc box, có thể đè một lớp text (description/heading) lên trên hình kèm theo một nút bấm.
*   **Loại Entity Đề xuất:** Custom Block Type (`slideshow_item`) hoặc Paragraph Type (`slide`).
*   **Fields:**
    *   `title` (String): Tên nội bộ để dễ quản lý trong backend.
    *   `field_image` (Entity Reference ➔ Media): Hình nền banner (ưu tiên dùng định dạng landscape lớn, có điểm nhấn).
    *   `field_heading` (String): Tiêu đề chính hiển thị to trên hình.
    *   `field_subheading` (Text Long): Đoạn mô tả ngắn (subtitle/description) nằm dưới tiêu đề.
    *   `field_link` (Link): Nút Call-to-Action (VD: "Khám phá ngay", URL).
    *   `field_text_position` (List String): Enum `[left, center, right]` — Giúp người dùng chọn vị trí đặt text trên hình, UI sẽ dựa vào trường này để đẩy Flexbox tương ứng.

## 2. Content: Introduction (Nội dung kép)
Giao diện: Một bên là nội dung (Title, Text, CTA), một bên là hình ảnh minh họa hoặc Video.
*   **Loại Entity Đề xuất:** Custom Block Type (`introduction_section`) hoặc Paragraph Type (`text_with_media`).
*   **Fields:**
    *   `title` (String): Tên khối nội dung nội bộ.
    *   `field_heading` (String): Tiêu đề chính (vd: "Học mọi lúc mọi nơi").
    *   `field_body` (Text Formatted): Nội dung chữ chi tiết (hỗ trợ in đậm, list).
    *   `field_media` (Entity Reference ➔ Media Image/Video): Hình ảnh hoặc video đại diện cho ngữ cảnh.
    *   `field_media_position` (List String): Enum `[left, right]` — Nếu chọn `left`, ảnh nằm bên trái, chữ bên phải. UI sẽ đổi thuộc tính `flex-direction: row-reverse` cực kỳ tiện lợi.
    *   `field_cta_link` (Link): Nút điều hướng (Tùy chọn).

## 3. Content: Basic Page (Trang cơ bản)
Giao diện: Bài viết dàn trải từ trên xuống như một Article thông thường. Thường dùng cho các trang tĩnh như Giới thiệu (About Us), Điều khoản (Terms), Privacy Policy.
*   **Loại Entity Đề xuất:** Node Type (`page`).
*   **Fields:**
    *   `title` (String): Tiêu đề trang (hiển thị làm phần tử H1).
    *   `body` (Text Formatted with Summary): Nội dung bài viết chính (tích hợp chuẩn CKEditor 5 để nhúng ảnh, bảng biểu).
    *   `field_featured_image` (Entity Reference ➔ Media): Hình đại diện (Dùng cho thumbnail chia sẻ SEO/Mạng xã hội, hoặc cover trang).
    *   `field_seo_tags` (Metatag): Tùy chỉnh thẻ meta SEO riêng cho từng trang.

## 4. Content: Glossary (Bảng thuật ngữ)
Giao diện: Một danh sách hiển thị dạng Từ điển, List A-Z, hoặc Layout thẻ Card (chia theo chuyên mục). Dùng để tra cứu.
*   **Loại Entity Đề xuất:** Node Type (`glossary`).
*   **Fields:**
    *   `title` (String): Tên từ khóa / Thuật ngữ chuyên ngành.
    *   `body` (Text Formatted): Định nghĩa và giải thích chi tiết thuật ngữ.
    *   `field_glossary_category` (Entity Reference ➔ Taxonomy `glossary_category`): Phân loại (VD: Lập trình, Kinh tế, AI...).
    *   `field_related_terms` (Entity Reference ➔ Node `glossary`): Các thuật ngữ liên quan để giăng lưới link nội bộ (Internal Link).

## 5. Content: Blog vs News
Giao diện: Lưới dạnh Card (Blog Grid) hoặc List.
*   **Sự khác biệt UI/UX:**
    *   `News` (Tin tức): Tập trung vào tính thời sự. Do đó UI sẽ ưu tiên ngày xuất bản (Published Date), nguồn tin (Source), hoặc sự kiện (Event Date).
    *   `Blog`: Tập trung vào kiến thức, chia sẻ cá nhân. Do đó UI sẽ ưu tiên Tác giả (Author Box), Tags, và Thời gian đọc bài (Reading time).
*   **Loại Entity Đề xuất (Blog):** Node Type (`blog`).
*   **Fields riêng cho Blog:**
    *   `title` (String): Tiêu đề bài Blog.
    *   `body` (Text Formatted with Summary): Nội dung bài viết.
    *   `field_featured_image` (Entity Reference ➔ Media): Cover bài Blog (Rất quan trọng cho UI).
    *   `field_blog_category` (Entity Reference ➔ Taxonomy `blog_category`): Chuyên mục bài viết.
    *   `field_tags` (Entity Reference ➔ Taxonomy `tags`): Tags tự do phân loại sâu hơn.
    *   `field_author` (Entity Reference ➔ User): Người đứng tên bài viết (UI sẽ đổ ra Avatar, tên, và thông tin bio ngắn ở cuối bài).
    *   `field_reading_time` (Integer): Ước tính thời gian đọc bài (VD: 5). UI sẽ render là "5 min read". Đem lại trải nghiệm rất xịn.
