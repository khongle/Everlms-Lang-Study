# Kế Hoạch Cải Thiện Giao Diện Hồ Sơ Người Dùng (User Profile UI/UX Plan)

## Mục Tiêu (Objective)
Đồng bộ hóa giao diện trang Hồ sơ Học viên (`/user/8`) theo đúng chuẩn thiết kế **"Nền tối (Dark Mode) + Kính mờ (Glassmorphism) + Phát sáng (Neon Glow)"** cao cấp của hệ thống EverLMS mới. Biến trang Profile từ một "bản báo cáo sinh viên" khô khan thành một "Trung tâm Chỉ huy (Dashboard)" cực ngầu và mang đầy tính khích lệ học tập (Gamification).

---

## 1. Nâng Cấp Cover & Avatar (Header Section)
Khu vực ảnh bìa và đại diện đang khá tối tăm và thiếu phân tầng:
- **Cover Photo (Ảnh bìa):** 
  - Đổ một dải màu Gradient trừu tượng cuốn hút (Ví dụ: `linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(236, 72, 153, 0.1) 100%)`) phủ mờ. Thay vì 1 cục đen xì.
- **User Avatar (Ảnh đại diện):** 
  - Bo tròn thành hình tròn tuyệt đối, thêm một viền hào quang phát sáng nhẹ xung quanh Avatar: `box-shadow: 0 0 0 4px rgba(30, 41, 59, 0.8), 0 0 0 8px rgba(99, 102, 241, 0.3)`.
  - Hiệu ứng giật phồng lên 1 tí (Scale) khi user Hover chuột vào ảnh.
- **Typography:** Tên người dùng (`<h1>`) dùng font-weight siêu đậm (Bold), Chức danh (Job Title) dùng màu text gradient hoặc xanh lơ (Indigo/Teal) để xé toạc sự đơn điệu.

## 2. Nâng Cấp Các Khối Nội Dung (Glassmorphism Panels)
Thay thế tất cả các khối nền đặc đục hiên tại (Bio, Info, Quizzes, Courses) thành giao diện Kính mờ:
- **Công thức Glassmorphism mặc định:** `background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 1rem;`.
- **Bio (Tiểu sử):** Thêm một icon Dấu nháy kép (`"`) chìm mờ ảo phía sau lưng chữ để biến nó thành một thanh "Quote" hiện đại.
- **Info Grid (Email, Location...):** Thay vì các ô vuông góc cạnh thô ráp, hãy biến chúng thành các thẻ bo tròn viền (pills) mềm mại. Cài cắm thêm biểu tượng FontAwesome (Email -> `fa-envelope`, Vị trí -> `fa-map-marker-alt`, Cty -> `fa-building`) phát hào quang nhỏ bên cạnh chữ.

## 3. Khu Vực Học Tập (Recent Courses & Quizzes)
- **Thanh Tiến Độ (Progress Bar):** Chuyển từ viền xám rỗng sang màu thanh Neon tự phát sáng xé màn đêm: `box-shadow: 0 0 10px rgba(99, 102, 241, 0.5)`. Thể hiện rõ tính nhiệt huyết.
- **Danh sách Courses/Quizzes:** Giữa các dòng thay vì gạch nối đơn điệu, ta sẽ bo góc từng item, khi Hover vào sẽ đổi màu nền (Highlight background) để User có cảm giác có thể bấm (Clickable).

## 4. Sidebar Thống Kê (Learning Statistics)
Cột Sidebar bên phải được ví như "Bảng Thành Tích" (Leaderboard) của Gamer:
- **Con số Cỡ Bự (Big Numbers):** Điểm nhấn chính (\`9 Courses\`, \`5%\`...) phải được phóng to lên size \`2rem\`, màu gradient rực rỡ báo hiệu thành tích đập thẳng vào mắt.
- **Icon Hào Quang (Glowing Icons):** Các Icon Check (hoàn thành), Huy chương (Certificate), Ngôi sao (Quiz) cần được thiết kế nằm trong 1 vòng tròn có nền trùng màu hạt giống (Ví dụ: Certificate màu Cam vàng, Quiz màu Tím mộng mơ) nhằm phân chia mảng thị giác.
- Cột Sidebar sẽ được áp dụng lệnh `position: sticky; top: 100px;` để bất lỳ người dùng cuộn xem lịch sử học tập dài tới đâu, bảng thống kê này vẫn luôn bám đuổi lơ lửng trên màn hình.

---
**Tóm Lại:** Kế hoạch này không đụng chạm một tí xíu nào luồng logic hay file PHP (Controller), 100% dựa vào việc cấu trúc lại HTML Twig (`user--full.html.twig`) và đẩy max đồ họa SCSS (`_profile.scss`). Chỉ cần bạn gật đầu, hệ thống sẽ tự động thi triển!
