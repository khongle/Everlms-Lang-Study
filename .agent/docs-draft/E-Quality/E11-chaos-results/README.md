# E11 — Chaos Results Index

> Kết quả của các chaos engineering drill. Một file mỗi drill.
> Chaos drill giúp verify khả năng phục hồi của hệ thống khi gặp failure scenarios.

## Registry

| File | Drill Type | Scope | Date | Outcome |
|------|-----------|-------|------|---------|
| *(chưa có drill nào)* | — | — | — | — |

## Trạng thái

Dự án EverLMS hiện đang ở giai đoạn **development (CP3 → CP4)**. Chưa có chaos drill nào được thực hiện vì:

1. Hệ thống chưa có môi trường staging ổn định
2. G-HARDENING (U-057–U-070) chưa hoàn thành — nhiều components vẫn ở trạng thái stub
3. Payment gateway thực tế chưa được implement (ISSUE-004)

Chaos drill sẽ được lên kế hoạch **sau CP5** (pre-release gate) khi hệ thống có đủ production-readiness.

## Drill Types Dự kiến

| Drill | Mục đích | Prerequisite |
|-------|---------|--------------|
| DB connection drop | Verify graceful degradation | U-067 (Error handling) done |
| AI provider timeout | Verify fallback provider | U-063 (AI rollback) done |
| Payment webhook flood | Verify idempotency | U-062 (Idempotency keys) done |
| Concurrent quiz submissions | Verify no data corruption | U-059 (Race condition fix) done |
| Cache flush under load | Verify cache rebuild | U-069 (Cache strategy) done |

## Naming Convention

```
CHAOS-{NNN}-{slug}.md
Ví dụ: CHAOS-001-db-connection-drop.md
```

## Runbook Template

Tham khảo: `00-Guidelines/A09-operational-templates/05-chaos-runbook.md`

## Links

- Chaos runbook template: A09-operational-templates/05-chaos-runbook.md
- Incident log: H01-incident-log/
- NFR tracking (availability): E10-nfr-tracking.md
