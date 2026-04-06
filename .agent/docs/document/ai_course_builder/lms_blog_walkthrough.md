# Tích hợp LMS Blog - Tài liệu & Hướng dẫn sử dụng

**Module**: `lms_blog`
**Trạng thái**: Hoàn thành (Giai đoạn 2)
**Phụ thuộc**: `lms_base`, `default_content`

## 1. Tổng quan Kiến trúc Kỹ thuật
Module `lms_blog` giới thiệu một Custom Content Entity hoàn toàn độc lập và mạnh mẽ (`lms_article`) để quản lý Tin tức và Bài viết dạng Blog trong EverLMS, qua đó loại bỏ các hạn chế của Node mặc định trong Drupal.

### Các thành phần Cốt lõi:
- **`LmsArticle.php`**: Class cấu trúc dữ liệu Entity. Tích hợp sẵn các field thiết yếu dạng chuỗi (`title`), vùng văn bản (`content`), tệp đính kèm (`thumbnail`), và các field chuyên dụng cho SEO (`seo_title`, `seo_description`).
- **`LmsArticleAccessControlHandler.php`**: Hệ thống phân quyền siêu chi tiết - Cho phép Giảng viên/Biên tập viên có quyền tạo và sửa riêng bài viết của họ, trong khi đó chỉ có Quản lý/Admin mới có quyền xuất bản (Publish) hoặc xóa toàn cục.
- **Routing & Menu**: Tích hợp chặt chẽ vào `/manage/blog` cho phép quản trị giao diện mượt mà và trực quan ngay bên cạnh khu vực quản lý khóa học.

### Chuẩn hóa Kiến trúc:
- **Tương thích JSON:API**: Bởi vì được build chuẩn trên `ContentEntityBase` mới nhất, nên ngay khi module JSON:API của Drupal được bật, các endpoints ở `/jsonapi/lms_article/lms_article` sẽ hoàn toàn hoạt động trơn tru mà không cần code thêm bất cứ gì, đáp ứng cực tốt việc phát triển Mobile App.

---

## 2. Hướng dẫn dành cho Quản trị viên (Cấu hình Phân quyền)

1. Đi đến **People > Permissions** (Tiện ích > Phân quyền).
2. Cuộn màn hình để tìm section "LMS Blog".
3. Check chọn **"Administer LMS Blog"** dành riêng cho role `administrator`.
4. Check chọn quyền **"Create Blog Articles"** và **"Edit own Blog Articles"** cho các role `instructor` và `content_editor`.
5. Bấm Lưu thiết lập ở cuối trang.

---

## 3. Hướng dẫn Biên tập viên (Tạo bài viết Blog)

1. Truy cập **Manage > Blog Articles** (`/manage/blog`).
2. Bấm nút **"Add Article"**.
3. Điền Tiêu đề, tải lên Ảnh đại diện Thumbnail và viết Nội dung linh hoạt.
4. Tối ưu bài viết bằng cách chú ý điền vào **"SEO Title"** và **"SEO Description"**.
5. Để xuất bản ngay, giữ check phần "Published". Nếu muốn lưu nháp để hôm sau sửa tiếp, hãy tick chọn bỏ "Published".
6. Click **Lưu**.

*Lưu ý: Giao diện Form bài viết này sẽ được tích hợp phép thuật của "AI Writer" tự động viết bài ở cấu trúc Giai đoạn 4!*
