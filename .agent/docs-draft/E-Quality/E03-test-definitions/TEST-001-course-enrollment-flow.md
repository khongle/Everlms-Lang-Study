# TEST-001 — Course Enrollment Flow

| Field | Value |
|-------|-------|
| ID | TEST-001 |
| Type | Integration + E2E |
| Priority | P0 — Must |
| Feature | Enrollment / F-ENROLL |
| Units | U-021 (UserCourse entity), U-022 (Progress cascade service) |
| Traceability | E2E-002 (E01), E2E-003 (E01) |
| Owner | Unassigned |

## Scope

Verify toàn bộ luồng enrollment: free enroll, paid enroll (sau khi payment confirm), và guard ngăn enroll trùng.

## Preconditions

- Course tồn tại với status `published`
- User đã authenticated với role `student`
- (Paid test) Order đã được tạo với status `completed`

## Test Cases

### TC-001-A: Free course enrollment

**Given:** Course có `price = 0`, user chưa enrolled  
**When:** User gọi enroll endpoint hoặc click "Enroll Now"  
**Then:**
- `user_course` record được tạo với `status = active`
- `progress_percentage = 0`
- User được redirect đến lesson đầu tiên

| Input | Expected |
|-------|---------|
| `course_id` valid, free | HTTP 200 / redirect to lesson |
| `course_id` not exist | HTTP 404 |
| User đã enrolled | HTTP 409 / "Already enrolled" |

---

### TC-001-B: Paid course — access without payment

**Given:** Course có `price > 0`, user chưa có Order completed  
**When:** User truy cập lesson URL trực tiếp  
**Then:** Redirect về checkout page, HTTP 403

---

### TC-001-C: Paid course — access after payment

**Given:** Order với status `completed` tồn tại cho user + course  
**When:** User click "Start Learning"  
**Then:** `user_course` record tạo ra, access granted tới lesson

---

### TC-001-D: Duplicate enrollment guard

**Given:** `user_course` đã tồn tại (active)  
**When:** Enroll request gửi lần 2  
**Then:** Không tạo duplicate record, trả về existing enrollment state

## Kiểu Assertion

- DB assertion: kiểm tra `user_course` table sau mỗi action
- HTTP assertion: status code + redirect URL
- No duplicate: `SELECT COUNT(*) WHERE uid = X AND course_id = Y` = 1

## Công cụ

- Integration: PHPUnit KernelTestBase
- E2E: Playwright (E2E-002, E2E-003)
