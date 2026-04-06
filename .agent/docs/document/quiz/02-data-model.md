# Quiz Module - Data Model

## 1. Base Entity: `lms_quiz`
Đại diện cho 1 bài kiểm tra.
- **field_questions**: Danh sách câu hỏi.
- **field_question_count**: Số câu được lấy ra ngẫu nhiên (0 = lấy tất cả).
- **field_time_limit**: Thời gian làm bài (phút, 0 = unlimited).
- **field_threshold**: Điểm pass (VD: 70%).
- **field_certificate**: Template chứng chỉ được cập khi pass.
- **field_display_right_answer**: Hiển thị đáp án đúng sau submit.
- **field_form_style**: Hiển thị `one_page` hay `question_step`.
- **field_take_quiz_number**: Giới hạn số lần thi tối đa.
- **field_prerequisite_quizzes**: Các quiz bắt buộc pass trước khi làm bài này.

## 2. Base Entity: `lms_question`
Lưu trữ kho câu hỏi. Bundle ứng với Question Type.
- **field_body**: Đề bài.
- **field_points**: Trọng số điểm.
- **field_explanation**: Giải thích đáp án (show sau submit).
- **field_choices** / **field_ordered_items** / **field_matching_pairs**: Cấu hình đáp án.

## 3. Result Entity: `lms_question_response`
Lịch sử chấm điểm chi tiết cho từng câu hỏi.
- **field_question** / **field_quiz**: References.
- **field_correct**: Đúng/Sai.
- **field_score**: Điểm thực tế đạt được.
- **field_text_answer**: Dữ liệu user text.
- **field_pending_review**: Cần giảng viên chấm thủ công (ví dụ bài Essay).
- **field_teacher_feedback**: Lời nhận xét, feedback từ giảng viên.

## 4. Result Entity: `lms_quiz_result`
Tổng hợp toàn bộ 1 lần làm bài của user.
- **uid**: User làm bài.
- **field_quiz**: Bài Quiz gốc.
- **field_score**: Tính trên %.
- **field_result**: Phân loại theo Taxonomy "Quiz Results" (Passed / Failed).
- **field_responses**: Reference tới 1 mảng các `lms_question_response` (chi tiết câu hỏi).
- **field_start_time** / **field_end_time**: Thời điểm làm bài.
