# Assessment Entities

> Quiz engine: Quiz → Question (8 bundles) → Answer/MatchingPair → QuizResult → QuestionResponse.

---

## Quiz (`lms_quiz`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Tên quiz |
| `field_body` | text_long | Instructions cho learner |
| `field_questions` | entity_reference (multi) | Danh sách câu hỏi |
| `field_threshold` | integer | Điểm pass (%) |
| `field_time_limit` | integer | Giới hạn thời gian (minutes, 0 = unlimited) |
| `field_certificate` | entity_reference | Certificate template cấp khi pass |
| `field_display_right_answer` | boolean | Hiển thị đáp án đúng sau submit |
| `field_form_style` | list_string | `one_page` / `question_step` |
| `field_difficulty` | entity_reference (taxonomy) | Độ khó |
| `field_question_count` | integer | Số câu rút ngẫu nhiên (0 = all) |
| `field_take_quiz_number` | integer | Giới hạn số lần thi (0 = unlimited) |
| `field_prerequisite_quizzes` | entity_reference (multi) | Quiz prerequisite |

### Business Rules
- `field_question_count > 0` → quiz random rút N câu từ pool
- `field_time_limit = 0` → không giới hạn thời gian
- `field_take_quiz_number = 0` → thi không giới hạn số lần

---

## Question (`lms_question`) — 8 Bundles

### Common Fields (tất cả bundles)
| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Nội dung câu hỏi (label) |
| `field_body` | text_long | Chi tiết / giải thích thêm |
| `field_points` | integer | Điểm tối đa |
| `field_explanation` | text_long | Giải thích đáp án (hiện sau submit) |

### Bundle-Specific Fields

| Bundle | Machine Name | Fields đặc thù | Scoring |
|--------|-------------|----------------|---------|
| **Multiple Choice** | `multiple_choice` | `field_choices` → `lms_answer` (multi correct) | Auto |
| **Single Choice** | `single_choice` | `field_choices` → `lms_answer` (1 correct) | Auto |
| **True/False** | `true_false` | `field_correct_answer` (boolean) | Auto |
| **Fill in Blank** | `fill_blank` | `field_correct_answers` (multi), `field_wrong_answers`, `field_case_sensitive` | Auto |
| **Short Answer** | `short_answer` | `field_correct_answers`, `field_keywords`, `field_match_type` (exact/contains/keywords), `field_case_sensitive` | Auto |
| **Matching** | `matching` | `field_matching_pairs` → `lms_matching_pair`, `field_ui_style` (dropdown/drag_drop) | Auto |
| **Ordering** | `ordering` | `field_ordered_items` (string multi — correct order) | Auto |
| **Essay** | `essay` | `field_min_words`, `field_max_words`, `field_rubric` | **Manual** |

---

## Answer (`lms_answer`)

| Field | Type | Mô tả |
|-------|------|-------|
| `name` | string | Label |
| `field_body` | text_long | Nội dung chi tiết (optional) |
| `field_correct` | boolean | Đáp án đúng? |

---

## Matching Pair (`lms_matching_pair`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_left_item` | string | Cột trái |
| `field_right_item` | string | Cột phải (đáp án đúng) |
| `weight` | integer | Thứ tự |

---

## Quiz Result (`lms_quiz_result`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_quiz` | entity_reference | Quiz |
| `field_taker` | entity_reference (user) | User |
| `field_score` | decimal | Điểm (%) |
| `field_result` | entity_reference (taxonomy) | Passed / Failed |
| `field_responses` | entity_reference (multi) | Chi tiết trả lời từng câu |
| `field_user_certificate` | entity_reference | Certificate đã cấp |
| `field_start_time` | timestamp | Bắt đầu làm bài |
| `field_end_time` | timestamp | Nộp bài |

### Business Rules
- `field_score >= quiz.field_threshold` → `field_result = Passed`
- Khi Passed + quiz có `field_certificate` → auto tạo UserCertificate

---

## Question Response (`lms_question_response`)

| Field | Type | Mô tả |
|-------|------|-------|
| `field_question` | entity_reference | Câu hỏi |
| `field_quiz` | entity_reference | Quiz |
| `field_answers` | entity_reference (multi) | Đáp án đã chọn |
| `field_correct` | boolean | Đúng / Sai |
| `field_score` | decimal | Điểm đạt được |
| `field_text_answer` | text_long | Trả lời dạng text (essay, short_answer) |
| `field_pending_review` | boolean | Chờ chấm manual (essay) |
| `field_teacher_feedback` | text_long | Feedback từ teacher |
