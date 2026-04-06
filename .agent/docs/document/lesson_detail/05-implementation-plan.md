# Implementation Plan: Phase 1 (Core UI/UX Revamp)

Dựa trên Master Plan, Phase 1 tập trung vào việc đại tu giao diện Giao diện Học Tập (Lesson Viewer) để mang lại trải nghiệm Glass UI hiện đại và Responsive.

## 1. Thiết kế lại Bố cục (Layout)
Cấu trúc hiện tại của `lms-lesson-view.html.twig` đang chia làm 2 cột cơ bản:
- `lms-learn__main` (chứa Video/Nội dung).
- `lms-learn__sidebar` (chứa Danh sách bài học).

**Thay đổi:**
- Tối ưu hóa padding/margin để trên màn hình Desktop, giao diện được chia theo tỷ lệ chuẩn `3:1` (Main: 75%, Sidebar: 25%).
- Trên thiết bị di động (Mobile), Sidebar tự động ẩn (hoặc rớt xuống dưới cùng dưới dạng Accordion).

## 2. Nâng cấp Player (Trình Phát)
- Bọc thẻ `<iframe>` của YouTube/Vimeo vào trong một khung Container có border-radius bo tròn góc, shadow đổ viền xám đen mờ (Glass Effect).
- Điều chỉnh tỷ lệ khung hình `16:9` mượt mà qua CSS `aspect-ratio` thay vì padding hack (padding-top: 56.25%) kiểu cũ nếu cần, hoặc làm mịn lại CSS cũ.

## 3. Tối ưu Typography & Nút tương tác
- Cải thiện Contrast (Độ tương phản) của nội dung chữ trong bài học trường (`field_content`). Sử dụng font-size to hơn một xíu (1.05rem - 1.1rem) để chống mỏi mắt.
- Nút "Mark Complete": Nâng cấp hiệu ứng Hover (phát sáng / scale) bằng CSS thuần (thay vì in-line script JS như hiện hành) cho mượt. Gắn class `btn-lms-success` theo đúng chuẩn Theming.
- Các Icon trạng thái khoá học (Đã học / Chưa học) trong Sidebar sẽ dùng bộ màu Xanh Lá / Xám chuẩn để dễ nhận diện.

## 4. Danh sách các file dự kiến thay đổi:
1. `web/themes/custom/lms_theme/css/main.css` (bổ sung/sửa đổi các class `.lms-learn__*`).
2. `web/modules/custom/lms_progress/templates/lms-lesson-view.html.twig` (Cấu trúc lại class HTML).

---
*(Kế hoạch này tập trung độc quyền vào Giao diện người dùng - UI. Các phần liên quan Javascript Logic và API sẽ được tách sang Phase 2).*
