# Kế Hoạch Triển Khai UX: Tabs cho Lesson Details

Phê duyệt Option 1: Thiết kế Dạng Tab (Kiểu Udemy / Coursera) dành cho khu vực tương tác dưới Video.

## 🔎 Báo Cáo Kiểm Tra Entity: Thuộc tính Resources
Sau khi kiểm tra Core Backend của file `Lesson.php`, **chúc mừng sếp vì Entity của chúng ta ĐÃ CÓ SẴN field này rồi!** 
Đó chính là trường `field_attachments` (loại `file`, hỗ trợ up không giới hạn các định dạng: pdf, doc, docx, ppt, pptx, xls, zip). 
Hiện tại nó đang bị "nhét chung" vào phần Overview, vì vậy **Plan của chúng ta sẽ là bứng nguyên khối Attachments này ra và gán cho nó một Tab "Resources / Materials" riêng biệt, sang xịn mịn hơn!** 

## 🎯 Mục Tiêu Mới
Gộp toàn bộ nội dung phụ trợ dưới bài học thành **4 Tab Chuyên Nghiệp**:
1. **Overview (Tổng quan)**: Nội dung mô tả bài học.
2. **Resources (Tài liệu)**: Các file đính kèm (PDF, Code files).
3. **My Notes (Ghi chú)**: Tính năng tự động lưu Note.
4. **Q&A Discussion (Thảo luận)**: Bình luận cộng đồng.

## 🛠️ Trình Tự Thi Công (Implementation Steps)

### 1. Refactor HTML Cấu Trúc (Twig Template)
File đích: `lms-lesson-view.html.twig`

Khu vực bên dưới div `lms-learn__info` sẽ được mở rộng ra 4 Tab:
```html
<div class="lms-learn__tabs-container" style="margin-top: 2rem;">
  <!-- Thanh Tab Navigation -->
  <ul class="lms-nav-tabs" style="display: flex; list-style: none; padding: 0; margin: 0; border-bottom: 1px solid hsla(0,0%,100%,0.1); gap: 2rem;">
    <li class="lms-tab-item active" data-tab="overview">Tổng quan (Overview)</li>
    <li class="lms-tab-item" data-tab="resources">Tài liệu (Resources)</li>
    <li class="lms-tab-item" data-tab="notes">Ghi chú (My Notes)</li>
    <li class="lms-tab-item" data-tab="qna">Thảo luận (Q&A)</li>
  </ul>

  <!-- Nội Dung Các Tab -->
  <div class="lms-tab-content" style="padding-top: 2rem;">
    <!-- Tab 1: Overview -->
    <div id="tab-overview" class="lms-tab-pane" style="display: block;">
      { Khối Description - Nội dung text bài học }
    </div>

    <!-- Tab 2: Resources -->
    <div id="tab-resources" class="lms-tab-pane" style="display: none;">
      { Khối File Đính kèm (Attachments) tải xuống }
    </div>

    <!-- Tab 3: Notes -->
    <div id="tab-notes" class="lms-tab-pane" style="display: none;">
      { Khối Form nhập Notes và các Nút Lưu/Xóa }
    </div>

    <!-- Tab 4: Q&A -->
    <div id="tab-qna" class="lms-tab-pane" style="display: none;">
      { Khối Render Comments AJAX }
    </div>
  </div>
</div>
```

### 2. Tiêm (Inject) CSS Styling
Thêm CSS trực tiếp vào template hoặc chỉnh sửa thư viện để thanh Tab trông hiện đại:
* Trạng thái thường (Default): Chữ màu xám (#8b949e), viền dưới trong suốt.
* Trạng thái Hover: Chữ sáng màu, viền dưới nhạt.
* Trạng thái Kích Hoạt (Active): Chữ trắng (#fff), font đậm hơn (font-weight: 600), có đường viền xanh mượt mà (Border-bottom: 2px solid #58a6ff) dưới chân làm điểm nhấn.
* Các hiệu ứng chuyển động (Transition) trơn trượt 0.3s.

### 3. Tiêm (Inject) Vanilla JavaScript
Viết đoạn mã JS để xử lý sự kiện click trên 4 Tab:
1. Xóa class `active` khỏi tất cả tab.
2. Ẩn tất cả `lms-tab-pane`.
3. Highlight tab được click và Open pane tương ứng.

---

## ✅ Kế hoạch Nghiệm thu (Verification)
- Đảm bảo khi load trang mặc định ở Tab Overview, người dùng thấy tiêu đề và text mô tả bài học.
- Click sang Tab **Resources**, hiển thị danh sách các file bài tập / slide tải xuống (`field_attachments`).
- Click sang Tab **My Notes**, khung textarea hoạt động auto-save độc lập.
- Click sang Tab **Q&A**, luồng bình luận thread comment load lên trọn vẹn.
