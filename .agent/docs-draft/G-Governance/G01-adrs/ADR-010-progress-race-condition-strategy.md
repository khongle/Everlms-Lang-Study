# G01-ADR-010: Progress Race Condition Strategy (DB Row Lock vs Queue Worker)

**Status:** PROPOSED
**Date:** 2026-04-16
**Decision Makers:** SA

**Open decision:** DEC-003 — chưa resolve, ADR này document alternatives để SA quyết định.
**Related:** ADR-008 (cascade strategy), RISK-002/RISK-003 in A10.

---

## Context

**Vấn đề:** `ProgressApiController::markComplete()` xử lý cascade update đồng bộ (sync) trực tiếp trong HTTP request. Khi học viên gửi nhiều requests đồng thời (multi-tab, double-click, retry sau network error), có thể xảy ra race condition:

**Kịch bản race condition:**

```
T1: Request A reads lms_user_course: progress = 80%
T2: Request B reads lms_user_course: progress = 80%
T3: Request A counts completed lessons: 9/10
T4: Request B counts completed lessons: 9/10
T5: Request A writes: progress = 90%
T6: Request B writes: progress = 90%  ← Lost update! Harusnya tính lại với 10/10
```

Nếu lesson cuối được mark complete bởi hai requests đồng thời:
- Cả hai đọc progress = 90%
- Cả hai tính completed = 10/10 → 100%
- Cả hai dispatch `CourseCompletedEvent`
- **Certificate được cấp 2 lần!**

**Tại sao xảy ra:**
- Không có locking giữa read và write
- `lms_user_lesson` upsert có check `field_status !== 'completed'` nhưng không atomic
- MariaDB default isolation: REPEATABLE READ — không ngăn lost update nếu không dùng `SELECT FOR UPDATE`

**Scale context:**
- LMS nhỏ (<1000 concurrent users): race condition xảy ra hiếm nhưng vẫn xảy ra
- Multi-tab học là use case phổ biến trong online learning

---

## Decision (PROPOSED — chờ SA approval)

Chưa có quyết định cuối. ADR này trình bày ba phương án.

---

## Alternative Analysis

### Option A: Database Row-Level Lock (SELECT FOR UPDATE)

Wrap toàn bộ cascade trong transaction với pessimistic lock trên `lms_user_course`:

```php
public function markLessonComplete(int $uid, int $course_id, int $lesson_id): void {
  $txn = $this->database->startTransaction();
  try {
    // Lock the enrollment row
    $enrollment = $this->database->query(
      'SELECT id FROM {lms_user_course} WHERE uid = :uid AND field_course_target_id = :cid FOR UPDATE',
      [':uid' => $uid, ':cid' => $course_id]
    )->fetchField();

    // Now safe to read, compute, and write
    $this->upsertLessonProgress($uid, $lesson_id);
    $progress = $this->calculateCourseProgress($uid, $course_id);
    $this->updateEnrollmentProgress($uid, $course_id, $progress);

    if ($progress >= 100) {
      $this->dispatchCourseCompleted($uid, $course_id);
    }
    // Transaction commits, lock released
  }
  catch (\Exception $e) {
    $txn->rollBack();
    throw $e;
  }
}
```

**Pros:**
- Strong consistency: chỉ một request tại một thời điểm có thể update enrollment của user
- Đơn giản hơn về infrastructure — không cần queue worker
- Kết quả immediate: progress hiển thị ngay sau HTTP response
- MariaDB 10.11 hỗ trợ tốt `SELECT FOR UPDATE`

**Cons:**
- **Deadlock risk:** Nếu hai transactions lock rows theo thứ tự ngược nhau → deadlock; phải implement retry logic
- **Lock wait timeout:** Nếu transaction chậm → concurrent request bị block (MariaDB default: 50s timeout)
- **Throughput giảm:** Serialized writes trên cùng user/course; không phù hợp nếu user có nhiều devices
- **Chỉ bảo vệ trên cùng DB server:** Nếu sau này scale horizontal → cần distributed lock

**Estimated effort:** ~8 giờ (service extraction + lock implementation + retry logic + tests)

---

### Option B: Queue Worker (Async Cascade)

Cascade không chạy trong HTTP request. Thay vào đó:

