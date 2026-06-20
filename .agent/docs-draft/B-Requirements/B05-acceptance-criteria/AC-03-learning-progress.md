# AC-03 — Learning Progress

**Feature ID:** F-003
**Epic:** EPIC-C — Learning Progress
**Epic Owner:** System / Student
**Related Workflow:** [WF-001 — Enrollment & Learning](../B04-business-workflows/WF-001-enrollment-learning.md)
**Feature Doc:** [B03 F-003 Learning Progress](../B03-features/03-learning-progress.md)

---

## Acceptance Criteria

### AC-C01 — Progress cascade chính xác

**Priority:** Must
**Given** Student enrolled trong course với 2 chapters, mỗi chapter 4 lessons
**When** Student mark complete lesson đầu tiên trong chapter 1
**Then** LessonProgress record tạo với status=completed
**And** ChapterProgress.percent cập nhật thành 25% (1/4)
**And** UserCourse.percent cập nhật thành 12.5% (1/8 tổng)

### AC-C02 — Không tạo duplicate progress records

**Priority:** Must
**Given** Hai concurrent requests gửi mark-complete cho cùng một lesson từ cùng student
**When** Cả hai request hit server trong cùng 100ms
**Then** Chỉ 1 LessonProgress record tồn tại
**And** Progress calculation chỉ tính 1 lần, không double-count
**And** Không có DB constraint violation hoặc 500 error

### AC-C03 — Continue learning URL chính xác

**Priority:** Must
**Given** Student đã complete 5/10 lessons (lessons 1-5 done, lesson 6 chưa)
**When** Student click "Continue Learning" từ dashboard hoặc course detail
**Then** Redirect đến lesson 6 (next incomplete theo thứ tự chapter/lesson weight)
**And** Nếu tất cả lessons done: redirect đến lesson cuối cùng hoặc course summary

### AC-C04 — Dashboard progress hiển thị đúng

**Priority:** Must
**Given** Student enrolled 3 courses với progress khác nhau (0%, 50%, 100%)
**When** Student truy cập dashboard
**Then** Mỗi course card hiển thị correct percent và progress bar
**And** In-progress courses sorted/highlighted; Completed courses có badge

---

## Edge Cases

| Case | Expected Behavior |
|------|-----------------|
| Mark complete lesson đã complete | Idempotent: không tạo duplicate, progress không thay đổi |
| Lesson bị xóa sau khi đã complete | Progress percentage recalculate; orphan records cleaned |
| Course có 0 lessons | Progress = 0%, không crash |
| Chapter reorder sau student đã học | Continue URL re-evaluates theo weight mới |
| Progress khi membership hết hạn | Progress records giữ nguyên; access bị lock nhưng data intact |

## NFR Tie-ins

| NFR | Target |
|-----|--------|
| Performance | Mark-complete API ≤500ms |
| Concurrency | DB-level lock prevent race condition on progress updates |
| Data integrity | Progress percentage luôn consistent với lesson completion records |
