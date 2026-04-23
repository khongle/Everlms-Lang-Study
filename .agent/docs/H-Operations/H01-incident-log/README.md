# H01 — Incident Log

> Ghi nhận mọi sự cố production với severity, timeline và resolution.
> Cập nhật ngay khi incident xảy ra. Review trong retrospective.
>
> **Trạng thái hiện tại:** Pre-production — chưa có incident nào được ghi nhận.

---

## Incident Severity Classification

> Canonical severities định nghĩa tại A05 §2.

| Severity | Tên | Tiêu chí | Ack SLA | Resolution SLA |
|----------|-----|----------|---------|----------------|
| SEV1 | Critical Outage | Service down hoàn toàn, data loss đang xảy ra, breach bảo mật đang active | 5 phút | 4 giờ |
| SEV2 | Major Degradation | Core features bị vỡ, >25% users bị ảnh hưởng, revenue-impacting | 15 phút | 8 giờ |
| SEV3 | Minor Issue | Non-core feature vỡ, <25% users, có workaround | 1 giờ | 24 giờ |
| SEV4 | Low Impact | Cosmetic, batch chậm, single-user issue | Next business day | Next sprint |

---

## Escalation Contacts

| Severity | Primary | Secondary | Kênh |
|----------|---------|-----------|------|
| SEV1 | SA (hainn.we@gmail.com) | — | Trực tiếp / phone |
| SEV2 | SA | — | Slack / email |
| SEV3 | Agent on-call | SA | Slack |
| SEV4 | Agent on-call | — | Slack |

> Ghi chú: Đây là dự án solo. Khi có team mở rộng, cập nhật bảng này theo A06-oncall-protocol.

---

## Incident Registry

| ID | Date | Severity | Summary | Status | RCA | Duration |
|----|------|----------|---------|--------|-----|----------|
| — | — | — | Chưa có incident (pre-production) | — | — | — |

---

## Incident Template

Mỗi incident tạo một file riêng: `INC-{NNN}-{slug}.md`

```markdown
### INC-{NNN}: {TITLE}

**Date:** {YYYY-MM-DD HH:MM UTC+7}
**Severity:** SEV{1|2|3|4}
**Status:** INVESTIGATING | IDENTIFIED | MITIGATED | RESOLVED
**Reporter:** {ai-monitor | user-report | manual-check}
**Responder:** {SA | Agent}

#### Timeline
| Time (UTC+7) | Event |
|-------------|-------|
| {HH:MM} | Phát hiện: {cách phát hiện} |
| {HH:MM} | Bắt đầu điều tra |
| {HH:MM} | Root cause xác định: {brief} |
| {HH:MM} | Fix áp dụng: {brief} |
| {HH:MM} | Incident resolved. Monitoring xác nhận recovery. |

#### Impact
- **Users bị ảnh hưởng:** {số hoặc phần trăm}
- **Features bị ảnh hưởng:** {danh sách}
- **Data impact:** {data loss/corruption hoặc "Không có"}

#### Root Cause Analysis
{Giải thích chi tiết nguyên nhân — code path, config, external factor.}

#### Resolution
{Cách fix. Tham chiếu A04 Flow 1 (HOTFIX) hoặc Flow 2 (BUG_FIX), E02 issue ID.}

#### Prevention
{Các biện pháp phòng ngừa. Tham chiếu H04, H07, E02.}

#### Runbook liên quan
{H02 runbook đã dùng để xử lý.}
```

---

## Naming & Filing Rules

- File name: `INC-{NNN}-{3-word-slug}.md` — ví dụ: `INC-001-cache-rebuild-loop.md`
- Đánh số tăng dần, không skip, không tái sử dụng ID
- Status `RESOLVED` phải có đầy đủ RCA trước khi đóng
- Severity SEV1/SEV2 bắt buộc có retrospective trong E07
- Tham chiếu chéo: H02 (runbook đã dùng), H04 (tech debt sinh ra), E02 (issue mở)

---

*Cập nhật lần cuối: 2026-04-16 — chưa có incident nào (pre-production)*
