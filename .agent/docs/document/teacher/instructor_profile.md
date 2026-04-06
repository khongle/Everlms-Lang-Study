# Hồ Sơ Giảng Viên (Instructor Profile)

Tài liệu này mô tả tính năng hiển thị tên Giảng viên và trang Hồ sơ Giảng viên thay vì sử dụng hệ thống User Profile mặc định của Drupal.

## 1. Yêu cầu Hệ thống
- **Hiển thị Tên:** Hệ thống phải hiển thị "First Name + Last Name" của người dùng thay thế cho "Username" ở tất cả những nơi hiển thị thông tin Giảng viên (như banner chi tiết khoá học, thông tin tác giả, thẻ khoá học).
- **Trang Hồ sơ Cá nhân (Instructor Profile):** Khi học viên click vào tên hoặc ảnh đại diện của giảng viên, hệ thống sẽ điều hướng sang một trang Profile dành riêng cho giảng viên thay vì trang `/user/{uid}` mặc định của Drupal.

## 2. Kế hoạch Triển khai (Implementation Plan)

### Bước 1: Cập nhật format Tên Giảng Viên
- **Controller:** Tại `CoursePublicController::detail()` và `CoursePublicController::buildCourseCards()`, tiến hành load `field_first_name` và `field_last_name` từ user entity.
- Nếu cả 2 field bị trống, fallback về sử dụng `getDisplayName()` (username mặc định).
- Nối chuỗi tạo thành `$instructor_full_name` để gán vào biến truyền xuống Twig.

### Bước 2: Tạo Trang Instructor Profile (`/instructor/{user}`)
- **Routing:** Thêm route `lms_course.instructor_profile` vào `lms_course.routing.yml` trỏ tới `/instructor/{user}`.
- **Controller:** Thêm method `instructorProfile(UserInterface $user)` vào `CoursePublicController`. Method này sẽ:
  - Load thông tin user (Avatar, Bio/About me, Social links...).
  - Query tất cả các khoá học (`lms_course`) mà user này là author (`uid`).
  - Đếm tổng số học viên đăng ký các khoá học của giảng viên này.
  - Tính điểm đánh giá (rating) trung bình (nếu có module reviews).
- **Template:** Thêm định nghĩa theme `lms_instructor_profile` vào `hook_theme` trong `lms_course.module`.
- **Twig:** Tạo file `lms-instructor-profile.html.twig` kế thừa từ layout hiện có của frontend để render một giao diện hiển thị thông tin và danh sách khoá học (Course Catalog style).

### Bước 3: Cập nhật URL liên kết
- Truyền biến `$instructor_url` (được sinh từ route `lms_course.instructor_profile`) xuống các template như `lms-course-detail.html.twig` và `lms-course-card.html.twig`.
- Cập nhật thẻ `<a>` ngoài frontend để bọc tên và avatar giảng viên, trỏ tới URL mới này.

---
**Gợi ý giao diện trang Instructor Profile:**
- Phía trên / Cột trái: Avatar lớn, chức danh, Bio/trích dẫn, link mạng xã hội (LinkedIn, X, YouTube).
- Thống kê: Số khoá học, tổng số học viên.
- Phía dưới / Cột phải: Grid danh sách các khoá học của Giảng viên.
