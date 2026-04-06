---
description: Tích hợp nút Switch User trực tiếp trên bảng User Management.
status: Lên kế hoạch (Planning)
---

# Kế hoạch Tích hợp Trình Đổi User (Open Switcher) vào Trang Quản lý User

Mục tiêu: Bổ sung một nút "Impersonate" (nút Đổi vai / Switch User) trực tiếp vào bảng danh sách người dùng (`/workspace/users`) để Admin/Manager có thể nhanh chóng "đội lốt" học viên hoặc giảng viên mà không cần rời khỏi màn hình quản lý hiện tại.

## 1. Phương hướng Triển khai Giao diện (Frontend)
- **Vị trí**: Nút sẽ được đặt vào ngay đầu tiên trong cột **ACTIONS** (Cạnh nút Edit 📝 và View 👁️) ở tệp `lms-admin-users-table.html.twig`.
- **Hiển thị**: 
  - Một Icon `fa-user-secret` (Biểu tượng điệp viên chìm) hoặc một Icon mặt nạ để đại diện cho tính năng "Đóng vai".
  - Hover chuột sẽ đổi màu sang xanh (`#3b82f6`) và có tooltip "Impersonate User" (Đổi quyền).
  - Có cơ chế `{% if user.switch_url %}` để chỉ hiển thị nút này nếu hệ thống đang kích hoạt module `open_switcher`, ngăn lỗi giao diện bị trống hoặc chệch cột.

## 2. Phương hướng Logic Dữ liệu (Backend)
- **Vị trí**: File lõi `AdminController.php` (tại phương thức đa tuyến `userList()`).
- **Thực thi**:
  - Khi Drupal lặp qua danh sách để đóng gói biến `$items`, tôi sẽ gọi một hàm dò Module `\Drupal::moduleHandler()->moduleExists('open_switcher')` để bảo vệ mã nguồn phòng khi tương lai bạn tắt Module này đi.
  - Nếu Module đang mở: Gọi thẳng Route đích `open_switcher.switch_to` (`/admin/switch-user/{uid}`) và nhúng Route này dưới cái tên `switch_url` truyền cho Twig Array.

## 3. Câu hỏi và Đánh giá (Review Required)

Sếp xem giúp tôi những chi tiết này đã đúng ý chưa nhé:
1. **Bạn có đồng ý dùng Icon "Điệp viên" (`<i class="fas fa-user-secret"></i>`) để thể hiện chức năng "Đội lốt" / Switch User cho rạch ròi không, hay có muốn icon nào khác (ví dụ: `fa-people-arrows` / `fa-mask`)?**
2. **Khi nhấn vào Icon này, bản thân file Controller của `open_switcher` sẽ tự đăng nhập bằng Account kia và Redirect bạn về Trang chủ (`<front>`). Bạn có đồng ý với kịch bản này luôn không? (Đây hiện là kịch bản mặc định tôi vừa test ở module mới nhất).**
