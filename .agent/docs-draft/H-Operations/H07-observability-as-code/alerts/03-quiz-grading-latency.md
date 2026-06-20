# Alert: Quiz/Essay Grading Queue Depth

> **PROPOSED** — Chưa active. Implement khi Drupal Queue metrics được expose.

---

## Metadata

| Field | Value |
|-------|-------|
| **Alert ID** | alert-everlms-quiz-queue-depth |
| **Severity** | med |
| **Owner** | SA |
| **Runbook** | `H-Operations/H02-runbooks/RUN-006-stuck-progress-cascade.md` |
| **Created** | 2026-04-16 |
| **Status** | PROPOSED |

---

## Alert Definition (YAML)

```yaml
id: alert-everlms-quiz-queue-depth
severity: med
for: 10m
team: sa
runbook: H-Operations/H02-runbooks/RUN-006-stuck-progress-cascade.md
suppress_during: []
notification:
  channels: [slack]
  auto_resolve: true
labels:
  service: everlms-quiz
  env: production

signal: |
  # Option A: Custom Drupal metric (PROPOSED)
  lms_queue_depth{queue="lms_essay_grading"} > 50

  # Option B: DB query (Drupal queue API — table: queue)
  # SELECT COUNT(*) FROM queue WHERE name = 'lms_essay_grading'
  #   AND created < UNIX_TIMESTAMP() - 300;  -- items đã > 5 phút chưa xử lý
```

---

## Trigger Condition

| Metric | Operator | Threshold | Window | Action |
|--------|----------|-----------|--------|--------|
| Essay grading queue depth | > | 50 items | 10 phút liên tục | Fire alert |

---

## Why This Threshold

- Essay grading dùng AI (lms_ai module) → async queue worker
- Normal processing: ~2–5 min/essay → queue drain rate ~10–15 items/min
- 50 items trong queue = ~3–5 phút backlog (acceptable với 1 worker)
- > 50 items sustained 10 phút = worker bị kẹt hoặc AI API quota vượt
- Cohort 1 peak: ~50 concurrent quiz takers → không phải tất cả là essay → threshold 50 reasonable

---

## Queue Context (lms_quiz + lms_ai)

| Queue name | Worker | Mô tả |
|------------|--------|-------|
| `lms_essay_grading` | `CourseContentGenerator` (tạm dùng) | AI-grade essay submissions |
| `lms_ai_course_generation` | `CourseContentGenerator` | Generate course content |

> Module: `web/modules/custom/lms_ai/src/Plugin/QueueWorker/CourseContentGenerator.php`
> Lưu ý: Essay grading queue worker có thể cần tách riêng khỏi course generation worker.

---

## Triage Steps (khi alert fire)

```
1. Kiểm tra queue depth hiện tại:
   drush sql:query "SELECT name, COUNT(*) as depth
     FROM queue GROUP BY name ORDER BY depth DESC;"

2. Kiểm tra worker có đang chạy không:
   drush queue:list
   drush watchdog:show --type=lms_ai --count=20

3. Nếu worker bị stuck — restart cron / queue worker:
   drush cron
   # Hoặc chạy thủ công:
   drush queue:run lms_essay_grading --time-limit=300

4. Nếu AI API quota vượt (OpenAI/Gemini):
   → Xem RUN-005 (key rotation hoặc quota check)
   → Tạm dừng essay grading, notify users bị delay

5. Nếu queue buildup do nhiều submissions đột biến:
   → Bình thường sau peak hour, monitor drain rate
   → Nếu không drain sau 30 phút → SEV3 incident

6. Nếu 1 item cụ thể bị stuck (poison pill):
   drush sql:query "SELECT item_id, data, expire, created
     FROM queue WHERE name = 'lms_essay_grading'
     ORDER BY created ASC LIMIT 5;"
   # Xóa item lỗi:
   drush sql:query "DELETE FROM queue WHERE item_id = {ITEM_ID};"
```

---

## Progress Cascade Link

- Essay grading xong → trigger progress update → cascade lên UserCourse
- Nếu queue kẹt → progress không cập nhật → xem RUN-006
- Nếu progress 100% nhưng certificate không generate → xem RUN-007

---

## Custom Metric (cần implement)

```php
// PROPOSED — thêm vào lms_quiz hoặc lms_ai module
// Sau mỗi submission vào queue:
\Drupal::service('lms_monitoring.gauge')
  ->set('lms_queue_depth', $queue->numberOfItems('lms_essay_grading'), [
    'queue' => 'lms_essay_grading',
  ]);
```

---

## SLO Link

- Ảnh hưởng gián tiếp đến `slos/02-quiz-submission-success.md`
- Grading queue backup không = data loss, nhưng ảnh hưởng user experience
- Nếu grading không hoàn thành trong 24h → cần manual intervention

---

## Related Alerts

| Alert | Quan hệ |
|-------|---------|
| `01-error-rate` | Worker crash có thể gây 5xx + queue backup |
| `05-ai-provider-key` (FUTURE) | AI quota vượt = queue không drain |
