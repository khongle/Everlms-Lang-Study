# Quiz Module - Workflow

## 1. Quá trình làm bài (Taking Flow)
1. **Prerequisite Check**: Khi User ấn nút bắt đầu trên `QuizController::takeQuiz`, hệ thống sẽ kiểm tra xem User đã pass các Quiz tiên quyết (`field_prerequisite_quizzes`) chưa và còn lượt thi (`field_take_quiz_number`) không. Nếu không, hiển thị thông báo chặn lại.
2. Lấy dữ liệu: Hệ thống load các ID `lms_question`. Gộp lại ngẫu nhiên nếu `field_question_count` > 0.
3. Giao diện: Build DTO dựa trên bundle câu hỏi, xuất ra form `lms_quiz_take` theo mode One-page hoặc Step-by-step.
4. Thực thi: Trình duyệt đếm ngược; form được auto-save liên tục qua LocalStorage.

## 2. Quá trình chấm bài (Grading Flow)
1. Nộp bài (Submit/Auto-submit khi hết giờ): Post dữ liệu tới `QuizController::submitQuiz`.
2. Factory Rating: Vòng lặp lấy `GradingStrategyFactory::getStrategy($bundle)` chấm điểm từng câu, tạo ra `lms_question_response` entities.
3. **Kiểm tra tự luận**: 
   - Nếu có câu hỏi Essay, kết quả bài Quiz được đánh dấu `Pending Review`, đưa vào hàng đợi chấm của Giáo viên.
   - Nếu chỉ toàn câu Multiple choice / Auto-grading, trực tiếp cộng `Sum Score / Max Score` làm % Final.
4. Hoàn tất kết quả: 
   - Nếu Final Score >= Threshold -> Passed.
   - Tại thời điểm Passed, kiểm tra Quiz có gắn Certificate Template không, nếu có gọi Service cấp chứng chỉ ngay lập tức.
5. Xóa LocalStorage draft và update Course Progress.
