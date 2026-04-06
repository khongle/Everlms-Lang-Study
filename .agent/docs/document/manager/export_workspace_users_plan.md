---
description: Tài liệu thiết kế & Nâng cấp Chức năng Export User bằng XLSX
status: Hoàn thành (Implemented)
---

# Báo cáo Triển khai Chức năng Export User & Toast Validation

Tính năng Trích xuất dữ liệu học viên (Export) và Kiểm tra chọn lựa người dùng (Validation) đã được thi công hoàn thiện trên giao diện Quản lý Người dùng (`/workspace/users`). Document này nhằm lưu trữ lại toàn cảnh kiến trúc đã được lập trình.

## 1. Mục tiêu đã giải quyết (Achievements)
- Chặn đứng dứt điểm hành vi Spam / Bấm nhầm các nút thao tác (Export/Activate/Deactivate) khi chưa chọn User nào trên giao diện.
- Trả về 1 File bảng tính chuẩn đuôi `.xlsx` (không phải chuẩn lai CSV) trực tiếp cho người quản trị với dữ liệu minh bạch, sạch sẽ, chuẩn ISO.

## 2. Chi tiết Lập trình (Implementation Details)

### 2.1 Cập nhật Giao diện (Frontend - Twig & Javascript)
- **Toast Notification (Đen đỏ hiện đại)**: 
  Tại file `lms-admin-users.html.twig`, một bộ xử lý API UI động đã được gắn vào:
  - Nếu người dùng bấm gửi (submit event) tại danh sách `lms-bulk-users-form` mà số lượng dòng (checked checkbox) bằng `0`. Form sẽ lập tức bị khóa (`e.preventDefault()`).
  - Hệ thống tự chủ động sinh ra một `<div>` thả nổi góc trên màn hình ở vùng vị trí an toàn (`z-index: 9999`) bắt mắt với hiệu ứng mờ kính `backdrop-filter: blur(8px);`.
  - Thông báo hiển thị liên tục 3 giây rồi Auto Fade Out trượt đi mất nhằm đảm bảo trải nghiệm người dùng sạch sẽ nhất.

### 2.2 Động cơ xuất Excel (Backend Controller)
- **Thư viện Cốt lõi**: Do yêu cầu đặc thù đóng file dạng `.xlsx`, hệ thống đã được cài cắm thêm một Package PHP siêu nén chỉ với duy nhất 1 tệp tin thông qua lệnh Composer: `ddev composer require shuchkin/simplexlsxgen`.
- **Thụ lý tại `AdminController::bulkUsers`**:
  Thay vì gửi ajax, Export tận dụng hành vi Post Action truyền thống để nhận Buffer phản hồi Download trực tiếp từ Server.
  - Sau khi lấy được biến `$action === 'export'`, Controller chạy lệnh quẹt qua mảng `$user_ids` và `loadMultiple()`.
  - Format 6 thông số:
    1. **ID**: Mã định danh.
    2. **Full Name**: Tên người dùng `field_first_name` + `field_last_name` hoặc Account Name dự phòng.
    3. **Email**: Trích xuất Email.
    4. **Roles**: Ghép lại thành chuỗi Text.
    5. **Status**: Filter Trạng thái `Active` / `Blocked`.
    6. **Last Login**: Quét `getLastAccessedTime()` và đẩy vào máy biến đổi Unix Time để trở thành định dạng Date gọn gàng `Y-m-d H:i:s`.
  - Gọi tĩnh hàm `SimpleXLSXGen::fromArray($data)` và chốt lệnh tải ngầm `downloadAs(EverLMS-Users-[DateTime].xlsx)` rồi gọi `exit` để đẩy thẳng Raw Bits của File xuống mặt Browser mà không bị lẫn HTML rác của Drupal.

## 3. Khắc phục Sự cố Thường gặp (Troubleshooting)
- **Báo lỗi thiếu Thư viện Excel**:
  Ngay khi chưa cài đặt Package `shuchkin/simplexlsxgen`, nếu nhấn Nút Export, hệ thống đã được gài Try/Catch thủ công báo đỏ Warning: `Thư viện tạo file Excel chưa được cài đặt...` nhắc nhở quản trị viên gọi cài đặt thay vì để Server sập (Internal Error 500).

---
*(Bản ghi này đánh dấu kết thúc Kế hoạch Export Excel & Validation UI tại Workspace Manager).*