```
HTTP Request (fast):
  1. Upsert lms_user_lesson (atomic insert/update)
  2. Enqueue job: LessonProgressJob { uid, lesson_id, course_id }
  3. Return 202 Accepted immediately

Background Worker (async):
  4. Dequeue LessonProgressJob
  5. Acquire Redis/DB lock per (uid, course_id)
  6. Calculate cascade
  7. Update lms_user_course
  8. Dispatch CourseCompletedEvent if 100%
```

Drupal Queue API + `QueueWorkerInterface`:
```php
#[QueueWorker(
  id: 'lms_lesson_progress',
  title: 'Lesson Progress Cascade',
  cron: ['time' => 60],
)]
final class LessonProgressWorker implements QueueWorkerInterface {
  public function processItem(mixed $data): void {
    // cascade logic with distributed lock
  }
}
```

`lms_ai` đã có `QueueWorker/` directory — pattern quen thuộc.

**Pros:**
- HTTP response nhanh (sub-50ms) — tốt cho UX
- Queue naturally serializes work per item — giảm concurrent write conflict
- Scalable: worker có thể scale độc lập
- Retry built-in: Drupal Queue re-queues failed jobs

**Cons:**
- **Eventual consistency:** Progress không update ngay — học viên có thể thấy 80% trong 1-2 giây sau khi complete lesson
- **UX complexity:** Frontend phải poll hoặc dùng WebSocket để biết khi progress update
- **Cron dependency:** Drupal cron phải chạy thường xuyên (mỗi 1 phút) để process queue
- **Debugging phức tạp:** Trace từ HTTP request qua queue đến worker khó hơn sync flow
- **Infrastructure:** Cần cron job + queue backend (DB queue đủ, nhưng Redis queue tốt hơn)

**Estimated effort:** ~20 giờ (queue worker + frontend polling + cron setup + tests)

---

### Option C: Optimistic Locking với Retry (Middle Ground)

Thêm `version` field vào `lms_user_course`. Update chỉ thành công nếu version match:

```sql
UPDATE lms_user_course
SET field_progress = :new_progress, version = version + 1
WHERE uid = :uid AND field_course_target_id = :cid AND version = :expected_version
```

Nếu `affected_rows = 0` → version đã thay đổi → retry (tối đa 3 lần).

**Pros:** Không blocking, không queue overhead, fails fast
**Cons:** Cần schema migration (thêm `version` column), retry logic phức tạp, vẫn có eventual inconsistency trong burst
**Estimated effort:** ~12 giờ

---

## Agent Recommendation

**Option A (DB Row Lock)** cho v1 vì:
- EverLMS không expect high concurrency (LMS nhỏ)
- Strong consistency quan trọng: tránh duplicate certificate
- Không cần thêm infrastructure (cron worker, queue backend)
- `lms_progress` có thể refactor thành `ProgressCascadeService` (ADR-008) kèm lock

**Option B** xem xét nếu:
- Scale lên 10,000+ concurrent users
- UX team yêu cầu sub-50ms response cho progress API
- Redis đã có trong stack (hiện tại: Redis cho cache)

---

## Consequences (nếu Option A được chọn)

### Positive
- Race condition được loại bỏ: chỉ một cascade per (uid, course_id) tại một thời điểm
- Certificate duplicate risk giảm về 0
- Không thay đổi frontend

### Negative
- Concurrent requests từ cùng user bị serialize (queue behind lock) — acceptable cho LMS
- Cần retry logic cho deadlock case
- Lock scope tổng quát cần design cẩn thận

### Risks
- Deadlock nếu cascade gọi thêm service có lock riêng — phải document lock ordering
- MariaDB lock wait timeout cần tune theo traffic pattern thực tế

---

## Related

- **Open decision:** DEC-003 in A-Project-Information/A03-project-context.md
- **Risks:** RISK-002, RISK-003 in A-Project-Information/A03; RISK-003 in G02
- **Related ADR:** ADR-008 (Progress cascade strategy)
- **Implementation:** `web/modules/custom/lms_progress/src/Controller/ProgressApiController.php`
- **Queue pattern reference:** `web/modules/custom/lms_ai/src/Plugin/QueueWorker/`
- **SA action required:** Chọn Option A, B, hoặc C trước TASK-017 execution
