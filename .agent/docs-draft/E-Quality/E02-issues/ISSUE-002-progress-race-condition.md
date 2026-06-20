# ISSUE-002 — Progress Race Condition trên Concurrent Requests

| Field | Value |
|-------|-------|
| ID | ISSUE-002 |
| Severity | **High** |
| Status | OPEN |
| Source | RISK-002 (A-Project-Information/A03-project-context.md) |
| Related Unit | U-059 — Progress race condition fix (DB lock/transaction) |
| Related Decision | DEC-003 — optimistic lock vs queue |
| Found | 2026-04-15 |
| Owner | Unassigned |

## Mô tả

`ProgressManager` (lms_progress) cập nhật `user_course` completion percentage bằng cách đọc danh sách `user_lesson` → tính % → ghi lại. Nếu nhiều request đến đồng thời (ví dụ: học viên complete nhiều lesson cùng lúc hoặc race giữa tab browser), có thể xảy ra:

- Read-modify-write race: hai transaction đọc cùng giá trị cũ, cùng ghi, một cái bị lost update
- Duplicate `user_lesson` record nếu insert không có unique constraint đúng

## Cách tái hiện (Reproduction)

1. Dùng k6 / ab: gửi 10 request đồng thời PATCH `/api/lms/progress/lesson/{id}/complete` cho cùng user + lesson
2. Sau đó truy vấn `user_course.progress_percentage` → thường thấy giá trị không nhất quán
3. Đôi khi xuất hiện 2 row `user_lesson` cùng `(uid, lesson_id)`

## Root Cause Hypothesis

`ProgressCascadeService::markLessonComplete()` không dùng database transaction bao phủ đủ — SELECT + UPDATE không nằm trong `FOR UPDATE` lock. MariaDB InnoDB có thể cho phép concurrent reads trả về snapshot cũ trước khi write commit.

## Tác động

- **Data Integrity**: Progress % sai → certificate issuance có thể bị trigger sớm hoặc không trigger
- **UX**: Dashboard hiển thị % lệch với thực tế

## Phương án khắc phục đề xuất

**Option A (DEC-003)**: Wrap toàn bộ cascade trong `db->startTransaction()` + `SELECT ... FOR UPDATE` trên `user_course` row.

**Option B**: Dùng queue worker — lesson complete event enqueue, worker xử lý tuần tự.

## Traceability

- Risk: RISK-002
- Unit spec: U-059
- Test: TEST-003-progress-cascade trong E03-test-definitions/
