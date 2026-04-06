# Kế hoạch Cải thiện: Trang My Courses (Lớp học của tôi)

## 1. Vấn đề hiện tại
Giao diện trang Lớp học của tôi đang dùng nền HTML cũ, thẻ khoá học (Course Card) lỗi style (không đồng bộ với thiết kế Card sang xịn mịn ở trang Dashboard), và thiếu bộ phận Lọc (Filter) trạng thái khoá học.

## 2. Kế hoạch Cập nhật
### 2.1 Cải thiện giao diện Thẻ Khoá Học (UI)
- Sẽ xoá bỏ các class Bootstrap cũ (`row`, `col-lg-4`) đang làm vỡ layout để thay bằng class `grid-courses` chuẩn của LMS Theme.
- Tái sử dụng (Reuse) nguyên mẫu mã HTML của Thẻ Khoá học (Glass Card) từ trang Dashboard sang trang My Courses.
- Sửa lại Controller (`UserPortalController::myCourses()`) để sinh ra các biến `thumbnail_url` và `category` thật chuẩn xác, truyền vào Twig.

### 2.2 Thêm chức năng Lọc (Filter) bằng Tabs
- Thêm một thanh Tabs điều hướng (Navigation Tabs) ngay phía trên danh sách khoá học, với 3 tuỳ chọn:
  - **All (Tất cả)**
  - **In Progress (Đang học)**
  - **Completed (Đã hoàn thành)**
- Sử dụng Javascript thuần (Vanilla JS) nhúng trực tiếp vào Twig để thực hiện việc ẩn/hiện khoá học tức thì khi người dùng bấm vào Tab tương ứng bằng cách lấy thuộc tính `data-status`. Mượt mà và không load lại trang.

## 3. Các file thay đổi:
- `[MODIFY] web/modules/custom/lms_progress/src/Controller/UserPortalController.php` (chuẩn hoá data truyền đi)
- `[MODIFY] web/modules/custom/lms_progress/templates/lms-my-courses.html.twig` (Dựng lại giao diện và JS code logic)
