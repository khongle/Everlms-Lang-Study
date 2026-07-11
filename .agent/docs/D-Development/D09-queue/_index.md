# D09-queue - Execution Queue (dependency-ordered waves)

Các wave được sắp theo dependency cho **phần việc còn mở** (D04 todo), điều phối theo độ khó. Các module as-built đã được delivered (D03); queue này thúc đẩy gap-closure.

| Wave | Tasks | Rationale | Suggested model tier |
| ---- | ----- | --------- | -------------------- |
| W00 (scaffold) | [X00](../D04-tasks/done/X00-scaffold-bootstrap.md) scaffold bootstrap ([D10](../D10-scaffold-contract/_index.md)) | As-built scaffold đã done; DoR mở CP4 | low |
| W01 (security P0) | [E06S02T02](../D04-tasks/todo/E06S02T02-move-secrets-to-key.md) secrets→Key, [E06S02T01](../D04-tasks/todo/E06S02T01-add-payment-webhooks.md) webhooks | Block safe release; independent | high |
| W02 (correctness P1) | [E05S02T01](../D04-tasks/todo/E05S02T01-implement-badge-award-subscriber.md) badges, [E09S01T01](../D04-tasks/todo/E09S01T01-implement-email-channel.md) email | Feature gaps; email creds depend on the prior wave's secrets migration | medium |
| W03 (quality P1) | [X01](../D04-tasks/todo/X01-establish-test-baseline.md) test baseline | Enables G3 to bite; touches all modules | high |
| W04 (cleanup P2) | [E06S04T01](../D04-tasks/todo/E06S04T01-dedupe-paypal-gateway.md) paypal dedup, [E09S01T02](../D04-tasks/todo/E09S01T02-implement-push-channel.md) push, [E12S01T01](../D04-tasks/todo/E12S01T01-complete-analytics-kpis.md) analytics | Lower risk; after core | medium/low |

> Suy ra từ [D02–D04](../D02-epics/) + [G02 risk register](../../G-Governance/G02-risk-register/_index.md). Topo order: W01 → W02/W03 → W04.
>
> **Backlog (chưa ticket, chưa xếp wave):** R-007 (API rate limiting, gồm OTP) và phần còn lại của R-008 (Zoom/analytics chỉ mới partial) là P2 defer — theo dõi ở [G02](../../G-Governance/G02-risk-register/_index.md), sẽ tạo card khi ưu tiên. "Derived from G02" nghĩa là mọi risk P0/P1 đều có wave; risk P2 defer được ghi nhận là backlog, không phải bỏ sót.
