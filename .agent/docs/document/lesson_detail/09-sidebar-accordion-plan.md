# Kế Hoạch Tổ Chức Accordion Sidebar (Đóng/Mở Chapter)

## 1. Phân Tích Yêu Cầu
- **Hiện trạng:** Sidebar đang bung (expand) TOÀN BỘ Chapter và Lesson ra cùng lúc. Khi số lượng bài lên tới hàng chục, sidebar sẽ dài thò lò, người dùng bị ngợp (rối mắt).
- **Mục tiêu:** 
  - Gắn cơ chế Accordion (Lò xo / Đóng mở hộp) cho các Chapter.
  - Tự động hóa: Người dùng đang học bài nào (Lesson X thuộc Chapter Y) thì khi load trang, **CHỈ CÓ Chapter Y tự động mở**, các Chapter khác xếp gọn lại.

## 2. Giải Pháp Kỹ Thuật (Zero-JS Approach)

Thay vì phải nhét một bó code Javascript cồng kềnh để xử lý sự kiện OnClick, thêm class "active", bắt event tung tóe... Ta sẽ dùng giải pháp thanh lịch nhất của HTML5 kết hợp Twig Logic: Thẻ tử thần `<details>` & `<summary>`.

### Bước A: Tư Duy Logic Trong Twig Template
Trong vòng lặp sinh ra Sidebar của file `lms-lesson-view.html.twig`, trước khi in cái Chapter ra, ta sẽ "cử thám tử" quét qua mớ Lesson của nó:
```twig
{# Tạo cờ hiệu mặc định là False #}
{% set is_active_chapter = false %}

{# Lục soát xem Lesson hiện tại có nằm trong Chapter này không #}
{% for l in item.lessons %}
  {% if l.entity.id() == lesson.id() %}
    {% set is_active_chapter = true %}
  {% endif %}
{% endfor %}
```

### Bước B: Ráp Thẻ `<details>` HTML5
Ta sẽ bọc toàn bộ Nội dung bài học của Chapter bằng thẻ `<details>`, và Tên Chapter bằng thẻ `<summary>`.
Thuộc tính `open` sẽ được nhét vào thẻ `<details>` một cách linh hoạt dựa vào "cờ hiệu" thám tử báo về ở Bước A:

```twig
<details class="lms-learn__chapter-accordion" {% if is_active_chapter %}open{% endif %}>
  <summary class="lms-learn__chapter-summary">
    {{ item.chapter.label }}
    <i class="fas fa-chevron-down toggle-icon"></i>
  </summary>
  
  <ul class="lms-learn__lesson-list">
    <!-- Danh sách Lesson xổ ra -->
  </ul>
</details>
```

### Bước C: UI/CSS Thẩm Mỹ Ban Đầu (Glass UI Style)
Làm phép một tí CSS trong file `_learn.scss` để con Accordion này mượt như bôi luyn:
- Xóa bỏ cái dấu tam giác (Marker) mặc định phèn chua của thẻ details:
  ```css
  summary::-webkit-details-marker { display: none; }
  ```
- Thêm hiệu ứng Hover cho người ta biết có thể Click được: `cursor: pointer`, đổi màu Text khi di chuột qua.
- **Hoạt hình Cụp/Mở:** CSS thẻ `<i>` fa-chevron-down tự xoay ngược lại thành fa-chevron-up khi thẻ details có chứa thuộc tính `[open]`.

---

**Đánh giá:** Phương pháp này giữ cho Code cực kỳ sạch (Không phình JS, không đụng tới jQuery), 100% Native, Mobile thân thiện và nhắm trúng phóc yêu cầu "Mở đúng Chapter cần thiết" của dự án này!
