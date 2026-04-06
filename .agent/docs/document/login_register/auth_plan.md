# EverLMS - Login & Registration Implementation Plan

Dựa trên các tài liệu requirements, workflows, và wireframes đã phân tích, dưới đây là kế hoạch chi tiết cho việc triển khai tính năng Login và Register trên EverLMS.

## 1. Yêu cầu & Kiến trúc chung
Tính năng Authentication bao gồm 2 phương thức chính:
- **Đăng nhập truyền thống**: Bằng Email và Mật khẩu.
- **Social Login (OAuth)**: Đăng nhập/Đăng ký nhanh qua Google và Facebook.

## 2. Cấu trúc Trang (Pages) & Route
- **Login (`/user/login`)**:
  - Giao diện tối giản (Header chỉ có Logo và nút Back to Home).
  - Layout: Form điền Email, Password (có mắt ẩn/hiện), checkbox Remember Me, link Quên mật khẩu.
  - Social Buttons: "Continue with Google", "Continue với Facebook".
  - Template: `page--user-login.html.twig`.
- **Register (`/user/register`)**:
  - Layout: Form gồm Full Name, Email, Password, Confirm Password, Checkbox đồng ý Terms & Privacy.
  - Social Buttons tương tự Login.
  - Template: `page--user-register.html.twig`.
- **Forgot Password (`/user/password`)**:
  - Form nhập Email để gửi link tạo lại mật khẩu mới.
  - Template: `page--user-password.html.twig`.

## 3. Luồng xử lý Đăng ký (Registration Flow)
### 3.1. Truyền thống (Email)
1. Người dùng nhập thông tin vào Register Form.
2. **Validation Rules**:
   - *Full Name*: Tối thiểu 2 ký tự.
   - *Email*: Định dạng chuẩn, không trùng lặp (unique).
   - *Password*: Dài tối thiểu 8 ký tự, có ít nhất 1 chữ hoa và 1 chữ số.
   - *Confirm Password*: Phải khớp hoàn toàn với Password.
   - *Terms*: Bắt buộc (Required).
3. Hệ thống tạo tài khoản Drupal User (trạng thái: Inactive/Pending).
4. Hệ thống tự động gửi **Verification Email** kèm link xác thực.
5. Người dùng click vào link -> Drupal kích hoạt (Activate) tài khoản.
6. Hệ thống gán Role: `student` (Học viên).
7. Chuyển hướng (Redirect) người dùng đến User Dashboard hoặc trang My Courses.

### 3.2. Social Login (Google/Facebook)
1. Người dùng click "Continue with Google".
2. Hệ thống redirect tới provider (Google/Facebook).
3. Sau khi xác thực, hệ thống lấy First Name, Last Name, Email, Avatar.
4. Kiểm tra Email:
   - Nếu *đã tồn tại*: Liên kết (Link) tài khoản social với user hiện hữu và tự động Login.
   - Nếu *chưa tồn tại*: Tự động tạo user mới, gán Role `student`, trạng thái Active ngay lập tức, và tự động Login.
5. Redirect đến Dashboard.

## 4. Kỹ thuật Triển khai (Technical Implementation)
- **Drupal Core**:
  - Sử dụng module `user` mặc định của Drupal để quản lý session và authentication.
  - Cấu hình Account Settings: *Khách có thể đăng ký nhưng cần xác nhận Email*. Chuyển mail template thành chuẩn HTML đẹp.
- **Contrib Modules**:
  - `social_api`: Framework nền tảng trung tâm (Base module) cho mọi tích hợp mạng xã hội.
  - `social_auth`, `social_auth_google`, `social_auth_facebook`: Các sub-modules/ecosystem của `social_api` để xử lý logic OAuth (Đăng nhập/Đăng ký).
  - `password_policy`: Để ràng buộc quy tắc độ khó của mật khẩu.
- **Custom Code (Module/Theme)**:
  - Override các Form của Drupal (`user_login_form`, `user_register_form`, `user_pass_form`) sử dụng Form API (ví dụ trong module `lms_base` hoặc theme `lms_theme`) để:
    - Ẩn Username field (dùng Email cho đăng nhập).
    - Chia đôi name thành First/Last name (nếu cần) hoặc custom Full Name field.
    - Thêm các Class CSS cần thiết để match với UI/UX Design (với TailwindCSS/Bootstrap hoặc custom CSS của project).
  - Bắt hook sự kiện (`hook_user_insert`) để tự động gán role `student` thay vì `authenticated` chung chung.

## 5. Kế hoạch Kiểm thử (Verification Plan)
- **Automated / Manual Test**:
  - Test Validation: Thử nhập sai định dạng email, mật khẩu yếu, không check term để đảm bảo form báo lỗi Inline dưới từng field rõ ràng.
  - Test Đăng ký thành công: Đăng ký một tài khoản thật, check Mailtrap (hoặc hộp thư ddev) có nhận được email Verify không, click link qua trình duyệt ẩn danh xem có kích hoạt được account không.
  - Test Social Authentication: Bật ddev, cấu hình dummy Google/FB client ID để xác định luồng redirect trả về có đúng và map được email không.
  - Test Role Assignment: Log in trang quản trị admin, kiểm tra User mới tạo xem có đúng thuộc tính role đang mang là `student` hay không.

## 6. Hỗ trợ Mobile App (API Architecture)
Dựa theo System Architecture, hệ thống có Mobile App truy cập qua REST API/JSON:API:
- Cần cung cấp Endpoint API Đăng nhập/Đăng ký cho Mobile.
- Định dạng trả về: `JWT Token` thông qua middleware xác thực.
- Xử lý Social Login qua API (Mobile App tự authenticate với SDK iOS/Android, sau đó gửi Access Token cho backend Drupal map user và trả về JWT).
