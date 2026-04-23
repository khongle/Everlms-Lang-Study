# H07 — SLOs (Service Level Objectives)

> SLO definitions versioned as code (PROPOSED — chưa có live measurement).
> Tối đa 3 SLOs — không overmodel.
> Target được SA approve trước khi implement.
>
> **Trạng thái:** PROPOSED. Cần SA sign-off trước CP6.

---

## SLO Index

| File | SLO ID | Service | Target | Window | Status |
|------|--------|---------|--------|--------|--------|
| [01-course-detail-availability.md](01-course-detail-availability.md) | slo-everlms-course-detail | Course viewing | 99.5% | 30d rolling | PROPOSED |
| [02-quiz-submission-success.md](02-quiz-submission-success.md) | slo-everlms-quiz-submission | Quiz submission | 99.9% | 30d rolling | PROPOSED |

---

## Error Budget Policy

| Budget state | Định nghĩa | Action |
|-------------|-----------|--------|
| Healthy (< 50% consumed) | Plenty of budget | Ship freely |
| Caution (50–80% consumed) | Getting tight | SA attention, slow risky releases |
| Warning (80–100% consumed) | Nearly exhausted | No new risky feature releases |
| Exhausted (> 100%) | Budget gone | Feature freeze, reliability sprint |

> Budget reset với rolling window, không reset theo incident.

---

## SLO Schema (YAML)

```yaml
id: slo-{service}-{short-name}
service: {service-id}
sli: |
  # Definition of "good" request
objective: 99.5      # percent
window: 30d          # rolling
error_budget: 0.5%   # = 100 - objective
burn_rate_alerts:
  - window: 1h
    threshold: 14.4  # consuming 2% budget in 1h = critical
  - window: 6h
    threshold: 6     # consuming 5% budget in 6h = high
owner: sa
```

---

## Rules

1. SLO target phải được SA approve — không có engineering-only SLOs
2. Mỗi SLO vi phạm phải được review trong retrospective (E07)
3. Thay đổi SLO target cần ADR mới trong G01
4. SLO báo cáo monthly — không phải build-and-forget
5. Không có PII trong SLI queries

---

## Burn Rate Alerts Reference

| Window | Burn Rate | Meaning | Severity |
|--------|-----------|---------|----------|
| 1h | 14.4x | 2% of monthly budget in 1 hour | critical |
| 6h | 6x | 5% of monthly budget in 6 hours | high |
| 3d | 1x | Normal consumption | (no alert) |

---

*Cập nhật lần cuối: 2026-04-16*
