# Mẫu Tham Chiếu JSON AI Quiz (EverLMS Quiz JSON Schema)

Dựa trên tài liệu kiến trúc của hệ thống, EverLMS hỗ trợ các loại câu hỏi đa dạng. Để AI Engine có thể tự động sinh câu hỏi và cho phép Code PHP đọc hiểu để tạo Entity tự động, chuỗi JSON trả về từ AI bắt buộc phải theo đúng cấu trúc mảng (Array) chứa các Object câu hỏi như sau:

```json
[
  {
    "type": "single_choice",
    "question": "Ngôn ngữ lập trình nào sau đây chạy ở nền tảng trình duyệt?",
    "options": [
      "Python",
      "Java",
      "JavaScript",
      "C++"
    ],
    "correct_answer": "JavaScript",
    "explanation": "JavaScript là ngôn ngữ duy nhất trong danh sách chạy trực tiếp trên các trình duyệt web."
  },
  {
    "type": "multiple_choice",
    "question": "Những công nghệ nào thuộc về Front-end?",
    "options": [
      "HTML",
      "Node.js",
      "CSS",
      "React"
    ],
    "correct_answers": ["HTML", "CSS", "React"],
    "explanation": "Node.js là công nghệ Back-end, còn lại là Front-end."
  },
  {
    "type": "true_false",
    "question": "Trái đất xoay quanh Mặt trời.",
    "correct_answer": true,
    "explanation": "Đây là kiến thức thiên văn học cơ bản đã được chứng minh."
  },
  {
    "type": "fill_in_the_blank",
    "question": "Nước sôi ở điều kiện tiêu chuẩn là [blank] độ C.",
    "correct_answer": "100",
    "explanation": "Ở áp suất tiêu chuẩn (1 atm), nước sôi ở 100 độ C."
  },
  {
    "type": "matching",
    "question": "Nối các quốc gia với thủ đô tương ứng:",
    "pairs": [
      {"left": "Việt Nam", "right": "Hà Nội"},
      {"left": "Nhật Bản", "right": "Tokyo"},
      {"left": "Hàn Quốc", "right": "Seoul"}
    ],
    "explanation": "Kiến thức địa lý cơ bản."
  },
  {
    "type": "ordering",
    "question": "Sắp xếp các bước phát triển phần mềm theo thứ tự Waterfall:",
    "correct_order": [
      "Phân tích yêu cầu",
      "Thiết kế",
      "Lập trình",
      "Kiểm thử",
      "Triển khai"
    ],
    "explanation": "Mô hình Waterfall yêu cầu làm tuần tự theo đúng 5 bước này."
  },
  {
    "type": "essay",
    "question": "Phân tích ưu nhược điểm của việc ứng dụng AI vào hệ thống LMS.",
    "explanation": "Gợi ý chấm điểm: Học viên cần phân tích rõ tính năng tự động hóa, giảm tải thao tác tay (ưu điểm) và rủi ro sai sót dữ liệu (nhược điểm)."
  }
]
```

## Giải thích các cột:
- `type`: Định danh loại câu hỏi. Bắt buộc là 1 trong các enum: `single_choice`, `multiple_choice`, `true_false`, `fill_in_the_blank`, `matching`, `ordering`, `essay`.
- `question`: Nội dung câu hỏi. Dùng chữ `[blank]` trong trường hợp điền vào chỗ trống.
- `options`: Mảng các đáp án có thể chọn (Chỉ dành cho `single_choice`, `multiple_choice`).
- `correct_answer`: Đáp án đúng duy nhất (Dành cho `single_choice`, `true_false`, `fill_in_the_blank`).
- `correct_answers`: Mảng các đáp án đúng (Dành cho `multiple_choice`).
- `pairs`: Mảng các cặp nối chéo (Vế trái - Vế phải) (Dành cho `matching`).
- `correct_order`: Mảng đáp án sắp xếp theo thứ tự đúng từ trên xuống dưới (Dành cho `ordering`).
- `explanation`: Giải thích đáp án sẽ được hiển thị sau khi học viên nộp bài.

Bạn có thể copy toàn bộ cấu trúc JSON ví dụ này và dán thẳng vào mục **Quiz Prompt** kèm theo lệnh:
*"Hãy đọc bài học và sinh ra 5 câu hỏi ngẫu nhiên tuân thủ NGHIÊM NGẶT cấu trúc JSON mẫu sau đây:"*
