# Enhance Clickable Areas on Course Cards

## Goal Description
Hiện tại trên giao diện Course Card (thẻ khóa học) dùng chung cho toàn bộ hệ thống (Wishlist, Catalog, Homepage...), chỉ có mỗi tiêu đề (title) là có thể click được. 
Yêu cầu mới: Khi người dùng đưa chuột (hover) và click vào **hình ảnh thu nhỏ (thumbnail)** hoặc **đoạn mô tả ngắn (short description)**, thẻ sẽ mượt mà điều hướng tới trang phù hợp:
- Nếu người dùng **chưa đăng ký học**: Điều hướng tới trang chi tiết khóa học (`card.url`).
- Nếu người dùng **đã đăng ký học (Enrolled)**: Điều hướng thẳng tới bài giảng đang học dở (`card.continue_url`).

Mục tiêu này sẽ làm tăng trải nghiệm UX, giúp user thao tác nhanh và thuận tiện hơn.

## Proposed Changes

### [MODIFY] [lms-course-card.html.twig](file:///wsl$/Ubuntu/home/phatle/projects/everlms/web/modules/custom/lms_course/templates/lms-course-card.html.twig)
Chỉnh sửa template dùng chung của thẻ khóa học.
1. Định nghĩa logic đường dẫn ở đầu file ngay dưới định nghĩa HTML của card:
   ```twig
   {% set target_link = (card.is_enrolled and card.continue_url) ? card.continue_url : card.url %}
   ```
2. Cập nhật phần hiển thị **hình ảnh**: Bọc thẻ `<img>` (trong nhánh có hình) và thẻ `<i class="fas fa-graduation-cap"></i>` (trong nhánh không có hình bằng cách dùng flexbox cho `<a>`) bằng thẻ `<a href="{{ target_link }}" style="display: block; color: inherit; text-decoration: none;">...</a>`. Quá trình bọc sẽ tinh tế tránh nút Heart Wishlist để nút Heart vẫn dùng bình thường.
3. Cập nhật phần hiển thị **mô tả ngắn**: Bọc đoạn `{{ card.short_description|striptags }}` trong thẻ `<a href="{{ target_link }}" style="color: inherit; text-decoration: none; display: block;">...</a>`.

## Verification Plan
1. Reset cache giao diện (`drush cr`).
2. Vào bất kỳ trang nào có chứa Course Card (như My Wishlist hoặc Course Catalog).
3. Kiểm tra với **khóa học chưa mua**: Di chuột lên hình và mô tả thấy link trỏ về `/course/{course_id}`. Bấm click và xác nhận nó sang trang Detail.
4. Kiểm tra với **khóa học đã mua (Unlocked/Enrolled)**: Di chuột lên hình và mô tả thấy link trỏ về `/course/.../lesson/...`. Bấm vào thấy chuyển thẳng vô bài học thay vì trang chi tiết.
