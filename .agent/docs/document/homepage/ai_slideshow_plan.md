---
description: Kế hoạch tạo nội dung AI Slideshow
---

# Kế hoạch tính năng AI Slideshow Actions

## 1. Tổng quan
Tính năng "Slideshow Actions" bên trong EverLMS AI Engine sẽ tự động hóa việc tạo ra các Slide banner (Hero Banner) cho trang chủ frontend (dựa theo requirement PC-04). Nó cho phép quản trị viên sử dụng AI để tạo ra nội dung marketing (tiêu đề, phụ đề, nút hành động) và các hình ảnh nền tương ứng, cho phép xem trước và "Approve" (phê duyệt) để tự động xuất bản lên giao diện người dùng.

## 2. Kiến trúc & Lưu trữ Dữ liệu
- **Nguồn dữ liệu gốc (Source of Truth)**: `web/modules/custom/lms_ai/JSON/ai_slideshow.json`
- File này sẽ lưu trữ trạng thái của các slide đang được AI tạo.
```json
{
  "slides": [
    {
      "id": "slide_1",
      "title": "Làm chủ AI cho Lập trình viên",
      "subtitle": "Tăng năng suất của bạn với GitHub Copilot và Cursor. Học cách code nhanh gấp 10 lần.",
      "cta_text": "Đăng ký ngay",
      "cta_link": "/courses/21",
      "image_prompt": "A modern, futuristic workspace with a developer coding on holographic glowing screens, cinematic lighting, 8k resolution.",
      "image_uri": "public://slideshow/slide_1.jpg",
      "status": "pending"
    }
  ]
}
```
- **Lưu trữ trên Drupal**: Khi click nút **Approve Slideshow**, hệ thống sẽ đọc file `ai_slideshow.json` ở trên và tự động cập nhật vào cấu hình hệ thống (ví dụ: `lms_homepage.slideshow` config) để file template trang chủ (`lms-homepage.html.twig`) có thể lấy ra hiển thị.

## 3. Triển khai Giao diện (`AiEngineUserForm.php`)
Sẽ thêm một group `<details>` mới tên là `slideshow_actions` bao gồm:

### 3.1. Cấu hình Prompt (Textarea)
- **`slideshow_prompt`**: Lệnh prompt dùng để gọi AI sinh ra nội dung chữ cho slide.
  - *Mặc định:* "Hãy tạo ra 3 slide marketing thật cuốn hút cho hệ thống học tập LMS của chúng tôi, tập trung giới thiệu các khóa học nổi bật. Dữ liệu trả về PHẢI là định dạng JSON bao gồm: title, subtitle, cta_text, cta_link."
- **`slideshow_image_prompt`**: Lệnh prompt dùng để yêu cầu AI mô tả hình ảnh dựa theo nội dung chữ.
  - *Mặc định:* "Dựa vào các tiêu đề slide sau đây, hãy viết một câu prompt cực kỳ chi tiết bằng tiếng Anh để đưa cho AI vẽ tranh (như Midjourney) tạo ra hình ảnh minh họa cho mỗi slide. Hãy đảm bảo phong cách hình ảnh mang hơi hướng modern glassmorphism, công nghệ giáo dục, màu tối."

### 3.2. Các nút chức năng (Ajax Buttons)
1. **Run Slideshow (`ajaxGenerateSlideshowContent`)**: 
   - Gửi `slideshow_prompt` cho Gemini.
   - Nhận JSON trả về và lưu danh sách slide (chữ) vào file `ai_slideshow.json`.
2. **Request Image slideshow Prompts (`ajaxGenerateSlideshowImagePrompts`)**:
   - Đọc file `ai_slideshow.json`.
   - Giao nó cùng `slideshow_image_prompt` cho Gemini để Gemini tự viết ra câu `image_prompt` (lệnh vẽ hình) của từng slide.
3. **Request Images (`ajaxGenerateSlideshowImages`)**:
   - Hiện tại Drupal sẽ gọi một hàm API tạo ảnh (Ví dụ: tích hợp OpenAI DALL-E 3 hoặc API sinh ảnh khác) để vẽ dựa theo `image_prompt`. 
   - Ảnh sinh ra sẽ tự tải xuống thư mục `public://slideshow/` và lấy URI lưu vô `image_uri`.
4. **Approve Slideshow (`ajaxApproveSlideshow`)**:
   - Kiểm tra xem slide đã có đủ nội dung và hình hay chưa.
   - Nếu đủ, chuyển tất cả dữ liệu đã duyệt vào config của Drupal để hiển thị ra Homepage.

## 4. Các Service liên quan
- Sử dụng service `lms_ai_provider` hiện có để gọi API Gemini.
- (Tùy chọn) Sẽ cần tạo hoặc dùng một API Service để sinh ảnh thực tế (Request Images).
