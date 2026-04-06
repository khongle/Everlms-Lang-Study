# Quiz Module - Core Features

## 1. Loại câu hỏi (Question Types)
Kiến trúc Factory Pattern hỗ trợ 8 loại câu hỏi:
- Trắc nghiệm 1 đáp án (Single choice).
- Trắc nghiệm nhiều đáp án (Multiple choice).
- Đúng/Sai (True/False).
- Điền từ vào chỗ trống (Fill in the blank).
- Câu hỏi tự luận (Essay) - Chờ Admin tự chấm (Pending review).
- Nối chéo (Matching).
- Sắp xếp thứ tự (Ordering) bằng Drag & Drop.

## 2. Cơ chế làm bài (Test Engine)
- **Display Style**: Hiển thị tất cả câu hỏi trên 1 trang (One-page) hoặc từng trang một (Step-by-step).
- **Prerequisites & Limits**: Check điều kiện học phần (phải pass quiz A mới được làm quiz B), và Giới hạn số lần thi (Max attempts).
- **Randomization**: Bốc ngẫu nhiên N câu hỏi từ Question Bank.
- **Timer**: Đếm ngược thời gian bằng JavaScript, tự động auto-submit khi hết giờ.
- **Auto-save**: Lưu nháp bài thi xuống `localStorage` để chống mất dữ liệu khi f5 / rớt mạng.

## 3. Phân hệ chấm điểm (Grading System)
- Interface `QuestionGradingInterface` định nghĩa logic chấm thi độc lập cho từng Question Type.
- Chấm tự động (Auto-grading) ngay sau khi nộp. Đối với câu tự luận (Essay), sẽ đưa vào hàng đợi chờ chấm tay (Manual grading queue).
- Hỗ trợ Teacher/Admin ghi đè điểm số (Score override).
- Cấu hình cho phép hiển thị đáp án đúng sau khi nộp bài.

## 4. UI / UX
- Dashboard kết quả thi tổng quát: Hiển thị đúng/sai chi tiết.
- Giao diện Premium Dark theme.
