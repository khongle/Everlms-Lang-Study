# Kế hoạch triển khai: Form Mode & Add/Edit Form cho User Membership

## Mục đích
Hiện tại, khi click vào nút "Edit" hoặc "Add Membership" trong danh sách giao diện Manager Workspace, hệ thống đang chuyển hướng về route mặc định của Drupal Backend (`/manage/user-memberships/...`). Điều này làm vỡ trải nghiệm UI/UX tối màu của Workspace. Mục tiêu của kế hoạch này là tích hợp các form Add/Edit của User Membership vào thẳng layout của Manager Workspace (giống như cách đã làm với form Pricing).

## Chi tiết kế hoạch

### 1. Cập nhật Entity Annotation (Khai báo Form handler mới)
#### [SỬA ĐỔI] `lms_membership/src/Entity/UserMembership.php`
- Thêm một form operation mới tên là `manager` vào mảng `handlers['form']`.
- Ví dụ: `'manager' => 'Drupal\Core\Entity\ContentEntityForm',`
- Thao tác này cho phép gọi: `\Drupal::service('entity.form_builder')->getForm($entity, 'manager')` ở Controller. Tính năng này cũng cho phép admin cấu hình hiển thị các trường (Manage Form Display) riêng biệt cho mode `manager` từ giao diện UI của Drupal (nếu cần).

### 2. Cấu hình Routing mới
#### [SỬA ĐỔI] `lms_membership/lms_membership.routing.yml`
- Tạo 2 route mới:
  - `lms_membership.admin_user_memberships.add`: Đường dẫn `/workspace/user-memberships/add`
  - `lms_membership.admin_user_memberships.edit`: Đường dẫn `/workspace/user-memberships/{lms_user_membership}/edit`
- Cả hai trỏ về các method xử lý trong `MembershipAdminController`.

### 3. Controller Rendering
#### [SỬA ĐỔI] `lms_membership/src/Controller/MembershipAdminController.php`
- Bổ sung hàm `userMembershipAddForm()`: Tạo entity `lms_user_membership` trống và gọi form builder mode `manager`.
- Bổ sung hàm `userMembershipEditForm(EntityInterface $lms_user_membership)`: Gọi form builder mode `manager` với đối tượng đang truyền vào.
- Cả 2 hàm sẽ trả về mảng render sử dụng giao diện `#theme => 'lms_admin_entity_form'` (đây là template form dùng chung trong Manager Workspace, đã được dùng cho form Pricing).
- Cần truyền biến `#active_menu => 'user_memberships'` để giữ trạng thái menu Sidebar mở đúng chỗ.

### 4. Cập nhật các liên kết (Links) ở trang danh sách
#### [SỬA ĐỔI] `lms_membership/src/Controller/MembershipAdminController.php` (trong hàm `userMembershipsList`)
- Sửa lại biến `#add_url` để nó lấy đường dẫn từ route `lms_membership.admin_user_memberships.add`.
- Trong vòng lặp foreach, sửa lại `edit_url` của từng item để nó lấy đường dẫn từ route `lms_membership.admin_user_memberships.edit`.

## Các bước kiểm thử (Testing)
1. Clear cache bằng lệnh `drush cr`.
2. Kiểm tra truy cập trang List User Memberships trong Manager Workspace.
3. Click vào "Add Membership" -> Kiểm tra UI có hiển thị theo chuẩn dark theme của workspace không.
4. Kiểm tra ô "Assign User" có hoạt động autocomplete đúng danh sách user không.
5. Xóa tham số `destination` cũ nếu nó gây lỗi quay lại, form sau khi submit nên tự điều hướng bằng form_alter hoặc controller (Mặc định entity form save sẽ redirect về canonical route, ta có thể cần viết thêm một `hook_form_alter` nhỏ hoặc dựa vào `destination` query để đưa nó về lại `/workspace/user-memberships`).
6. Click vào "Edit" -> Kiểm tra logic tương tự chức năng Add.

---
## Behaviors (Hành vi Form sau khi Save)
Khi người dùng lưu (Save) form Add/Edit User Membership:
1. **Validate & Lưu:** Drupal Entity system tự động validate các trường (User hợp lệ, ngày bắt đầu/kết thúc đúng chuẩn) và lưu record xuống database bảng `lms_user_membership`.
2. **Kích hoạt Hook (Nếu có):** Bất kỳ custom hook module nào bắt sự kiện *insert / update* của `lms_user_membership` đều sẽ được tự động kích hoạt để thực thi business logic (vd: phân quyền, email nội bộ...).
3. **Redirect:** Nhờ query parameter `?destination=/workspace/user-memberships` được đính kèm vào trong các nút bấm Edit/Add ở ngoài List Controller, Drupal form controller sẽ tự động chuyển hướng người dùng quay lại màn hình danh sách Manager.
4. **Messenger:** Banner thông báo màu xanh mặc định (Ví dụ: "User Membership ... has been created") sẽ được render phía trên trang danh sách để xác nhận kết quả.
