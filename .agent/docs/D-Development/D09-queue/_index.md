# D09-queue - Execution Queue (dependency-ordered waves)

Dependency-ordered waves for the **remaining open work** (D04 todo), routed by difficulty. As-built modules are already delivered (D03); this queue drives gap-closure.

| Wave | Tasks | Rationale | Suggested model tier |
| ---- | ----- | --------- | -------------------- |
| W01 (security P0) | [E06S02T02](../D04-tasks/todo/E06S02T02-move-secrets-to-key.md) secrets→Key, [E06S02T01](../D04-tasks/todo/E06S02T01-add-payment-webhooks.md) webhooks | Block safe release; independent | high |
| W02 (correctness P1) | [E05S02T01](../D04-tasks/todo/E05S02T01-implement-badge-award-subscriber.md) badges, [E09S01T01](../D04-tasks/todo/E09S01T01-implement-email-channel.md) email | Feature gaps; depend on W01 secrets for email creds | medium |
| W03 (quality P1) | [X01](../D04-tasks/todo/X01-establish-test-baseline.md) test baseline | Enables G3 to bite; touches all modules | high |
| W04 (cleanup P2) | [E06S04T01](../D04-tasks/todo/E06S04T01-dedupe-paypal-gateway.md) paypal dedup, [E09S01T02](../D04-tasks/todo/E09S01T02-implement-push-channel.md) push, [E12S01T01](../D04-tasks/todo/E12S01T01-complete-analytics-kpis.md) analytics | Lower risk; after core | medium/low |

> Derived from [D02–D04](../D02-epics/) + [G02 risk register](../../G-Governance/G02-risk-register/_index.md). Topo order: W01 → W02/W03 → W04.
