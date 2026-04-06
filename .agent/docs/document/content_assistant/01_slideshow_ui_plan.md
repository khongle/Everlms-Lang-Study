# Kế hoạch Tích hợp UI Slideshow Lên Homepage

Tài liệu này trình bày kế hoạch kỹ thuật để truy xuất 5 Block Content (Slideshow) từ Database và render lên trang chủ của EverLMS dưới dạng một chức năng Swiper/Bootstrap Carousel tương tác lộng lẫy.

## Vấn đề Thiết Kế (Cần chốt)

Trong giao diện `lms-homepage.html.twig` hiện tại, phần Hero tĩnh đang giữ 2 khối chức năng quan trọng:
1. **Thanh tìm kiếm (Glassmorphism Search)**
2. **Thống kê (Animated Stats)**

**Hướng giải quyết ưu việt nhất:** 
Chuyển 2 khối này thành một lớp **Floating Layer** (nổi bồng bềnh). Lớp này sẽ được đặt cố định đè lên Slider nền chạy bên dưới. Khi người dùng bấm Next/Prev chuyển slide, hình ảnh và câu slogan Marketing sẽ trượt qua, nhưng thanh tìm kiếm và con số thống kê vẫn đứng yên lấp lánh ở nửa dưới màn hình. Đây là Pattern Web Development phổ biến nhất cho UI CMS hiện đại.

---

## Các File Cần Thay Đổi

### 1. `web/modules/custom/lms_course/src/Controller/CoursePublicController.php`
- Cập nhật hàm `homepage()` để query danh sách Entity `block_content` thuộc loại `slideshow_item` (lọc status = 1).
- Trích xuất thông tin: `field_slide_image` (biến đổi Object thành URL tuyệt đối bằng `file_url_generator`), `field_slide_heading`, `field_slide_subheading` (HTML rendered), `field_slide_link` và `field_slide_position`.
- Parse mảng này thành variable `#slideshows` truyền vào mảng build trả về cho theme `lms_homepage`.

### 2. `web/modules/custom/lms_course/templates/lms-homepage.html.twig`
- **Xóa** khối Hero tĩnh cũ (`<section class="lms-hero ...">`).
- **Thêm** cấu trúc **Bootstrap 5 Carousel** (`#homeSlideshow.carousel.slide`).
- Sử dụng Twig loop `{% for slide in slideshows %}` để gọi ra từng slide trong thẻ `<div class="carousel-item">`.
- In thẻ CSS riêng đại diện cho từng vị trí căn chỉnh từ biến `slide.position` (VD: `.text-start`, `.text-center`, `.text-end`).
- Đóng hộp **Search Bar** và khối **Stats** bằng `<div class="lms-hero__floating-ui position-absolute w-100 bottom-0 z-3">` đè ngang Carousel.

### 3. `web/themes/custom/lms_theme/scss/pages/_homepage.scss`
- Viết SCSS tùy chỉnh mạnh tay cho CSS Selector `.lms-hero-carousel`:
  - Reset Base Height: Set `height: 85vh` hoặc `min-height: 700px` phục vụ cover màn lớn.
  - Xử lý mảng màu (Gradient Overlay): Tạo bộ nhúng `linear-gradient` phủ đen nhẹ từ rìa ngoài vào trong để bảo đảm độ tương phản (contrast) giữa Text và Hình khối.
  - Design lại hoàn toàn cặp nút **Previous / Next** của Bootstrap Control (dùng mũi tên FontAwesome mỏng, background glassmorphism mờ viền tròn).
  - Áp Flexbox cho text Marketing và Animation `Fade-Up` mỗi khi chuyển cảnh mới.

## Tiêu chí Nghiệm thu (Review)
1. Homepage hiển thị mượt mà Slider và tự động cuộn (Auto-play 5s/slide).
2. Hình Background độ nét cao bao trọn màn hình, không bị vỡ / móp méo tỉ lệ.
3. Box tìm kiếm nhập Text bình thường, bấm search vẫn văng ra đúng trang `/courses` kèm từ khóa.
4. Chữ (Typography) hiển thị rõ ràng, đậm chất LMS.
5. Resize trên điện thoại bố cục không bị bể, chuyển thành Full Width dọc chiều xuống đúng Responsive.
