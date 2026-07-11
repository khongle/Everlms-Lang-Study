# C04 Entity - lms_quiz

**Display name:** Quiz
**Owning module(s):** [lms_quiz](../C05-modules/lms_quiz.md)
**Mode:** as-built (Content Entity, translatable)

## Purpose
Một assessment gồm các question có thứ tự, có pass threshold, time limit tùy chọn, giới hạn số lần attempt, và prerequisite. Được gắn vào một lesson/course; vượt qua quiz cuối cùng sẽ kích hoạt việc issue certificate.

## Baseline fields
| Field | Purpose |
| ----- | ------- |
| id, uuid, langcode, default_langcode | keys + translation |
| uid | author |
| name | quiz title |
| field_body | formatted intro/instructions |
| field_course | ref → lms_course (owning course; certificate issuance source) |
| field_threshold | pass percentage |
| field_time_limit | time limit (minutes) |
| field_display_right_answer | show correct answers after submit |
| field_form_style | rendering style |
| field_question_count | number of questions to present |
| field_take_quiz_number | max attempts |
| field_questions | **multi-ref → lms_question** |
| field_prerequisite_quizzes | **multi-ref → lms_quiz** (gating) |
| created, changed | timestamps |

## Relationships
- N→1 `lms_course` (`field_course`) cho quyền sở hữu final assessment và việc issue certificate.
- N→N `lms_question` (`field_questions`); self-ref cho prerequisite.
- Cũng có thể được tham chiếu bởi `lms_lesson.field_quiz`; sinh ra `lms_quiz_result`.

## Validation rules
Nguồn: `web/modules/custom/lms_quiz/src/Entity/Quiz.php`.
- `name`: bắt buộc, translatable, tối đa 255 ký tự.
- `field_threshold`: integer, mặc định 70 — mô tả "Pass Threshold (%)" nhưng **không có ràng buộc khoảng giá trị 0-100** trong code (có thể lưu số âm hoặc >100, gap as-built).
- `field_time_limit`: integer, mặc định 0 ("0 = unlimited").
- `field_take_quiz_number`: integer, mặc định 0 ("0 = unlimited").
- `field_form_style`: `list_string`, giá trị hợp lệ `one_page|question_step`, mặc định `one_page`.
- `field_course`: entity_reference tới `lms_course`, không bắt buộc dù mô tả "Used for final assessment certificate issuance" — quiz có thể tồn tại độc lập, không gắn course.

## Data invariants
- `field_prerequisite_quizzes` (self-reference, unlimited) tạo đồ thị gating giữa các quiz, nhưng **không có kiểm tra chống chu trình (cycle detection)** trong `baseFieldDefinitions()` — có thể cấu hình 2 quiz yêu cầu lẫn nhau (A prerequisite của B và ngược lại), gây deadlock nghiệp vụ (gap as-built).
- `field_threshold` được `QuizController::submitForm()` dùng để so sánh với `field_score` của `lms_quiz_result` nhằm xác định Passed/Failed (xem `lms_quiz_result.md`) — đây là quan hệ chức năng giữa 2 entity, không phải invariant lưu trữ trên chính `lms_quiz`.

## Calculations & derived values
- Không có trường nào trên `lms_quiz` tự tính — toàn bộ (`field_threshold`, `field_time_limit`, `field_question_count`, `field_take_quiz_number`) là cấu hình instructor nhập tay.
- `field_question_count = 0` nghĩa là dùng toàn bộ `field_questions`; giá trị >0 ngụ ý chọn ngẫu nhiên một tập con câu hỏi khi làm bài — cơ chế chọn ngẫu nhiên (nếu có) nằm ở tầng controller khi render quiz, không lưu lại danh sách câu hỏi đã chọn trên `lms_quiz`.

## Editorial/data management
Instructor xây dựng quiz qua Quiz entity form, gắn question và thiết lập threshold/limit. Toàn bộ nội dung nằm trong field. Cache bị invalidate khi save. Xem [F-006](../../B-Requirements/B03-features/F-006-quiz-and-assessment-engine.md), [WF-002](../C12-workflows/WF-002-quiz-attempt-and-grading.md).
