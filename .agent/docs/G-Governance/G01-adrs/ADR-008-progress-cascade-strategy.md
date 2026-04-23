# G01-ADR-008: Progress Cascade Strategy (Lesson → Chapter → Course)

**Status:** ACCEPTED
**Date:** 2026-03-08
**Decision Makers:** SA, Agent_Architect

**Open question:** DEC-003 (race condition handling) — chưa resolve, xem ADR-010.

---

## Context

Khi học viên hoàn thành một lesson, hệ thống cần cascade update progress theo hierarchy:

```
Lesson completed
  → Chapter progress = (completed_lessons / total_lessons) × 100%
  → Course progress  = (completed_lessons_all_chapters / total_lessons_all) × 100%
  → Nếu Course progress = 100% → mark course "completed" → trigger certificate
```

**Entities liên quan:**
- `lms_user_lesson` — tracking lesson-level progress (field_status, field_completed_at)
- `lms_user_course` — tracking course-level enrollment + progress (field_progress, field_status)
- Chapter-level progress tracking: hiện tại chưa có entity riêng, tính inline

**Vấn đề kỹ thuật:**
- Cascade tính toán qua nhiều entity → nhiều database queries
- Concurrent requests (học viên multi-tab, retry) → race condition
- Mỗi request chạy cascade độc lập → kết quả không nhất quán

**Tình trạng hiện tại:**

`ProgressApiController::markComplete()` implement trực tiếp trong controller:
1. Upsert `lms_user_lesson` (completed)
2. Scan toàn bộ lessons của course → đếm completed
3. Update `field_progress` trên `lms_user_course`
4. Nếu progress = 100% → set status = completed

Logic này đang nằm **trong controller** — vi phạm separation of concerns, khó test, và thiếu transaction wrapper.

---

## Decision

**Cascade via dedicated service:** Toàn bộ cascade logic phải được extract ra `ProgressCascadeService` (hoặc `LessonProgressManager`) — controller chỉ validate input và gọi service.

**Cascade flow chuẩn:**
```php
class ProgressCascadeService {
  public function markLessonComplete(int $uid, int $lesson_id, int $course_id): ProgressResult {
    // 1. Upsert lms_user_lesson
    // 2. Count completed lessons per chapter
    // 3. Calculate course progress
    // 4. Upsert lms_user_course
    // 5. Dispatch CourseCompletedEvent if 100%
    // 6. Return ProgressResult DTO
  }
}
```

**Race condition mitigation:** TBD — xem DEC-003 / ADR-010 (DB row lock vs Queue worker).

**Interim mitigation (hiện tại):**
- Wrap toàn bộ cascade trong Drupal database transaction
- Không dùng optimistic lock (chưa implement)
- Idempotency: check `field_status !== 'completed'` trước khi write

---

## Rationale

**Tại sao cascade service riêng thay vì controller logic:**

1. **Testability:** Service có thể unit test với mock entity storage. Controller không.
2. **Reusability:** Cascade cần trigger từ nhiều nơi: quiz completion → lesson complete → cascade; SCORM completion → cascade; admin manual completion.
3. **Transaction boundary:** Service có thể wrap toàn bộ trong `\Drupal::database()->startTransaction()`.
4. **Separation of concerns:** Controller xử lý HTTP input/output; service xử lý business logic.

**Tại sao cascade theo hierarchy thay vì event-driven:**

Event-driven (lesson_completed event → chapter listener → course listener) sẽ phức tạp hơn với distributed state. Sync cascade trong một transaction đơn giản hơn và nhất quán hơn cho v1.

---

### Options Considered

#### Option 1: Cascade Service (CHOSEN)
- **Pros:** Transaction-wrapped, testable, reusable, clear data flow
- **Cons:** Single transaction dài hơn → higher lock contention; N+1 query risk khi scan all lessons
- **Fit:** Phù hợp cho v1; có thể optimize sau

#### Option 2: Event-driven cascade (REJECTED)
- **Pros:** Decoupled, async-ready
- **Cons:** Phức tạp hơn nhiều, eventual consistency — progress có thể sai trong khoảng thời gian ngắn, khó debug
- **Reason rejected:** Over-engineering cho v1; cascade là simple hierarchy

#### Option 3: Queue worker cascade (DEFERRED)
- **Pros:** Async, không block HTTP response, giải quyết race condition
- **Cons:** Eventual consistency — progress không update ngay sau click "Complete"
- **Status:** Đang xem xét trong DEC-003 / ADR-010 như giải pháp race condition

---

## Consequences

### Positive
- Controller giảm từ 100+ dòng xuống còn ~20 dòng
- Cascade logic có thể unit test
- Transaction wrapper giảm partial-write risk
- `CourseCompletedEvent` dispatch từ service → certificate module listen

### Negative
- **Chưa implement** `ProgressCascadeService` — hiện tại logic vẫn nằm trong `ProgressApiController`
- N+1 query: với course nhiều lessons, mỗi `markComplete` chạy query count per lesson
- Không có optimistic lock → race condition vẫn là rủi ro (RISK-002 pattern, xem DEC-003)

### Risks
- **RISK-003** (A10): Race condition trên concurrent requests — DEC-003 chưa resolve
- N+1 query trên large course (100+ lessons): phải batch query hoặc cache chapter lesson counts
- Nếu `CourseCompletedEvent` dispatch fail trong transaction → certificate không được cấp

---

## Related

- **Design docs:** C01-architecture-decision.md
- **Open decision:** DEC-003 (race condition handling) — xem ADR-010
- **Implementation:** `web/modules/custom/lms_progress/src/Controller/ProgressApiController.php` (current), planned `ProgressCascadeService`
- **Other ADRs:** ADR-010 (race condition strategy — Proposed)
- **Risks:** RISK-003 in A10, RISK-002 pattern in G02
- **Module:** `lms_progress`
