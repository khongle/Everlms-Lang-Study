# TEST-003 — Progress Cascade

| Field | Value |
|-------|-------|
| ID | TEST-003 |
| Type | Integration |
| Priority | P0 — Must |
| Feature | Progress Tracking / F-PROGRESS |
| Units | U-022 (ProgressCascadeService), U-059 (Race condition fix) |
| Traceability | E2E-009 (E01), KT-10 (test-plan.md), ISSUE-002 |
| Owner | Unassigned |

## Scope

Verify cascade logic: lesson complete → chapter progress → course progress. Đặc biệt test concurrency safety sau khi U-059 được implement.

## Preconditions

- Course với 2 chapters, mỗi chapter có 3 lessons
- User đã enrolled (`user_course` active)
- Tất cả `user_lesson` records ở trạng thái `not_started`

## Test Cases

### TC-003-A: Single lesson complete → chapter progress update

**Given:** Chapter có 3 lessons, user complete lesson 1  
**When:** `ProgressCascadeService::markLessonComplete(uid, lesson_id)`  
**Then:**
- `user_lesson.status = completed` cho lesson 1
- `user_course.progress_percentage` tăng đúng tỷ lệ (1/6 = ~16.67%)

---

### TC-003-B: Full chapter complete → course progress

**Given:** User complete toàn bộ 3 lessons trong chapter 1  
**When:** Lesson 3 của chapter 1 được marked complete  
**Then:**
- `user_course.progress_percentage = 50%` (3/6 lessons)
- Không có duplicate `user_lesson` records

---

### TC-003-C: All lessons complete → 100% + certificate trigger

**Given:** User complete tất cả 6 lessons  
**When:** Lesson cuối cùng được marked complete  
**Then:**
- `user_course.progress_percentage = 100%`
- `user_course.status = completed`
- Certificate issuance service được gọi (kiểm tra via mock/spy)

---

### TC-003-D: Concurrent requests — no duplicate, no lost update

**Given:** 3 concurrent requests đến `markLessonComplete` cho 3 lesson khác nhau  
**When:** Tất cả xử lý đồng thời  
**Then:**
- Sau khi tất cả settle: `user_lesson` count = 3 (không duplicate)
- `user_course.progress_percentage` phản ánh đúng 3/6 = 50%
- Không có `user_lesson` row bị lost

**Ghi chú:** Test này validate ISSUE-002 fix. Chạy sau khi U-059 hoàn thành.

| Scenario | Expected |
|----------|---------|
| 3 concurrent complete (diff lessons) | 3 records, 50% |
| 2 concurrent same lesson | 1 record (idempotent) |

## Công cụ

- PHPUnit KernelTestBase (transaction support)
- Concurrency test: custom PHPUnit parallelism hoặc separate process forks
