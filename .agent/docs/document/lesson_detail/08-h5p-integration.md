# Kế Hoạch Tích Hợp H5P Native Lên Drupal LMS

Dựa theo chốt phương án, chúng ta sẽ **tích hợp trực tiếp công cụ H5P vào nền tảng EverLMS** thông qua module `h5p` của Drupal (Native Integration). Phương án này chuyên nghiệp, bảo mật dữ liệu và quan trọng nhất là đo lường được chi tiết kết quả học tập.

---

## Lợi Ích Cốt Lõi
1. Mọi dữ liệu (hình ảnh, video, text) nằm hoàn toàn trên Server EverLMS.
2. Học sinh tương tác H5P (chơi game, giải đố, coi video pause lại trả lời) => **Hệ thống Tracking trực tiếp điểm số qua chuẩn xAPI** và đổ về File Report nội bộ.
3. Giáo viên không cần vào web ngoài, họ tự biên tập nội dung H5P bằng công cụ kéo thả trực quan (H5P Editor) **ngay trong lúc tạo Bài Học trên EverLMS**.

---

## Kiến Trúc Hệ Thống H5P Trên Drupal
EverLMS sẽ dựa trên 4 khối chính sau khi cài:
1. **Module `h5p` (Drupal Contrib):** Cầu nối giao tiếp giữa H5P PHP Core và các bảng Database của Drupal.
2. **H5P Core (PHP Library):** Trái tim của H5P, xử lý xuất file, nén/giải nén `.h5p`.
3. **H5P Editor (PHP Library):** Bộ công cụ soạn thảo WYSIWYG cho H5P.
4. **H5P Hub (Libraries):** Quầy thư viện chứa hàng chục loại nội dung (như Interactive Video, Drag & Drop, Course Presentation,...). Nó sẽ tự tải về host của mình.

---

## 🚀 Lộ Trình Triển Khai (Implementation Plan)

### Bước 1: Cài đặt Core H5P
- Cần chạy lệnh Composer để tải source code của Module.
- Kích hoạt (Enable) module H5P.
- Cấp quyền (Permission) cho Giáo Viên được phép "Tạo, Chỉnh sửa, Upload nội dung H5P".
- Truy cập vào `/admin/content/h5p` để cấu hình kích hoạt Hub (Cho phép tải bộ thư viện H5P Editor về hệ thống).

### Bước 2: Thiết kế lại Database Entity "Lesson"
- Hiện tại Entity Lesson `lms_lesson` đang có trường `field_h5p_embed` (Kiểu văn bản thuần để dán Iframe).
- Chúng ta sẽ **TẠO THÊM trường mới**: `field_h5p_content`.
- Trường này sẽ có định dạng `Entity Reference` (Chỉ định dạng bảng Entity), nó móc nối trực tiếp tới Entity `h5p_content` vừa được Module H5P tạo ra.

### Bước 3: Nâng cấp luồng Giao Diện (Admin & Frontend)
- **Bên phía Admin (Giáo viên ráp bài):**
  - Tại Form sửa Bài học, lúc chọn loại `H5P Interactive`, hệ thống sẽ cho giáo viên chọn:
    - Hoặc là Bấm Nút Tạo Mới nội dung H5P (Bật Editor trực quan của H5P lên ngay tại chỗ).
    - Hoặc là Bấm Nút Upload File `.h5p` có sẵn mang từ ngoài vào.
- **Bên phía Frontend (Học sinh trải nghiệm):**
  - Trong file `lms-lesson-view.html.twig`, Twig sẽ gọi thẳng `{{ content.field_h5p_content }}` ra.
  - Lúc này H5P Module tự động lo liệu 100% phần Iframe hoặc thư viện JS (`h5p.js`, `h5p.css`), tự dựng lên Game tương tác mà không cần phải dùng đường link ngoài như cũ.

### Bước 4: Tracking & Lưu Điểm Số (xAPI Event Listener)
- Đây là cái "ăn tiền" nhất của hệ thống học tập LMS. Đoạn JS do Module H5P sinh ra sẽ âm thầm bắn sự kiện `xAPI` (Tin Can) mỗi khi học sinh làm xong điều gì (VD: Xem hết Video + trả lời đúng 4/5 câu).
- Đội Dev sẽ code bổ sung 1 đoạn `everlms_h5p_tracking.js` để tóm lấy cái xAPI Event này:
  ```javascript
  H5P.externalDispatcher.on('xAPI', function (event) {
     if (event.getScore() !== null) { // Có điểm rồi!
        var score = event.getScore();
        // Bắt đầu dùng AJAX gửi gói dữ liệu điểm số này về Controller Drupal để lưu bảng lms_progress
     }
  });
  ```

---
**=> Tổng thể 4 bước này sẽ mang H5P chính thống 100% vào trong tay EverLMS. Anh Reivew lại file này, nếu OK thì gõ OK để Team Dev bắt tay vào Gõ Lệnh (Bước 1) ngay nhé ạ!**
