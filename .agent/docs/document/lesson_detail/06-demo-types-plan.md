# Kế Hoạch Triển Khai 4 Loại Bài Học Đặc Biệt (Demo Types Plan)

Để phục vụ cho kỳ Demo sắp tới, hệ thống cần hỗ trợ hiển thị 4 loại nội dung trực quan ngay bên trong Player của giao diện Học tập (thay vì bắt học sinh tải về). Dưới đây là phương án kỹ thuật khả thi, nhanh gọn và hiệu quả nhất cho từng loại:

## 1. SCORM (Gói bài giảng E-Learning chuẩn)
SCORM bản chất là một file `.zip` chứa các file HTML, CSS, JS và một file chạy chính (thường là `index.html` hoặc `story.html`).
- **Backend Setup:**
  - `Lesson.php` đã có sẵn trường `field_scorm_package` (kiểu file zip).
  - Thuật toán (Hook): Khi giáo viên bấm Lưu (Save) bài học, ta viết một hook `hook_ENTITY_insert/update` để tự động giải nén (unzip) file SCORM này vào thư mục công khai `public://scorm_extracted/{lesson_id}`.
- **Frontend Display (`lms-lesson-view.html.twig`):**
  - Nếu `field_type == 'scorm'`, ta chèn một thẻ `<iframe>` trỏ đường dẫn src tới file `index.html` vừa được giải nén.
  - Kích thước Iframe sẽ set width `100%` và height khoảng `600-800px` (hoặc aspect ratio 16:9).

## 2. H5P (Nội dung tương tác)
File H5P rất phức tạp để tự viết engine đọc. Có 2 phương án tùy thuộc vào mức độ "gấp" của đợt Demo:
- **Phương án A (Siêu tốc cho Demo):**
  - Bổ sung 1 trường `field_h5p_embed` (Text) vào `Lesson.php`. 
  - Giáo viên lên trang `h5p.org` (hoặc server nội bộ khác), tạo nội dung rồi copy mã `<iframe src="...">` dán vào trường này. 
  - Hệ thống chỉ việc nhả thẳng iframe đó ra màn hình. Nhanh, gọn, 100% chạy mượt.
- **Phương án B (Chuẩn hệ thống Core lâu dài):**
  - Download module Contrib của cộng đồng: `h5p` module. Mở chức năng upload file `.h5p`, hệ thống Drupal sẽ dùng thư viện của module này để nhúng nó ra giao diện. Giao diện cũng sẽ hiển thị y hệt như dạng nhúng iframe.

## 3. PDF (Tài liệu văn bản)
Cực kỳ dễ và Native trên mọi trình duyệt hiện đại (Chrome, Edge, Safari đều có sẵn PDF Reader).
- **Backend Setup:**
  - Thêm `'pdf' => 'PDF Document'` vào danh sách dropdown của `field_type`.
  - Không cần tạo trường mới. Tái sử dụng `field_attachments`. Yêu cầu giáo viên upload đúng 1 file PDF lên đó.
- **Frontend Display:**
  - Lấy đường dẫn URL của file đính kèm đó, ném vào Iframe:
  - `<iframe src="{{ file_url }}#view=FitH&toolbar=0" style="width: 100%; height: 800px; border: none;"></iframe>`
  - Tham số `#toolbar=0` sẽ ẩn thanh công cụ tải về của trình duyệt nếu muốn học sinh tập trung xem.

## 4. PPTX (PowerPoint Slide)
Trình duyệt **KHÔNG THỂ** đọc trực tiếp file PPTX, DOCX hay XLSX. Nếu nhét file PPTX vào iframe, trình duyệt mặc định sẽ bắt "Download" thay vì "View". Do đó ta phải dùng "bên thứ 3" để đọc nó.
- **Giải pháp (Microsoft Office Web Viewer):**
  - Dùng thẻ iframe thần thánh của Microsoft: `<iframe src="https://view.officeapps.live.com/op/embed.aspx?src={URL_QUỐC_TẾ_CỦA_FILE_PPTX}"></iframe>`
- **LƯU Ý CỰC KỲ QUAN TRỌNG CHO DEMO:** 
  - Vì web của anh hiện tại là `everlms.ddev.site` (Web nội bộ Localhost chạy trên máy anh), nên Microsoft Server nằm tít ở Mỹ sẽ **không chui vào máy anh** để gắp file PPTX ra đọc được. Nó sẽ báo lỗi "File not found" trên iframe.
  - Tức là nếu muốn Demo thành công PPTX Web Viewer, anh phải dùng **Ngrok** để đưa website ra Internet, hoặc đẩy source này lên 1 con VPS / Hosting chạy mượt mà có IP Pubic.
- **Phương án B (Chữa cháy nếu chỉ Demo Local):**
  - Anh cứ tạo loại bài học `'pptx'`, nhưng lúc upload tài liệu lên anh dùng tính năng **Save As -> PDF** của phần mềm PowerPoint để tải file PDF lên mạo danh, và dùng Trình xem PDF gánh thay. Khán giả nhìn vào vẫn thấy từng trang slide to bành trướng, lật lật rất đẹp.

---

**🔥 Tóm tắt cho anh quyết định:**
Nếu anh "Chốt", em sẽ:
1. Thêm type `pdf`, `pptx` vào `Lesson.php`. Thêm trường `field_h5p_embed` để nhúng link cho lẹ.
2. Viết Code Unzip file SCORM cho `Lesson`.
3. Sửa Twig để render Iframe tương ứng với 4 loại này.
 *(Các trường mới cần update database bằng drush entities-update)*
