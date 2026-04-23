# Feature Guide: Instructor — Soạn Quiz

**Feature ID:** FEAT-02 (assessment-system)
**Available since:** v0.1.0
**User roles:** Instructor, Manager, Admin
**Prerequisites:** Role `instructor`.

## What It Does

Quiz builder cho phép soạn 8 question types, cấu hình grading policy, gắn quiz vào lesson hoặc chapter.

## How to Use

### Bước 1: Tạo Quiz

1. **Instructor Dashboard** → **Quizzes** → **New Quiz**.
2. Form:
   - **Title**.
   - **Passing score** (% hoặc absolute, default 60%).
   - **Time limit** (minutes, empty = unlimited).
   - **Retry limit** (-1 = unlimited).
   - **Shuffle questions** / **Shuffle options** checkbox.
   - **Show correct answers** (immediately / after_submit / after_pass / never).
3. **Save** → tạo quiz shell.

### Bước 2: Thêm Questions (8 types)

Trong quiz edit → tab **Questions** → **Add Question**. Chọn type:

#### single_choice / multi_choice
1. Question text (rich).
2. Options (2-10 options, click **Add Option** để thêm).
3. Đánh dấu correct answer(s).
4. Explanation (shown nếu policy cho phép).
5. Point value (default 1).

#### true_false
1. Question text.
2. Chọn `True` hoặc `False` là đúng.

#### fill_in_blank
1. Template text với `{{blank_1}}`, `{{blank_2}}` placeholder.
2. Với mỗi blank: list các câu trả lời chấp nhận (array); tùy chọn case-sensitive.

#### matching
1. Left column items (up to 10).
2. Right column items (up to 10).
3. Pair (drag-drop trong admin UI).

#### ordering
1. List items (2-10).
2. Drag-drop để set correct order.

#### short_answer
1. Question text.
2. Accepted answers (array of strings).
3. Tùy chọn case-sensitive, trim whitespace.

#### essay
1. Question text + guidance.
2. Min/max word count (optional).
3. Rubric (optional text) — hiển thị cho instructor khi chấm.

### Bước 3: Arrange và Save

- Drag-drop reorder questions.
- **Preview Quiz** (role Student view).
- **Save** quiz.

### Bước 4: Gắn Quiz vào Lesson

- Trong lesson edit (type = quiz) → chọn quiz từ dropdown.
- Completion criteria của lesson = pass quiz.

Hoặc gắn ở cấp chapter (quiz cuối chapter).

### Bước 5: Chấm Essay (manual)

1. `/instructor/grading` → list essay pending.
2. Click attempt → xem câu trả lời.
3. Set score + feedback text.
4. **Save** → student nhận notification + UserCertificate re-check eligibility nếu khóa close.

## Configuration Options (per quiz)

| Option | Default | Description |
|--------|---------|-------------|
| `pass_score` | 60 | Ngưỡng pass (nếu % thì 0-100, nếu raw thì 0-max_points) |
| `pass_score_type` | `percentage` | `percentage` hoặc `raw` |
| `retry_cooldown_min` | 0 | Thời gian chờ giữa các lần retry |
| `grading_mode_when_retried` | `highest` | `highest`, `latest`, `average` |
| `anti_cheat_mode` | off | Random question pool, disable copy-paste (experimental) |

## Tips & Best Practices

- **Point calibration:** Câu khó hơn → cho điểm cao hơn. Auto-grade partial credit cho multi_choice nên chia mịn.
- **Dùng explanation** cho câu sai thường gặp — giúp học viên học từ sai.
- **Essay rubric rõ ràng** — dùng criteria dạng bullet để grade consistent.
- **Time limit** phù hợp — ~1 min / câu single_choice, 2-3 min / essay.
- **Preview** trước khi gắn vào lesson.

## Known Limitations

- Không có question bank / question pool (tất cả câu cố định trong quiz).
- Không import quiz từ Moodle/Canvas format (roadmap).
- Essay không có plagiarism check tích hợp.

## Related

- Feature spec: `B-Requirements/B03-features/02-assessment-system.md`
- AC: `B-Requirements/B05-acceptance-criteria/AC-02-assessment-system.md`
- Entity: `C-Design/C04-entities/02-assessment-entities.md` (Quiz, Question, Answer, MatchingPair, QuizResult, QuestionResponse)
- ADR-005: GradingStrategyFactory
- Wireframe: `C-Design/C08-wireframes/13-admin-assessments.md`
- Learner side: [learner-03-take-quiz.md](learner-03-take-quiz.md)
