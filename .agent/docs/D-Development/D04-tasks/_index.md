# D04-tasks - Kanban Index

Task card theo status folder. **As-built modelling:** phần công việc module đã hoàn tất được biểu diễn bằng story `resolved`/`done` trong [D03](../D03-stories/_index.md) (không nhân đôi thành card); **D04 card theo dõi phần việc còn mở** (gap-closure cho các risk trong [G02](../../G-Governance/G02-risk-register/_index.md)). Card ID = `E##S##T##` (hoặc `X##` cross-cutting). Statuses: `backlog, todo, in-progress, resolved, in-review, blocked, closed, done`.

## Open tasks (todo)
| Task | Title | Story | Risk |
| ---- | ----- | ----- | ---- |
| [E06S02T01](todo/E06S02T01-add-payment-webhooks.md) | Payment webhooks + signature verify | E06S02 | R-002 (P0) |
| [E06S02T02](todo/E06S02T02-move-secrets-to-key.md) | Move secrets to Key/env | E06S02 | R-001 (P0) |
| [E05S02T01](todo/E05S02T01-implement-badge-award-subscriber.md) | Badge award subscriber | E05S02 | R-003 (P1) |
| [X01-establish-test-baseline](todo/X01-establish-test-baseline.md) | PHPUnit kernel/functional baseline | cross-cutting | R-006 (P1) |
| [E09S01T01](todo/E09S01T01-implement-email-channel.md) | Email notification channel | E09S01 | R-005 (P1) |
| [E09S01T02](todo/E09S01T02-implement-push-channel.md) | Push notification channel | E09S01 | R-005 (P2) |
| [E06S04T01](todo/E06S04T01-dedupe-paypal-gateway.md) | Remove duplicate lms_paypal | E06S04 | R-004 (P2) |
| [E12S01T01](todo/E12S01T01-complete-analytics-kpis.md) | Complete analytics KPIs | E12S01 | — (P2) |

## Other status folders
`backlog/`, `in-progress/`, `resolved/`, `in-review/`, `blocked/`, `closed/`, `done/` — hiện đang trống (công việc as-built nằm trong D03 stories). Việc mới đi theo luồng todo → in-progress → resolved → in-review → done (Phase F).

Mẫu card: [_template.md](_template.md).
