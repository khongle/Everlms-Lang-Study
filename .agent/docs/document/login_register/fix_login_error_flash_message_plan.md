# Fix Login Error Flash Message

## Goal Description
Trong một số tình huống (ví dụ: gõ sai mật khẩu ở lần thử đầu tiên, phần mềm / block AJAX không hiển thị lỗi này ra ngay lúc đó, sau đó gõ đúng mật khẩu ở lần thứ 2), hệ thống cốt lõi của Drupal lưu lại thông báo lỗi "Unrecognized username or password" (hoặc câu phiên dịch tương đương) vào bộ nhớ tạm (session) của trình duyệt. 
Khi user đăng nhập thành công ở lần thứ 2, hệ thống Drupal sẽ chuyển hướng tới trang profile mặc định (ví dụ: `/user/16?check_logged_in=1`). Tại trang này, bộ khung render báo lỗi mới chạy và in câu thông báo lỗi của lần thất bại **trước đó** ra màn hình, gây hiểu lầm rằng tài khoản có lỗi dù thực tế user đã login thành công đầy đủ.

Mục tiêu là sẽ xóa bỏ thông báo lỗi `Unrecognized username...` tồn đọng này vào khoảnh khắc user vừa login thành công để hệ thống không in câu báo lỗi "ngớ ngẩn" đó ra màn hình nữa.

## Proposed Changes

### LMS Base Module
Sử dụng `hook_user_login` trong `lms_base.module` để can thiệp vào khoảnh khắc ngay khi user login thành công. Nếu trong session đang có kẹt thông báo sai pass từ lần trước, ta sẽ dọn dẹp nó đi. Các thông báo lỗi khác không liên quan sẽ được giữ lại an toàn.

#### [MODIFY] [lms_base.module](file:///wsl$/Ubuntu/home/phatle/projects/everlms/web/modules/custom/lms_base/lms_base.module)
Thêm đoạn code sau vào:

```php
/**
 * Implements hook_user_login().
 */
function lms_base_user_login(\Drupal\Core\Session\AccountInterface $account) {
  // Dọn dẹp các thông báo lỗi "lạc" từ những lần đăng nhập thất bại trước đó.
  // Tránh việc hiển thị lỗi "Unrecognized username or password"
  // ngay sau khi người dùng đã login và được chuyển sang trang trong.
  $messenger = \Drupal::messenger();
  $error_messages = $messenger->deleteByType('error');
  
  if (!empty($error_messages)) {
    foreach ($error_messages as $message) {
      $message_str = (string) $message;
      // Trả lại các thông báo lỗi KHÁC, chỉ dọn dẹp lỗi sai mật khẩu.
      if (strpos($message_str, 'Unrecognized username or password') === FALSE && 
          strpos($message_str, 'Tên đăng nhập hoặc mật khẩu không đúng') === FALSE) {
        $messenger->addError($message);
      }
    }
  }
}
```

## Verification Plan

### Manual Verification
1. Mở trang EverLMS trong chế độ ẩn danh (Incognito).
2. Thử đăng nhập trên giao diện (popup / form) với 1 mật khẩu sai để hệ thống sinh lỗi ngầm.
3. Đăng nhập tiếp tục ngay sau đó với thông tin account / mật khẩu **ĐÚNG**.
4. Xác minh rằng hệ thống vào thẳng bên trong tài khoản và hoàn toàn **không** còn câu báo lỗi "Unrecognized username or password" màu đỏ của Drupal nữa.
