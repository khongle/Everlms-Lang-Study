---
description: Kế hoạch thiết kế và tự động hóa công cụ tạo Quiz bằng AI
---

# Kế hoạch AI Quiz Generator

## 1. Tổng quan
Tính năng "AI Quiz Generator" trong hệ thống AI Engine hướng tới việc tự động hóa quá trình tạo bài kiểm tra trắc nghiệm (Quiz/Assessment) cho từng Bài học (Lesson) hoặc từng Chương (Chapter) trong khóa học. Công cụ này sẽ đọc nội dung bài học đã được sinh ra trước đó và yêu cầu AI tự động biên soạn bộ câu hỏi chính xác kèm đáp án.

## 2. Kiến trúc & Lưu trữ dữ liệu
- **Nguồn dữ liệu gốc**: Vẫn sử dụng `web/modules/custom/lms_ai/JSON/ai_curriculum.json` làm trung tâm.
- Sẽ bổ sung mảng `quiz_questions` và cờ `is_quiz_generated` vào bên trong cấu trúc của một object Bài học (Lesson) hoặc Course Assessment.
```json
{
  "id": "301",
  "lesson_title": "Lesson 1: How LLMs Understand Code",
  "is_slide_content_generated": true,
  "...": "...",
  "is_quiz_generated": true,
  "quiz_questions": [
    {
      "question": "Mô hình ngôn ngữ lớn (LLMs) chủ yếu dựa trên kiến trúc mạng nơ-ron nào?",
      "options": [
        "A. Mạng nơ-ron tích chập (CNN)",
        "B. Mạng Transformer",
        "C. Mạng nơ-ron hồi quy (RNN)",
        "D. Mạng đối nghịch phát sinh (GAN)"
      ],
      "correct_answer": "B. Mạng Transformer",
      "explanation": "Kiến trúc Transformer cho phép xử lý dữ liệu song song và có cơ chế attention..."
    }
  ]
}
```

## 3. Giao diện (AiEngineUserForm.php)
Sẽ thêm một fieldset / details group mới có tên `AI Quiz Generator` (`quiz_actions`), bao gồm:

### 3.1. Cấu hình
- **`quiz_prompt` (Textarea)**: Prompt dùng để gửi cho Gemini.
  - *Mặc định*: "Dựa vào nội dung chi tiết bài học sau đây, hãy đóng vai một chuyên gia đào tạo và ra 3 câu hỏi trắc nghiệm khách quan đa lựa chọn (Multiple Choice) để kiểm tra kiến thức của học viên. Mỗi câu phải có 4 đáp án (A,B,C,D), chỉ định rõ đáp án đúng và kèm theo tối đa 2 dòng giải thích. Hãy trả về ĐÚNG MỘT mảng JSON."

### 3.2. Các Action Buttons
1. **Run Quiz (`ajaxGenerateQuiz`)**:
   - Vòng lặp quét qua `ai_curriculum.json`. Tìm các Lesson đã có `lesson_detail` hoặc `slide_content` nhưng chưa có `quiz_questions`.
   - Lắp nội dung của bài học đó vào `quiz_prompt` và gọi AI Gemini sinh JSON câu hỏi.
   - Lưu trữ lại vào file JSON (`is_quiz_generated = true`).
2. **Approve Quiz (`ajaxApproveQuiz`)**:
   - Đọc JSON, duyệt qua toàn bộ các bài học đã có `is_quiz_generated`.
   - Tạo thực thể Bài kiểm tra (Quiz Entity) trong Drupal, liên kết nó vào đúng bài học tương ứng (LMS Lesson entity / Quiz Node tùy theo kiến trúc database cũ của EverLMS).

## 4. Kiểm thử
- Xóa log cache. Thay đổi JSON mẫu và bấm "Run Quiz" để xem file json có thực sự được inject các câu hỏi trắc nghiệm chuẩn chỉnh hay không. 
