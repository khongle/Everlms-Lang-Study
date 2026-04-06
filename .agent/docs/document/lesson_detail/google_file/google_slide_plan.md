# Kế hoạch phát triển `GoogleSlideBuilderService` (Architecture V2 - Template Based)

## 1. Ý tưởng cốt lõi (Core Concept)
Thay vì dùng API đi vẽ và thiết kế từng text box hay căn chỉnh layout tốn công sức, chúng ta sẽ áp dụng mô hình **Template-based Generation**:
1. Người dùng thiết kế sẵn 1 file **Google Slides Template** cực kỳ đẹp mắt với các slide mẫu và chèn sẵn các thẻ giữ chỗ (Placeholder) dạng `{{slide_1_title}}`, `{{slide_2_left_content_1}}`,...
2. Cấu hình ID của file template này và cấu trúc JSON các placeholder vào một Form Config trên Drupal (như hình AI Config Form).
3. Gửi cấu trúc JSON mapping đó cho Gemini Prompt, yêu cầu AI sinh ra nội dung bài học khớp chính xác định dạng keys.
4. Drupal gọi Google Drive API để **Duplicate (nhân bản)** file template gốc thành 1 file Slide mới cho bài học.
5. Drupal gọi Google Slides API chạy hàm **ReplaceAllText** để thế chỗ toàn bộ placeholder `{{...}}` bằng data bóc tách từ JSON do Gemini trả về.
6. Cấp quyền public cho file Slide mới và gắn vào khóa học.

## 2. Tiền đề & Yêu cầu API
- Thư viện: `google/apiclient` (Package chuẩn của Google API). Cài đặt `ddev composer require google/apiclient`.
- Google Cloud Console: Bật **Google Drive API** (để nhân bản file) và **Google Slides API** (để thay thế text).
- Xác thực bằng **Service Account**:
  - Tải file `credentials.json` lưu ẩn ở server.
  - Share quyền `Editor` trực tiếp từ file Template Slide trên Google Drive của Admin cho email của Service Account để Service Account có quyền đọc và copy file.

## 3. Kiến trúc Service `GoogleSlideBuilderService`
- **Class:** `Drupal\lms_ai\Service\GoogleSlideBuilderService`

### Cấu trúc các hàm xử lý chính:

#### 1. `__construct()`
- Auth Google Client với file JSON của Service Account.
- Khởi tạo 2 services: 
  - `$this->driveService = new \Google_Service_Drive($client);` 
  - `$this->slidesService = new \Google_Service_Slides($client);`

#### 2. `buildSlideFromTemplate(string $templatePresentationId, array $generated_json, string $lesson_title)`
- Hàm Controller chính phối hợp các tiến trình. Nhận đầu vào là cấu hình ID Template, mảng JSON đã có ruột do Gemini điền, và tiêu đề `$lesson_title`.

#### 3. `duplicateTemplateSlide(string $templatePresentationId, string $newTitle)`
- Dùng chức năng Drive API Copy: `$newFile = new \Google_Service_Drive_DriveFile(); $newFile->setName($newTitle); return $driveService->files->copy($templatePresentationId, $newFile);`
- Lấy ra `newPresentationId`. Mặc định file mới sẽ kế thừa quyền sở hữu của Service Account (không tốn dung lượng tài khoản chính).

#### 4. `replacePlaceholders(string $presentationId, array $generated_json)`
- Parse đệ quy mảng JSON từ Gemini (`$generated_json`). Với mỗi `key` (như `slide_1_title`), nó sẽ tương đương với chuỗi Text `{{slide_1_title}}` trong file Slides gốc.
- Xây dựng mảng DTO `ReplaceAllTextRequest`.
  Ví dụ: Với thẻ `{{slide_1_title}}` có giá trị là "Lịch sử AI":
  ```json
  {
    "replaceAllText": {
      "containsText": {
        "text": "{{slide_1_title}}",
        "matchCase": true
      },
      "replaceText": "Lịch sử AI"
    }
  }
  ```
- Gom tất cả lệnh thay thế (có thể là hàng trăm key từ trang 1 đến 15) lại, gọi API một lần duy nhất để tối đa hiệu năng:
  `$slidesService->presentations->batchUpdate($presentationId, $batchUpdateRequest);`

#### 5. `makeItPublic(string $presentationId)`
- File do Service Account mới tạo ra là Private.
- Dùng Drive API Permissions cấp quyền public vô danh mở rộng:
  ```php
  $permission = new \Google_Service_Drive_Permission(['type' => 'anyone', 'role' => 'reader']);
  $this->driveService->permissions->create($presentationId, $permission);
  ```

#### 6. Render và Lưu trữ (Lesson Entity)
- Lưu trữ ID `presentationId` vào field riêng của Lesson (ví dụ `field_google_slide_id`) hoặc lưu thẳng link Iframe vào `field_video_url` nếu phù hợp.
- Chuỗi Iframe hiển thị ở Front-end: 
  `https://docs.google.com/presentation/d/{$presentationId}/embed?start=false&loop=false&delayms=3000`

## 4. Ưu điểm của kiến trúc "Template-based"
- **Thẩm mỹ 100%:** Thiết kế cực đẹp từ tay designer trên Google Slides. Layout, font chữ, hiệu ứng chuyển cảnh của Google sẽ giữ nguyên không bị lệch lạc như code CSS iframe.
- **Dễ dàng Maintain:** Muốn đổi giao diện Slide? Đơn giản thiết kế 1 file mới rồi vào trang Drupal Config dán ID kia vào.
- **Tiết kiệm API Quota thần tốc:** Việc clone file bằng Drive API và chạy 1 lệnh BatchReplace bằng Slides API tốn cực ít Request, giúp build ra cả bộ giáo trình 30 phút trong vài giây mà không lo cạn giới hạn quota (Rate Limits) của Google so với tự Insert TextBox từng trang tĩnh.
