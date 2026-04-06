# Kế hoạch Triển khai AJAX & Phân trang cho User Management

## Cập nhật Đặc tả Kỹ thuật (Theo yêu cầu tìm kiếm First Name, Last Name, Email, Username)
Tính năng Search Text cần mở rộng phạm vi dò tìm (Full-text/Partial-text search) quét qua 4 trường dữ liệu của User với mức độ ưu tiên/trọng số (Weight/Boost) ngầm định:
- `mail` (Email) ^10
- `name` (Username) ^8
- `field_first_name` ^7
- `field_last_name` ^6

> **Lưu ý về kỹ thuật Database:** Vì chúng ta đang dùng hệ thống truy vấn thực thể cốt lõi `EntityQuery` (SQL base) chứ không phải là `Search API (Solr/Elasticsearch)`, thuật toán sẽ hoạt động dưới dạng **Mệnh đề OR**. Tức là tìm thấy chữ số trùng khớp ở bất kỳ trường nào trong 4 trường trên đều sẽ nạp User đó ra, và danh sách vẫn sẽ được sắp xếp theo `Thời gian tạo mới nhất` (Created DESC) hoặc theo Alphabet chứ không tự động Sort theo điểm số Ranking ^10 ^8. (Nếu bắt buộc phải xếp hạng theo điểm chuẩn xác, ta phải dùng Raw SQL Query rất phức tạp).

## Giải pháp Tối ưu toàn diện

### 1. Refactor Logic Tìm Kiếm (Tối ưu hóa Database Query)
- **File:** `AdminController.php` (Hàm `userList()`)
- **Action:** 
  - Gỡ bỏ vòng lặp PHP `array_filter` lấy vào RAM rủi ro cao hiện tại.
  - Sử dụng `$query->orConditionGroup()` của Drupal Query để bắt `CONTAINS` đa trường:
    ```php
    $group = $query->orConditionGroup()
      ->condition('mail', $search, 'CONTAINS')
      ->condition('name', $search, 'CONTAINS')
      ->condition('field_first_name', $search, 'CONTAINS')
      ->condition('field_last_name', $search, 'CONTAINS');
    $query->condition($group);
    ```
  - Nối `->pager(10)` và `->accessCheck(FALSE)` vào `$query`. Dữ liệu sẽ chia rành mạch 10 user/trang từ Database.

### 2. Tách View Giao diện thành Partial Template
- **File mới:** `lms-admin-users-table.html.twig`.
- Cắt khối `<div class="lms-admin-card">...</div>` bảng thành vùng riêng. Đưa mã hiển thị `{{ pager }}` vào khối footer của bảng thay thế cho HTML cứng.

### 3. Cập nhật `lms_course.module`
- Đăng ký hàm Render: Bổ sung Hook `lms_admin_users_table` (mang variable `users` và `pager`) để Twig hiểu được.

### 4. Đấu nối Form JS AJAX + Reset Button
- **File:** `lms-admin-users.html.twig`
- **Thực thi:**
  - Bổ sung nút **Reset** nằm sát bên các cục Select lọc.
  - Thêm thẻ `id="lms-user-table-container"` bọc lấy include template.
  - Thêm `<script>` xử lý **Debounce Fetch (0.4s)**. Đưa AJAX chặn click của `a.pager__item` để kết nối hoàn hảo với Pagination từ backend nhả ra.

## Trạng thái
- Cần user kiểm tra Kế hoạch đã bổ sung 4 trường tìm kiếm OR `mail, name, first_name, last_name`. Việc tối ưu hóa SQL Group này sẽ cho phép hệ thống lọc nhanh như chớp. Khi có lệnh "OK", sẽ tiến hành sửa file.
