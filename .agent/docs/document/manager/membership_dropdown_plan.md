# Kế hoạch triển khai: Dropdown "Membership" và "Certificate" Sidebar

## Mục tiêu
Dự án tập trung vào việc quy hoạch lại thanh menu doc (sidebar) của Manager Workspace để gọn gàng hơn, bằng cách sử dụng các accordion dropdown thay vì menu lộn xộn:
1. **Membership Dropdown:** Gom "Pricing" và "User membership" vào một nhóm, đồng thời tạo trang `/workspace/user-memberships` hiển thị danh sách thành viên.
2. **Certificate Dropdown:** Gom "Certificate Management" và "Certificate Templates" vào nhóm "Certificate", mặc định đóng giống như nhóm Membership.

---

## Phần 1: Các thay đổi cho "Certificate Dropdown"

### 1. `lms_course` Module - Cập nhật giao diện Sidebar
#### [SỬA ĐỔI] `lms_course/templates/lms-admin-sidebar.html.twig`
- Xóa 2 menu link riêng rẽ là `Certificate Management` và `Certificate Templates`.
- Viết lại bằng một block HTML Dropdown (tương tự như Membership Dropdown). 
- Dropdown này chứa 2 nhánh con:
  - **Certificate Management:** Trỏ về url `path('lms_certificate.admin_dashboard')`
  - **Certificate Templates:** Trỏ về url `path('lms_certificate.admin_templates')`
- Tái sử dụng CSS class `lms-admin__subnav-item`.
- Mặc định đóng (display: none và icon hướng xuống).

---

## Phần 2: Các thay đổi cho "Membership Dropdown" (Đã hoàn thành)

### 1. `lms_course` Module - Cập nhật giao diện Sidebar
#### [SỬA ĐỔI] `lms_course/templates/lms-admin-sidebar.html.twig`
- Đã thay thế menu link "Pricing" hiện tại bằng menu thả xuống "Membership".
- Mặc định đóng, sử dụng `lms-admin__subnav-item`.

### 2. `lms_membership` Module - Khởi tạo Router & Controller
#### [SỬA ĐỔI] `lms_membership/lms_membership.routing.yml`
- Tạo thêm route `lms_membership.admin_user_memberships` (Hoàn thành).

#### [SỬA ĐỔI] `lms_membership/src/Controller/MembershipAdminController.php`
- Thêm hàm `userMembershipsList()` (Hoàn thành).

#### [SỬA ĐỔI] `lms_membership/lms_membership.module`
- Đăng kí theme hook `lms_admin_user_memberships` (Hoàn thành).

#### [TẠO MỚI] `lms_membership/templates/lms-admin-user-memberships.html.twig`
- Tạo template giao diện hiển thị danh sách (Hoàn thành).

---

## Quy trình Kiểm thử (Testing)
1. Đăng nhập vào trang EverLMS bằng quyền Admin (Manager).
2. Kiểm tra xem trên sidebar có 2 group menu "Certificate" và "Membership", cả 2 đều mặc định đóng lại bằng icon chevron-down.
3. Click vào group "Certificate" -> dropdown mở ra, nhấp thử thẻ "Certificate Management" và "Certificate Templates" để xem UI active `lms-admin__subnav-item--active`.
4. Khi đổi code, chạy `drush cr` để dọn cache.
