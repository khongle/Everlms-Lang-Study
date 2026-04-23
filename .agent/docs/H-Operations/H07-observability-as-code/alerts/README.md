# H07 — Alerts

> Alert definitions versioned as code (PROPOSED — chưa có Grafana/Prometheus).
> Mỗi file là một alert spec. Không có alert nào active khi chưa có monitoring stack.
>
> **Trạng thái:** PROPOSED. Implement trước CP6 (release).

---

## Alert Index

| File | Alert ID | Severity | Signal | Runbook |
|------|----------|----------|--------|---------|
| [01-error-rate.md](01-error-rate.md) | alert-everlms-error-rate | critical | HTTP 5xx > 1% / 5min | RUN-001 |
| [02-payment-webhook-fail.md](02-payment-webhook-fail.md) | alert-everlms-webhook-fail | high | Webhook signature failures spike | RUN-004 |
| [03-quiz-grading-latency.md](03-quiz-grading-latency.md) | alert-everlms-quiz-queue-depth | med | Essay grading queue depth > 50 | RUN-006 |

---

## Alert Severity Rules

| Severity | Pager? | Response SLA | Ví dụ |
|----------|--------|-------------|-------|
| critical | Có (24/7) | < 15 phút ack | Site down, data loss |
| high | Có (business hours) | < 1 giờ ack | Payment fail, major feature vỡ |
| med | Không | Triage trong 1 ngày | Queue buildup, latency degraded |
| low | Không | Weekly review | Non-critical warnings |

---

## Alert Quality Rules

1. **Mọi alert phải có runbook** — không có runbook = alert invalid, không được deploy
2. **Không flapping** — nếu alert fire > 10 lần/ngày mà không có incident → disable và viết lại
3. **Actionable < 5 phút** — on-call phải biết làm gì ngay khi nhận alert
4. **Deduplicated** — không double-page cùng root cause
5. **Suppress khi deploy** — tắt alert trong cửa sổ deploy để tránh noise

---

## Alert Schema (YAML template)

```yaml
id: alert-{service}-{short-name}
severity: [critical | high | med | low]
signal: |
  # PromQL expression
for: [duration]           # ví dụ: 5m
team: sa
runbook: H-Operations/H02-runbooks/RUN-{NNN}-{slug}.md
suppress_during: [deploy | known-outage]
notification:
  channels: [email | slack | pagerduty]
  auto_resolve: true
labels:
  service: everlms
  env: [staging | production]
```

---

## Channels (PROPOSED)

| Channel | Severity | Tool |
|---------|----------|------|
| Email | high, critical | SMTP / SendGrid |
| Slack #alerts | med, high, critical | Slack webhook |
| PagerDuty | critical (SEV1/SEV2) | PROPOSED khi team > 1 |

---

*Cập nhật lần cuối: 2026-04-16*
