# H06 — Capacity Model

> Capacity model, headroom targets, scale triggers, và cost projection cho EverLMS.
> Cập nhật: review monthly, rebaseline quarterly, spot-check trước mỗi release.
>
> **Trạng thái:** Pre-production. Tất cả số liệu là PROJECTED / ESTIMATED — chưa có live metrics.
> Rebaseline ngay sau khi cohort đầu tiên (~500 students) được onboard.

---

## Stack Reference

| Layer | Technology | Deployment hiện tại |
|-------|-----------|---------------------|
| Application | Drupal 11 + PHP 8.3 + PHP-FPM | Single node (DDEV local dev) |
| Database | MariaDB 10.11 | Single primary, không có replica |
| Cache | Drupal internal cache (DB-backed) | Redis: PROPOSED |
| Queue | Drupal Queue API (DB-backed) | Queue worker riêng: PROPOSED |
| CDN / Static | — | PROPOSED |

---

## Workload Drivers (PROJECTED — Cohort 1)

| Driver | Baseline estimate | Cohort 1 peak | 90d projection |
|--------|-----------------|--------------|----------------|
| Tổng students | 0 | ~500 | ~500 |
| DAU (daily active users) | 0 | ~200 | ~200 |
| Concurrent users (bình thường) | 0 | ~20–30 | ~30 |
| Concurrent quiz takers (peak) | 0 | ~50 | ~50 |
| Requests/phút (peak) | 0 | ~600 rpm | ~800 rpm |
| Essay grading queue depth (peak) | 0 | ~20–30 | ~40 |
| DB size | ~50 MB | ~500 MB | ~1 GB |

> Giả định: 50 concurrent quiz takers × avg 12 requests/quiz session = ~600 rpm peak.

---

## Resource Capacity (Pre-production Baseline)

> PROPOSED — dựa trên ước tính, chưa có dữ liệu thực.
> headroom = (allocated - peak_usage) / allocated × 100%
> Target: headroom ≥ 25% trước khi launch.

| Resource | Allocated (dự kiến) | Peak estimate | Headroom est. | Target | Status |
|----------|--------------------|---------------|---------------|--------|--------|
| PHP-FPM workers | 20 | ~15 (50 concurrent) | 25% | ≥ 25% | WATCH |
| CPU cores (server) | 4 vCPU | ~2.5 cores | 37% | ≥ 25% | OK |
| RAM | 8 GB | ~5 GB | 37% | ≥ 25% | OK |
| MariaDB connections | 100 | ~60 | 40% | ≥ 25% | OK |
| MariaDB storage | 20 GB | ~1 GB | 95% | ≥ 20% free | OK |
| Disk (files/uploads) | 50 GB | ~5 GB | 90% | ≥ 20% free | OK |
| Queue depth (lms_ai) | 500 | ~30–50 | 90% | ≥ 30% | OK |

---

## Ratio Model (Request → Resources)

```
50 concurrent quiz takers
  → ~15 PHP-FPM workers active
  → ~60 MariaDB connections (mix read/write)
  → ~600 rpm (12 req/session × 50)

1 AI course generation job
  → 1 queue worker
  → ~1 API call/sec to OpenAI/Gemini
  → ~30–120 sec completion time

1 certificate PDF generation
  → ~2–5 sec CPU burst
  → ~500 KB disk
```

> Rebaseline ratio khi code thay đổi query pattern > 20%.

---

## Headroom Guardrails

| Headroom % | Action | SLA |
|-----------|--------|-----|
| < 25% | Yellow alert — lên kế hoạch scale trong 1 sprint | Không urgent |
| < 15% | Red alert — scale trong tuần này | < 5 ngày |
| < 10% | SEV3 incident — scale ngay | < 24 giờ |
| Trend âm 3 tuần liên tiếp | Forced capacity review | Next sprint |

---

## Scale Triggers

### Automatic (PROPOSED — chưa có auto-scaling)

| Component | Metric | Scale-out trigger | Scale-in trigger |
|-----------|--------|-------------------|------------------|
| PHP-FPM | CPU > 70% × 5 phút | Tăng max_children | Giảm sau 15 phút idle |
| Queue worker | depth > 100 × 3 phút | Thêm worker process | depth < 20 × 10 phút |

### Manual (Phải có SA approval)

| Trigger | Action | Cần |
|---------|--------|-----|
| DB connections > 80% | Vertical scale MariaDB | Maintenance window |
| RAM headroom < 15% | Upgrade RAM / thêm node | SA approval |
| Disk > 80% | Thêm volume hoặc archive | SA approval |
| Cohort 2+ launch (>500 more users) | DB replica + Redis cache | ADR trong G01 |

---

## Cost Projection (PROPOSED — chưa có production billing)

| Component | Ước tính monthly | 90d projection | Notes |
|-----------|-----------------|----------------|-------|
| VPS / Cloud compute | ~$30–50 | ~$50–80 | Phụ thuộc provider |
| DB (nếu managed) | ~$20–40 | ~$40 | MariaDB managed |
| Storage | ~$5 | ~$10 | Tăng theo uploads |
| CDN (PROPOSED) | — | ~$5–10 | Khi implement |
| OpenAI / Gemini API | ~$5–20 | ~$20 | Phụ thuộc usage |
| **Tổng ước tính** | **~$60–110** | **~$115–160** | |

> Budget alerts: MoM tăng > 20% → investigate; > $200/mo → SA + PM review.

---

## Optimization Backlog

> Tracked thêm trong H04 với `type: cost-optimization`.

| Item | Savings est. | Effort | Priority |
|------|-------------|--------|----------|
| Implement Redis cache (giảm DB load) | 30% DB connections | 2 ngày | High |
| CDN cho static assets | 20% PHP-FPM load | 1 ngày | Medium |
| Aggressive Drupal page cache | 40% rpm reduction | 1 ngày | High |
| DB read replica khi > 1000 users | Scale DB headroom | 1 sprint | Medium |

---

## Review Cadence

| Loại | Tần suất | Thời gian | Nội dung |
|------|----------|-----------|----------|
| Spot-check | Trước mỗi release | 15 phút | Load delta, headroom đủ không |
| Monthly review | Hàng tháng | 30 phút | Headroom per component, cost actuals |
| Quarterly rebaseline | Mỗi quý | 1–2 giờ | Refresh workload drivers, ratio model, 90d projection |

---

*Cập nhật lần cuối: 2026-04-16 — Pre-production baseline. Rebaseline sau cohort 1 launch.*
