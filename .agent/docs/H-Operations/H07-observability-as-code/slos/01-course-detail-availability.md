# SLO: Course Detail Availability

> **PROPOSED** — Chưa có live measurement. Target đã được SA draft, pending final approval.

---

## Metadata

| Field | Value |
|-------|-------|
| **SLO ID** | slo-everlms-course-detail |
| **Service** | EverLMS — Course viewing |
| **Owner** | SA |
| **Created** | 2026-04-16 |
| **Status** | PROPOSED |
| **ADR** | Chưa có — tạo G01 ADR khi finalize |

---

## SLO Definition (YAML)

```yaml
id: slo-everlms-course-detail
service: everlms-course
sli: |
  # "Good" request = HTTP 2xx response trong <= 2s
  # cho endpoint /course/{id} và /course/{id}/learn
  good_requests:
    filter: 'job="everlms" AND path=~"/course/.*" AND status=~"2.." AND duration_ms <= 2000'
  total_requests:
    filter: 'job="everlms" AND path=~"/course/.*"'

objective: 99.5    # percent — SA approved
window: 30d        # rolling
error_budget: 0.5% # = 100 - 99.5

burn_rate_alerts:
  - window: 1h
    threshold: 14.4   # 2% budget in 1h — fire critical
    alert: alert-everlms-error-rate
  - window: 6h
    threshold: 6      # 5% budget in 6h — fire high
    alert: alert-everlms-error-rate

owner: sa
```

---

## Tại sao 99.5%?

| Lý do | Chi tiết |
|-------|---------|
| Pre-production, single node | Không có HA, không có replica → 99.9% không realistic |
| Cohort 1 context | ~500 students, không phải SaaS công cộng |
| Downtime allowance | 99.5% × 30d = 3.6 giờ/tháng downtime allowed |
| Upgrade path | Sau khi có DB replica + Redis → target 99.9% |

---

## Error Budget Calculation

```
Window: 30 days = 43,200 minutes
Objective: 99.5%
Error budget: 0.5% = 216 minutes / 30d

Nếu site down 1 giờ = 60 phút = 28% budget consumed
Nếu site down 3.6 giờ = 216 phút = 100% budget exhausted
```

---

## SLI Measurement Points

| Endpoint | Mô tả | Included? |
|----------|-------|-----------|
| `GET /course/{id}` | Course detail page | Yes |
| `GET /course/{id}/learn` | Course learning view | Yes |
| `GET /course/{id}/lessons` | Lesson list | Yes |
| `POST /api/v1/progress/*` | Progress API | No (SLO riêng) |
| `GET /admin/*` | Admin pages | No (excluded) |
| `GET /manage/*` | Admin manage pages | No (excluded) |

---

## Burn Rate Alert Actions

### Critical (14.4x burn rate / 1h window)

1. Check `01-error-rate` alert — có đang fire không?
2. Check recent deploy
3. Execute triage per RUN-001 (cache rebuild)
4. Nếu không resolve trong 15 phút → log H01 SEV2

### High (6x burn rate / 6h window)

1. Review dashboard `01-app-latency`
2. Check DB queries `02-db-queries`
3. Plan action cho business hours

---

## Monthly SLO Review Checklist

```
[ ] Tổng error budget consumed trong tháng: ____%
[ ] Incidents ảnh hưởng SLO: ____
[ ] Longest outage: ____ phút
[ ] Budget status: Healthy / Caution / Warning / Exhausted
[ ] Action items nếu cần:
```

---

## Evolution Path

| Giai đoạn | Target | Prerequisite |
|-----------|--------|-------------|
| Cohort 1 (current plan) | 99.5% | Single node |
| Cohort 2+ | 99.9% | DB replica + Redis cache |
| Scale-out | 99.95% | Load balancer + multi-node |

---

## Related

- Dashboard: `dashboards/01-app-latency.md`
- Alert: `alerts/01-error-rate.md`
- SLO 2: `slos/02-quiz-submission-success.md`
