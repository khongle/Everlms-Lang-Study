# SLO: Quiz Submission Success (No Data Loss)

> **PROPOSED** — Chưa có live measurement.
> Target 99.9% phản ánh zero-tolerance với data loss cho submission.

---

## Metadata

| Field | Value |
|-------|-------|
| **SLO ID** | slo-everlms-quiz-submission |
| **Service** | EverLMS — Quiz & Assessment |
| **Owner** | SA |
| **Created** | 2026-04-16 |
| **Status** | PROPOSED |
| **ADR** | Chưa có — tạo G01 ADR khi finalize |

---

## SLO Definition (YAML)

```yaml
id: slo-everlms-quiz-submission
service: everlms-quiz
sli: |
  # "Good" event = quiz submission được persist thành công vào DB
  # Không data loss, không duplicate, không silent fail
  good_events:
    filter: 'event="quiz_submission_accepted" AND persisted=true'
  total_events:
    filter: 'event="quiz_submission_attempted"'

objective: 99.9    # percent — SA approved
window: 30d        # rolling
error_budget: 0.1% # = 100 - 99.9

burn_rate_alerts:
  - window: 1h
    threshold: 14.4   # critical — 2% budget in 1h
    alert: alert-everlms-quiz-queue-depth
  - window: 6h
    threshold: 6      # high

owner: sa
```

---

## Tại sao 99.9%?

| Lý do | Chi tiết |
|-------|---------|
| Data integrity critical | Quiz submission = kết quả học tập, không thể mất |
| Không chấp nhận silent fail | Student submit → không nhận confirmation = catastrophic UX |
| Thấp hơn 99.99% vì | Vẫn là pre-production, chưa có retry queue full |
| 99.9% allowance | 43 phút downtime/tháng cho quiz endpoint |

---

## Error Budget Calculation

```
Window: 30 days = 43,200 minutes
Objective: 99.9%
Error budget: 0.1% = 43.2 minutes / 30d

Equivalent: nếu 1000 submissions/ngày
  → 99.9% = 1 failed submission / 1000 allowed
  → 1 phút downtime = ~0.7 lost submissions (tại ~50 concurrent × 12 req/session)
```

---

## SLI Measurement Points

| Endpoint | Mô tả | Included? |
|----------|-------|-----------|
| `POST /api/v1/quiz/{id}/submit` | Quiz submission API | Yes — primary |
| `POST /api/v1/quiz/{id}/answer` | Answer auto-save | Yes — secondary |
| `GET /quiz/{id}/result` | Result retrieval | No (read-only) |
| `POST /api/v1/progress/lesson` | Lesson completion | No (SLO 01 covers) |

---

## "Bad Event" Definition

Một submission được tính là **BAD** (lỗi) khi:

| Condition | Lý do |
|-----------|-------|
| HTTP 5xx trả về | Server error khi submit |
| HTTP 2xx nhưng không có DB record | Silent fail — tệ nhất |
| Duplicate submission không được dedup | Data integrity issue |
| Submission timeout (> 10s) | User experience unacceptable |

> Lưu ý: HTTP 4xx (client error như invalid data) KHÔNG phải bad event — đó là user error.

---

## Data Loss Prevention Measures

| Measure | Status | Notes |
|---------|--------|-------|
| DB transaction cho submission | Cần verify | lms_quiz module |
| Idempotency key per submission | PROPOSED | Tránh duplicate nếu retry |
| Submission validation trước persist | Cần verify | API input validation (RISK-001) |
| Retry logic nếu DB fail | PROPOSED | Queue-based fallback |

> RISK-001 trong A10: API validation gaps ảnh hưởng trực tiếp SLO này.

---

## Burn Rate Alert Actions

### Critical (14.4x burn rate / 1h)

1. Check quiz submission errors: `drush watchdog:show --type=lms_quiz --severity=error`
2. Check DB transactions đang lock: `SHOW PROCESSLIST;` trong MariaDB
3. Nếu race condition (RISK-002): rollback và implement queue-based submission
4. Log H01 SEV2 nếu data loss confirmed

### High (6x burn rate / 6h)

1. Check essay grading queue (`03-quiz-grading-latency` alert)
2. Check lms_quiz API error logs
3. Triage trong business hours

---

## Race Condition Risk

> RISK-002 trong A10: Progress race condition trên concurrent requests.
> Nếu 50 concurrent quiz takers submit cùng lúc → DB write contention.
>
> Mitigation (chưa implement):
> - DB transaction với appropriate isolation level
> - Optimistic locking hoặc queue-based cascade
> - Xem DEC-003 (Open Decision trong A10)

---

## Monthly SLO Review Checklist

```
[ ] Total quiz submissions trong tháng: ____
[ ] Failed submissions: ____ (%)
[ ] Error budget consumed: ____%
[ ] Data loss events confirmed: ____ (phải = 0)
[ ] Race condition incidents: ____
[ ] Action items:
```

---

## Related

- Dashboard: `dashboards/01-app-latency.md` (submission endpoint latency)
- Alert: `alerts/03-quiz-grading-latency.md`
- Runbook: `H02-runbooks/RUN-006-stuck-progress-cascade.md`
- SLO 1: `slos/01-course-detail-availability.md`
- Risk: RISK-002 (A-Project-Information/A03-project-context.md)
