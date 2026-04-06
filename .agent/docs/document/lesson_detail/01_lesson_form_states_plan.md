# Kế Hoạch Cập Nhật Lesson Form States (UI Động)

## 1. Mục Tiêu
Cải thiện UI của trang `Lesson Edit/Add Form` trong khu vực Admin (Backend).
Hiện tại, khi tạo/sửa Bài học (Lesson), tất cả các trường dữ liệu (Video, SCORM, Zoom, H5P, Quiz...) đều hiển thị cùng một lúc, gây rối mắt cho người soạn thảo nội dung.
Mục tiêu là sử dụng **Drupal Form API `#states`** để ẩn/hiện các ô nhập liệu tương ứng dựa trên giá trị được chọn trong dropdown **Lesson Type** (`field_type`).

## 2. Ánh Xạ Logic Ẩn/Hiện (Form States Mapping)

Dựa trên cấu trúc Entity `lms_lesson`, trường chọn loại bài học có tên là `field_type`. Các trường tương ứng với từng loại cần được cấu hình như sau:

| Trường (Field) | Điều kiện hiển thị (hiển thị khi Lesson Type bằng...) |
| :--- | :--- |
| `field_content` (Nội dung / Body) | `text`, `mixed` (Hoặc có thể luôn hiện như một đoạn mô tả ngắn) |
| `field_video_url` (Video URL) | `video`, `mixed` |
| `field_video_file` (Video File) | `video`, `mixed` |
| `field_quiz` (Bài kiểm tra) | `quiz`, `mixed` |
| `field_h5p_embed` (Mã nhúng H5P) | `h5p`, `mixed` |
| `field_scorm_package` (Gói SCORM) | `scorm` |
| `field_pptx_attachment` (Tệp PowerPoint) | `pptx`, `mixed` |
| `field_zoom_meeting_id` (ID phòng Zoom) | `zoom` |
| `field_zoom_schedule` (Giờ Zoom) | `zoom` |

*Lưu ý: Các trường chung như Attachments, Duration, Free Preview, Drip Days, Comments, Weight, Status sẽ **luôn luôn hiển thị** mà không bị ảnh hưởng bởi Lesson Type.*

## 3. Các File Cần Chỉnh Sửa

### [MODIFY] `web/modules/custom/lms_lesson/src/Form/LessonForm.php`
- Ghi đè phương thức `form(array $form, FormStateInterface $form_state)` thừa kế từ `ContentEntityForm`.
- Trong hàm `form()`, chúng ta sẽ chèn các thuộc tính `#states` vào mảng `$form`.

**Logic kỹ thuật dự kiến:**
```php
public function form(array $form, FormStateInterface $form_state) {
  $form = parent::form($form, $form_state);

  // Lấy DOM name của trường field_type (options_select thường là field_type)
  $type_selector = 'select[name="field_type"]';

  // Điều hướng trạng thái cho Video
  $form['field_video_url']['#states'] = [
    'visible' => [
      [$type_selector => ['value' => 'video']],
      'or',
      [$type_selector => ['value' => 'mixed']],
    ],
  ];

  $form['field_video_file']['#states'] = [
    'visible' => [
      [$type_selector => ['value' => 'video']],
      'or',
      [$type_selector => ['value' => 'mixed']],
    ],
  ];

  // (Tiếp tục tương tự cho Zoom, SCORM, H5P, Quiz, v.v...)

  return $form;
}
```

## 4. Các Bước Triển Khai
1. Mở file `LessonForm.php`.
2. Thêm hàm `form()` và xây dựng toàn bộ quy tắc `#states` ánh xạ đúng cấu hình selector của Drupal Form.
3. Test thử trên url `/manage/lessons/4096/edit`.
4. Khi chọn `Lesson Type = Video`, màn hình chỉ show ra `Video URL` và `Video File`.
5. Khi chọn `Lesson Type = Zoom`, màn hình chỉ show ra `Zoom Meeting ID` và `Zoom Schedule`.

## 5. Câu Hỏi Xác Nhận (Review Required)
Bạn có muốn tôi bắt đầu sửa code trong `LessonForm.php` theo kế hoạch trên luôn không? Cứ báo cho tôi nếu bạn đồng ý nhé!
