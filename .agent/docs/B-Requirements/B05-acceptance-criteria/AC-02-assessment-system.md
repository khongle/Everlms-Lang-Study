# AC-02 — Assessment System

**Feature ID:** F-002
**Epic:** EPIC-B — Assessment & Grading
**Epic Owner:** Instructor / System
**Related Workflow:** [WF-002 — Quiz Submit & Grading](../B04-business-workflows/WF-002-quiz-grading.md)
**Feature Doc:** [B03 F-002 Assessment System](../B03-features/02-assessment-system.md)

---

## Acceptance Criteria

### AC-B01 — Auto-grading chính xác 100%

**Priority:** Must
**Given** Quiz có các câu hỏi thuộc 7 objective types (MC, SC, TF, Fill Blank, Short Answer, Matching, Ordering)
**When** Student submit quiz với tất cả câu trả lời đúng
**Then** Score = 100%; QuizResult.passed = true
**And** Với tất cả test fixtures trong suite, auto-grading chính xác 100%

### AC-B02 — Essay manual grading

**Priority:** Must
**Given** Quiz có ≥1 câu hỏi Essay và student đã submit
**When** Instructor truy cập grading interface và nhập score cho essay
**Then** QuizResult.score cập nhật với essay component
**And** QuizResult.status chuyển từ `pending` sang `graded`; student nhận notification

### AC-B03 — Quiz configuration enforced

**Priority:** Must
**Given** Quiz được cấu hình với: passing_threshold=70, time_limit=30min, max_attempts=3
**When** Student take quiz và đạt các giới hạn
**Then** Timer đếm ngược chính xác; auto-submit khi hết giờ
**And** Block retake sau 3 lần; hiển thị "Max attempts reached"
**And** Score < 70% → status=failed; Score ≥ 70% → status=passed

### AC-B04 — Quiz result & responses lưu đầy đủ

**Priority:** Must
**Given** Student submit quiz với N câu hỏi
**When** Server xử lý submission
**Then** QuizResult record tồn tại với score, passed, attempt_number, submitted_at
**And** N QuestionResponse records tồn tại, mỗi record có question_id + student_answer + is_correct

### AC-B05 — Quiz result history

**Priority:** Should
**Given** Student đã làm quiz nhiều lần (max_attempts > 1)
**When** Student vào "My Results" hoặc quiz result page
**Then** Hiển thị tất cả attempts với score, date, pass/fail cho từng lần
**And** Best score hoặc latest score được highlight theo cấu hình quiz

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| Submit quiz sau khi tab bị đóng | Auto-save AJAX giữ tiến trình; next open: resume hoặc timed out |
| Concurrent submit từ 2 tab | Idempotency: chỉ tạo 1 QuizResult per attempt |
| Matching với duplicate keys | Validation: không cho tạo question có duplicate match options |
| Essay + other types cùng quiz | Auto-grade objective types; essay pending; final score pending |
| Score tròn (e.g., 66.666%) | Làm tròn đúng theo cấu hình (floor hoặc round) |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Performance | Quiz submit response ≤2s |
| Data integrity | QuestionResponse không bị mất khi concurrent submit |
| Security | Student không thể xem đáp án trước khi submit |
