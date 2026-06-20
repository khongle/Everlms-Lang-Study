# Feature Guide: Làm Quiz (8 Question Types)

**Feature ID:** FEAT-02 (assessment-system)
**Available since:** v0.1.0
**User roles:** Student (enrolled trong khóa chứa quiz)
**Prerequisites:** Lesson/chapter cha đã unlock.

## What It Does

Quiz engine hỗ trợ 8 question types, tự động chấm điểm (trừ essay), và lưu kết quả vào `QuizResult` entity. User có thể xem review, retry theo policy của quiz.

## 8 Question Types

| Type | Grading | Mô tả |
|------|---------|-------|
| **single_choice** | Auto | Radio, một đáp án đúng |
| **multi_choice** | Auto | Checkbox, nhiều đáp án; partial credit tùy setting |
| **true_false** | Auto | Câu đúng/sai |
| **fill_in_blank** | Auto (case sensitive tùy setting) | Gõ text vào blank |
| **matching** | Auto | Kéo thả ghép cặp |
| **ordering** | Auto | Sắp xếp thứ tự |
| **short_answer** | Auto (string-match) | Câu trả lời ngắn (1-2 từ) |
| **essay** | Manual (instructor chấm) | Long-form, nộp cho instructor review |

## How to Use

### Bước 1: Bắt đầu quiz

1. Từ lesson (type = quiz) hoặc từ `/my-quizzes` → click **Start Quiz**.
2. Modal hiển thị: số câu, time limit (nếu có), passing score, số lần được retry.
3. Confirm → timer start.

### Bước 2: Làm bài

1. Nav thanh đầu: số câu / tổng + thời gian còn lại.
2. Flag câu để review sau (cờ vàng).
3. Save progress tự động mỗi 30s (chống mất dữ liệu khi mạng rớt).
4. Click **Submit** khi hoàn tất (hoặc hết giờ tự submit).

### Bước 3: Xem kết quả

- **Auto-graded quiz:** kết quả hiện ngay sau submit.
- **Quiz có essay:** phần auto-grade hiện ngay, essay hiển thị "Pending instructor review".
- Review page cho từng câu: đáp án đúng (nếu quiz policy cho phép), giải thích (nếu có).

### Bước 4: Retry (nếu được phép)

- Policy quiz cấu hình ở instructor side (`retry_limit`, `retry_cooldown`).
- Nếu còn lượt: nút **Retry Quiz** hiện. Các lượt retry tính điểm cao nhất (hoặc mới nhất, tùy setting).

## Configuration Options (instructor set)

| Option | Default | Description |
|--------|---------|-------------|
| `passing_score` | 60% | Điểm tối thiểu để pass |
| `time_limit_minutes` | null (unlimited) | Nếu set, auto-submit khi hết |
| `shuffle_questions` | false | Hoán vị thứ tự câu |
| `shuffle_options` | false | Hoán vị options trong câu |
| `retry_limit` | 3 | -1 = unlimited |
| `retry_cooldown_minutes` | 0 | Thời gian chờ giữa các lần |
| `show_correct_answers_after` | after_pass | `immediately` / `after_submit` / `after_pass` / `never` |

## Tips & Best Practices

- Quiz được save-on-blur mỗi câu; đóng browser không mất tiến độ nếu chưa submit.
- Essay có rich text editor → dùng format headings/lists để dễ đọc.
- Xem feedback instructor ở `/my-learning/{course}/quizzes/{quiz}/result/{attempt}` sau khi essay chấm xong.

## Known Limitations

- Quiz không hỗ trợ question pool ngẫu nhiên trong MVP (tất cả câu trong quiz đều hiện).
- Essay chấm có thể delay phụ thuộc instructor.
- Concurrent submit edge case — nếu double-click submit có thể tạo 2 `QuizResult` entries; FIND chưa resolved.

## Related

- Feature spec: `B-Requirements/B03-features/02-assessment-system.md`
- AC: `B-Requirements/B05-acceptance-criteria/AC-02-assessment-system.md`
- Workflow: `B-Requirements/B04-business-workflows/WF-002-quiz-taking-grading.md`
- Entity: `C-Design/C04-entities/02-assessment-entities.md`
- Wireframe: `C-Design/C08-wireframes/05-quiz-flow.md`
- ADR-005: GradingStrategyFactory pattern
- Next: [learner-04-track-progress.md](learner-04-track-progress.md)
