# Kế hoạch Triển khai Kỹ thuật (Phased Implementation Plan)

Dựa trên bộ tài liệu Requirement và Workflow đã chốt, dưới đây là kế hoạch chi tiết mã nguồn cần phát triển, được **chia thành 5 Phase** rõ ràng để đảm bảo kiểm soát chất lượng kỹ càng ở từng bước.

## Người dùng lưu ý (User Review Required)
> [!IMPORTANT]
> Chúng ta sử dụng thư viện **`mpdf/mpdf`** để convert HTML/CSS sang PDF nội bộ vì mPDF hỗ trợ rất tốt font Unicode (tiếng Việt) và position absolute.

---

## Phase 1: Nâng cấp Assessment Engine (Module `lms_quiz`)
*Mục tiêu: Hoàn thiện logic thi, giới hạn lượt thi, điều kiện tiên quyết và cơ chế chấm tự luận.*

1. **Data Model Updates**
   - `lms_quiz`: Thêm `field_display_right_answer`, `field_form_style`, `field_take_quiz_number`, `field_prerequisite_quizzes`.
   - `lms_question`: Thêm `field_explanation`.
   - `lms_question_response`: Thêm `field_teacher_feedback`.

2. **Logic Updates (`QuizController.php`)**
   - Hàm `takeQuiz()`: Chặn user nếu chưa pass Quiz tiên quyết hoặc vượt quá số lần thi (Max attempts).
   - Hàm `submitQuiz()`: Phát hiện câu hỏi Essay -> Chuyển trạng thái response thành `Pending Review` (chờ chấm tay) -> Ngăn chưa xuất kết quả ngay.

---

## Phase 2: Core Entities Module Chứng chỉ (Module `lms_certificate`)
*Mục tiêu: Khởi tạo module, định nghĩa cấu trúc dữ liệu và phân quyền.*

1. **Module Scaffolding**
   - Tạo `lms_certificate.info.yml`, `.module`, `.routing.yml`.
   - Tạo `lms_certificate.permissions.yml` định nghĩa các quyền: `manage certificate templates`, `issue own course certificate`, `issue any certificate`, `revoke any certificate`.

2. **Entity: Certificate Template (`lms_certificate`)**
   - Tạo Content Entity.
   - Khai báo các field: `field_twig_template` (Raw HTML/CSS Text), `field_background`, `field_logo`, `field_signature`, `field_orientation`.
   - **Giao diện Admin (Builder UI)**: Viết custom Admin Form (Kế thừa `ContentEntityForm`) tích hợp 2 phần:
     - Khung nhập liệu raw HTML/Twig.
     - Khung Preview trực tiếp render nội dung HTML mẫu bên dưới form sử dụng Form API (AJAX callback để Live Preview).

3. **Entity: Issued Certificate (`lms_user_certificate`)**
   - Tạo Content Entity.
   - Khai báo các field lưu chứng chỉ cấp phát: Course, User, Code, PDF Link, Issue Date, Revoked (Boolean).

---

## Phase 3: Engine Render PDF (mPDF Integration)
*Mục tiêu: Đưa dữ liệu chứng chỉ vào HTML và in ra PDF với tọa độ chính xác.*

1. **Composer Install**
   - Cấu hình DDEV chạy `ddev composer require mpdf/mpdf`.

2. **Service: `CertificateGenerator`**
   - Lớp Service chịu trách nhiệm nhận thông tin `User`, `Course`, và dữ liệu Template.
   - Render file Twig `certificate-pdf.html.twig` thành chuỗi HTML.
   - Tạo file vật lý bằng mPDF và lưu vào `private://certificates/`.

3. **Theme & Twig Setup**
   - Tạo file `certificate-pdf.html.twig` cấu trúc CSS absolute position cho Logo, Signature.

---

## Phase 4: Workflow Cấp phát Tự động (Auto-Issuance)
*Mục tiêu: Bắt sự kiện khi user hoàn thành khóa học để sinh PDF tự động.*

1. **Service: `CertificateManager`**
   - Hàm `checkAndIssueCertificate(User, Course)`: Kiểm tra xem user có lms_user_certificate chưa, nếu chưa thì gọi `CertificateGenerator`.

2. **Event Subscriber (`CertificateEventListener`)**
   - Lắng nghe `CourseCompletedEvent` hoặc `QuizPassedEvent`.
   - Trigger `CertificateManager`.

---

## Phase 5: Giao diện Xác thực & Quản trị (Verification & Admin UI)
*Mục tiêu: Người dùng có thể tra cứu tính hợp lệ của chứng chỉ và Admin có thể thu hồi.*

1. **Public Verification Controller**
   - Tạo Route `/verify-certificate/{code}`.
   - Tìm entity `lms_user_certificate` dựa bào mã Code.
   - Trả về màn hình an toàn xác nhận "Hợp lệ" hoặc "Đã bị thu hồi" (Revoked).

2. **Admin UI (Revocation/Re-issue)**
   - Bổ sung Local tasks/Action links trong trang chi tiết chứng chỉ cho phép Admin click nút "Revoke" hoặc "Re-issue".
