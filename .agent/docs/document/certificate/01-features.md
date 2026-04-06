# Certificate Module - Core Features

## 1. Mẫu chứng chỉ linh hoạt (Certificate Builder)
- Tích hợp **Twig/HTML Builder**: Cho phép Admin nhập mã raw HTML/CSS trực tiếp để tự do thiết kế bố cục chứng chỉ 100% tuỳ biến.
- **Live Preview (Template Default)**: Cho phép xem trước thiết kế chứng chỉ ngay lập tức trên màn hình chỉnh sửa khi thay đổi mã HTML.
- Sử dụng Token đa dạng: `[student_name]`, `[course_name]`, `[issue_date]`, `[certificate_code]`,...
- Hỗ trợ tải lên ảnh nền chứng chỉ (Background Image), Logo và Chữ ký (Signature) kèm theo nếu cần.
- Thiết lập hướng in: Dọc (Portrait) hoặc Ngang (Landscape).

## 2. Trình tạo PDF (PDF Generation Engine)
- Sử dụng thư viện **`mPDF`** để render template HTML + CSS sang định dạng PDF chất lượng cao để in ấn (Tối ưu cho Font Unicode tiếng Việt).
- Tối ưu hóa dung lượng file PDF.

## 3. Cấp phát và Thu hồi (Issuance & Revocation)
- Tự động cấp (Auto-issue) khi user hoàn thành 100% khóa học hoặc pass Quiz cuối khóa.
- Giao diện quản trị hỗ trợ cấp phát thủ công, thu hồi (Revoke) và cấp lại (Re-issue).
- **Phân quyền (Roles & Permissions)**:
  - `Administrator` / `Manager`: Toàn quyền quản trị mẫu chứng chỉ (template). Có quyền cấp phát, thu hồi mọi chứng chỉ.
  - `Instructor`: Có quyền xem danh sách chứng chỉ và cấp phát thủ công cho học viên, nhưng **chỉ giới hạn trong khóa học của mình**. Không có quyền sửa mẫu hoặc thu hồi.

## 4. Xác thực chứng chỉ (Verification & QR Code)
- Mỗi chứng chỉ sinh ra một chuỗi ID duy nhất (VD: `CERT-2026-X8Y9A`).
- Trang xác thực public (Public Verification Page) cho phép tra cứu tính hợp lệ. Nếu chứng chỉ bị thu hồi sẽ báo "Revoked".
- Tích hợp QR code in trên chứng chỉ, quét bằng điện thoại sẽ trỏ về trang xác thực.

## 5. Tích hợp Social (Social Sharing)
- Chia sẻ trực tiếp lên LinkedIn (Add to Profile).
- Cung cấp link Public URL để chia sẻ.

## 6. Huy hiệu & Gamification (Badges)
- Tự động trao huy hiệu (Badges) dựa trên các tiêu chí (Hoàn thành course, Quiz master, Streak học tập).
- Tích lũy điểm năng nổ (Points) để nâng hạng (Level progression) trong cộng đồng.
