# EverLMS — Open Switcher Module (User Impersonation)

> **Mô tả:** Module `open_switcher` là một công cụ tùy biến (custom module) trên nền tảng EverLMS, cho phép Quản trị viên (hoặc các Role được ủy quyền) mạo danh thao tác (impersonate) dưới góc nhìn của một tài khoản Học viên hoặc Giảng viên nhằm mục đích hỗ trợ hệ thống (Support) / Debug lỗi giao diện mà không cần biết mật khẩu thật của họ. Đặc biệt, công cụ này hỗ trợ cơ chế "Switch Back" nhanh chóng.

---

## 1. Cơ chế hoạt động cốt lõi (Core Logic)
- **Session Swapping:** Khi thực thi switch, module sẽ lưu lại ID của người dùng quản trị ban đầu vào HTTP Session (`open_switcher_original_uid`). Sau đó, hệ thống gọi hàm Core của Drupal là `user_login_finalize()` để đóng giả danh tính tài khoản đích.
- **Smart Menu Injection:** Thông qua hàm Hook `open_switcher_preprocess_menu()`, hệ thống tự động tiêm cờ báo `is_impersonating` và nút lệnh **"Switch Back"** vào Menu Tài khoản (`account` menu). Điều này rất quan trọng vì tài khoản bị mạo danh thường không có quyền Switch, nhưng nút "Switch Back" vẫn được ép hiển thị dựa trên Session mạo danh.
- **Lớp bảo mật thép:**
  - Vô hiệu hóa tính năng lấy quyền của User 1 (`uid: 1` - Super Admin) nếu người mạo danh không phải là Super Admin.
  - Tự động "ẩn" toàn bộ tài khoản mang Role `administrator` khỏi danh sách Switch frontend.
  - Chặn mạo danh chính mình (Impersonating self).

---

## 2. Hướng dẫn cài đặt & Cấu hình (Setup)

**Bước 1: Bật Module (Enable)**
Vì module này nằm ở thư mục `web/modules/custom/open_switcher`, nó đã sẵn sàng nhưng có thể chưa được kích hoạt. Chạy lệnh:
```bash
ddev drush en open_switcher -y
```

**Bước 2: Cấp quyền thao tác (Permissions)**
- Truy cập trang: **Admin > People > Permissions** (`/admin/people/permissions`).
- Tìm quyền có tên là: **"Switch to another user"** (`switch users`).
- Đánh dấu (Tick) quyền này cho các Vai trò quản lý cấp cao như `Manager` (Quản lý) hoặc `System Admin`.
- ⚠️ **Lưu ý bảo mật:** Không bao giờ cấp quyền này cho `Student` (Học viên) hoặc các Role có đặc tính công khai.

---

## 3. Cách sử dụng thực tế (Usage Guide)

### Góc nhìn Front-end (UI Mới cho Manager)
- **Truy cập:** Đi tới đường dẫn `/app/switch-user`.
- **Thao tác:** Trang này được xử lý thông qua `SwitchFrontendController`. Nó sẽ in ra danh sách toàn bộ các Users trên nền tảng (Kèm Filter dropdown để lọc cấp độ như Student, Instructor).
- **Thực thi:** Click vào nút **Switch** kế bên người cần giả dạng. Giao diện sẽ chớp nhẹ, thông báo báo xanh (Status Message) và lập tức chuyển bạn về màn hình chính dưới giao diện/danh tính của người đó.
- **Thoát khỏi mạo danh:** Từ bất kỳ trang web nào, mở Menu Tài khoản (Dropdown góc trên bên phải của Header) → Nhấn **Switch Back** (`/admin/switch-back`) để rũ bỏ vỏ bọc và khôi phục quyền Manager.

### Góc nhìn Back-end (Giao diện chuẩn Admin)
- Có thể dùng Form Switch chuẩn tại Route `open_switcher.switch_form` qua đường dẫn `/admin/switch-user`.

---

## 4. Tham chiếu kỹ thuật (Technical Routes)
Danh sách các Machine routes để team Frontend gắn links khi code file `.html.twig`:

| Tên Route | Đường dẫn (URL) | Chức năng |
| :--- | :--- | :--- |
| `open_switcher.frontend` | `/app/switch-user` | Trang liệt kê danh sách Users Frontend |
| `open_switcher.frontend_switch` | `/app/switch-user/{user}`| Action mạo danh một ID cụ thể |
| `open_switcher.switch_back` | `/admin/switch-back` | Action trả lại phiên đăng nhập gốc |
| `open_switcher.switch_form` | `/admin/switch-user` | Trang Form nhập UID theo chuẩn Backend |
