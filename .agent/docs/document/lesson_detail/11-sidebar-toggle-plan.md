# Kế Hoạch Hiện Thực Hóa Chức Năng Đóng/Mở Sidebar (Lesson Detail)

**Mục tiêu**: Thêm nút Tắt/Mở Sidebar (Khu vực danh sách bài học và tiến độ) bên phải trang bài học để người dùng có thể mở rộng vùng xem nội dung chính (video, SCORM, bài giảng), đặc biệt hữu ích trên các màn hình nhỏ hơn hoặc khi cần tập trung xem.

**Yêu cầu cốt lõi**:
- Dễ dàng thao tác (nút bấm trực quan).
- Lưu lại trạng thái (người dùng đã đóng thì bài sau vẫn giữ đóng).
- Giao diện KHÔNG được vỡ (responsive mượt mà, animation chuyển cảnh êm ái).

---

## Các thành phần cần chỉnh sửa

### 1. `lms-lesson-view.html.twig`
- **Nút Toggle**: Thêm một button nhỏ cạnh nút "Back to Course" hoặc góc trên bên phải của phần Nội Dung (`.lms-learn__top-bar`).
- **JavaScript**: Viết thêm một đoạn JS nhỏ để xử lý việc ấn nút:
  - Toggle CSS class (ví dụ `#lms-learn-container.lms-sidebar-collapsed`).
  - Ghi nhớ trạng thái bằng `localStorage` để khi chuyển bài học mới, sidebar sẽ tự tiếp tục đóng nếu học viên đã chọn đóng.

### 2. CSS Style (Tích hợp luôn vào thẻ `<style>` của twig hoặc `main.css`)
- **Khi Sidebar đang bật (Mặc định)**:
  - `.lms-learn__sidebar` có `width: 360px` như hiện tại.
  - `.lms-learn__player` giới hạn `max-width: 1000px`.
- **Khi Sidebar tắt (`.lms-sidebar-collapsed`)**:
  - Ẩn hẳn Sidebar: `.lms-sidebar-collapsed .lms-learn__sidebar { display: none !important; }`
  - Mở rộng Player: `.lms-sidebar-collapsed .lms-learn__player { max-width: 1400px; }` (Cho phép bung rộng tùy ý, rất tốt cho SCORM và H5P ngang).
  - Khung content: `.lms-sidebar-collapsed .lms-learn__content { margin: 0 auto; width: 100%; }`
  
### 3. Giao diện UX/UI
- CSS Animation: Sử dụng thẻ `transition` để các thành phần thu phóng mượt mà, không bị chớp giật layout.
- Icon: Sử dụng `fas fa-outdent`, `fas fa-expand` hoặc `fas fa-columns` tùy thuộc vào ngữ cảnh cho nút Đóng/Mở, và có Tooltip "Toggle Sidebar".

---

## Các bước triển khai (Phase Execution)

**Bước 1: Chèn DOM Element (Button)**
Tìm vị trí hợp lý cạnh nút Edit Lesson / Back to Course trong file twig. Chèn `<button id="lms-sidebar-toggle" class="btn-lms-outline"><i class="fas fa-columns"></i> Mở rộng</button>`.

**Bước 2: Bổ sung CSS Animation & Style**
Thêm block code CSS quy định luật cho `.lms-sidebar-collapsed`. Để giảm thiểu rủi ro xung đột (không làm vỡ layout cũ), quá trình ẩn `.lms-learn__sidebar` sẽ dùng `hide` linh hoạt và bung rộng `flex: 1` của thằng `.lms-learn__main`.

**Bước 3: Tích hợp LocalStorage & Logic bằng JS**
Bắt sự kiện `click` vào nút, thêm/bớt class và xử lý icon đổi trạng thái. Đảm bảo JS chạy êm mỗi khi tải trang mà không làm trang bị giật nhảy layout ở giây đầu tiên.

---

> Anh review thử xem logic và kế hoạch này đã đúng ý anh chưa nhé! Nếu OK em sẽ tiến hành chỉnh sửa code vào file template gốc ngay.
