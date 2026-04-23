# E09 — Impact Analysis Index

> Lưu trữ phân tích tác động (impact analysis) cho từng thay đổi quan trọng.
> Một file mỗi change. Chỉ dùng cho maintenance và high-risk changes.
>
> **Không dùng E09 cho retrospectives** — xem E07.

## File Registry

| File | Change Type | Scope | Status | Owner | Date |
|------|-------------|-------|--------|-------|------|
| *(chưa có)* | — | — | — | — | — |

## Trạng thái

Dự án chưa có change nào vượt qua giai đoạn development yêu cầu formal impact analysis. Thư mục này sẽ được populate khi:

- Bắt đầu bất kỳ HOTFIX nào trên staging/production
- Refactor ảnh hưởng đến nhiều module (cross-group changes)
- Dependency upgrade có breaking changes

## Khi nào tạo Impact Analysis

| Trigger | Required? |
|---------|-----------|
| HOTFIX trên production | Bắt buộc |
| ENHANCEMENT cross-group | Nên có |
| Dependency major version | Nên có |
| Refactor trong G-HARDENING | Xem xét theo case |
| Bug fix trong một unit | Không cần |

## Template

Sử dụng template tại: `00-Guidelines/A09-operational-templates/04-impact.md`

## Naming Convention

```
IMP-{NNN}-{slug}.md
Ví dụ: IMP-001-progress-lock-strategy.md
```

## Links

- Template: A09-operational-templates/04-impact.md
- Change requests: G04-change-requests/
- Reverse tracing: D07-code-map/
- Risk register: G02-risk-register.md
