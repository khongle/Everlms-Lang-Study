# EverLMS Homepage Redesign Plan

Chúng ta sẽ nâng cấp toàn diện giao diện trang chủ để mang lại cảm giác cao cấp (Premium), hiện đại và sinh động hơn, đồng thời tích hợp đầy đủ các logic dữ liệu theo đúng yêu cầu từ các file Requirement, Architecture, và Mockup.

## 1. Goal Description

- **Bám sát Requirement**: Homepage không chỉ đẹp mà còn phải hiển thị đủ các section chiến lược đã định nghĩa trong `01-requirement` (như My Courses widget, Popular Bundles, Banner Slideshow, Featured Courses).
- **Nâng cấp thẩm mỹ (UI/UX)**: Áp dụng các phong cách thiết kế hiện đại như Glassmorphism, Smooth Gradients, bo góc lớn (border-radius), đổ bóng mượt (soft shadows). Sử dụng Dark Mode làm chủ đạo với các điểm nhấn màu neon (xanh/tím) theo đúng `04-design-mockup`.
- **Tăng tính tương tác (Animations)**: Thêm các hiệu ứng micro-interactions khi hover. Sử dụng Animate On Scroll (AOS) để các section mượt mà hiện ra.

## 2. Alignment with Requirements

Dưới đây là đối chiếu chi tiết các yêu cầu hệ thống sẽ được tích hợp vào Homepage mới:

1. **PC-04 (Banner management)**: Giao diện Hero sẽ được thiết kế có thể dễ dàng chuyển thành dạng Slideshow (Carousel) thay vì chỉ là text tĩnh như hiện tại.
2. **MC-05 & PC-07 (Homepage progress widget)**: Block hiển thị **top 3-5 khoá học đang học** kèm theo progress bar cho user đã đăng nhập. (Hiện tại bị thiếu).
3. **C-12 (Featured course)**: Hiển thị danh sách các khoá học có checkbox `field_featured` = TRUE.
4. **CB-07 (Homepage Feature Bundles)**: Section "Popular Bundles" hiển thị các gói ứng dụng/khoá học bán chéo.
5. **Wireframe (01-homepage.md)**: Đảm bảo có đủ Popular Categories, Core Values / How it works, và Testimonials.

## 3. Proposed Changes

### Controller Layer
`CoursePublicController::homepage()` (`src/Controller/CoursePublicController.php`)

- **Hiệu chỉnh Data fetching**:
  - `categories`: Truyền đủ thuộc tính để render grid dạng 4x2.
  - `my_courses`: Thay vì chỉ query danh sách trắng, query `lms_user_course` và `lms_user_lesson` lấy ra **Top 3-5 khoá học gần nhất** của user (kèm progress %) nếu `logged_in == true`.
  - `bundles`: Query entity `lms_bundle` lấy ra danh sách các bundle nổi bật (Nếu entity `lms_bundle` đã có data).
  - `testimonials`: Đưa vào data mock (do backend chưa có entity này) để làm chuẩn cho UI.

---

### View (Twig) & Styles Layer
Cấu trúc lại giao diện `lms-homepage.html.twig`:

1. **Hero Slideshow Section (PC-04)**: Khung banner hiện đại, nền shadow gradient. Mở rộng structure để sẵn sàng cho tính năng Admin thay banner.
2. **Continue Learning Widget (MC-05, PC-07)**: Nằm ngay dưới Hero (chỉ khi login), dạng scroll ngang hoặc grid nhỏ chứa max 3-5 cards khoá học đang học + progress bar hiện tại.
3. **Featured Courses (C-12)**: Dành riêng một section với layout lưới bắt mắt, nâng cấp nhẹ `lms_course_card.html.twig` cho có hover 3D effect.
4. **Popular Bundles (CB-07)**: Một dải (strip) hoặc grid hiển thị các "Course Bundles" có tag tiết kiệm/sale.
5. **Popular Categories (Wireframe)**: Layout lưới 4x2 hiển thị icon danh mục lớn, số lượng khoá, và hiệu ứng scale khi hover.
6. **Core Values / Why EverLMS (Wireframe)**: Tối giản chữ, làm nổi icon.
7. **Testimonials (Wireframe)**: Carousel với hiệu ứng Glassmorphism (làm mờ phông nền phía sau).
8. **Call-to-Action**: Bloc footer cho guest thúc giục đăng kí.

**SCSS (`_homepage.scss`)**:
- Bổ sung biến cho các dải màu gradient mượt.
- Style chuẩn hóa các class `lms-glass-card`, `lms-progress-bar--animated`.

---

## 4. Verification Plan

1. **Review logic truy vấn**: Đảm bảo Controller không bị chậm trangủ do query thêm `bundles` và `my_courses`. Có limit/range đúng với yêu cầu.
2. **Guest Mode**: Truy cập trang chủ dưới quyền Anonymous sẽ thấy CTA banner và không thấy "My Learning".
3. **User Mode**: Đăng nhập sẽ thấy thay đổi ngay lập tức ở "Continue Learning widget" với % progress đã học.
