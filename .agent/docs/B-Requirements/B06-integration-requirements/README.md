# B06 — Integration Requirements

> Roll-up index of all external integration definitions. One file per integration; this file is the dependency matrix and navigation entry point. Source of truth is each per-integration `INT-{NNN}.md` file.

Last updated: 2026-06-20

---

## Integration Index

| ID | File | Provider | Type | Direction | Module(s) | Status |
|----|------|----------|------|-----------|-----------|--------|
| INT-001 | [INT-001-stripe.md](./INT-001-stripe.md) | Stripe | REST | Outbound + Inbound (webhook) | `lms_stripe`, `lms_order`, `lms_payment` | as-built (webhook stub — no sig-verify) |
| INT-002 | [INT-002-paypal.md](./INT-002-paypal.md) | PayPal | REST | Outbound | `lms_payment` plugin; `lms_paypal` dead-code | stub / dead-code |
| INT-003 | [INT-003-zoom.md](./INT-003-zoom.md) | Zoom | REST + WebSDK | Bidirectional | `lms_zoom` | as-built (one minor stub in VTT download) |
| INT-004 | [INT-004-openai.md](./INT-004-openai.md) | OpenAI | REST | Outbound | `lms_ai` — `OpenAiProvider` plugin | as-built |

---

## Integration by Category

### Payment
- [INT-001 — Stripe](./INT-001-stripe.md) — primary payment gateway (real PaymentIntent; webhook is stub)
- [INT-002 — PayPal](./INT-002-paypal.md) — secondary payment gateway (stub; `lms_paypal` module is dead code)

### Video Conferencing / Live Lessons
- [INT-003 — Zoom](./INT-003-zoom.md) — live class sessions via Server-to-Server OAuth + Web SDK

### AI / Content Generation
- [INT-004 — OpenAI](./INT-004-openai.md) — GPT-4o for text/JSON generation; DALL-E 3 for images

---

## Integration Dependency Matrix

| Integration | Direction | Features | Workflows | Risk Level | Security Risk |
|-------------|-----------|----------|-----------|------------|---------------|
| INT-001 Stripe | Outbound + Inbound | F-004 payments | WF-004 order management | Critical | Credentials in config YAML (plaintext, VCS) |
| INT-002 PayPal | Outbound only (stub) | F-004 payments | WF-004 order management | High | Credentials in config YAML; no server verification |
| INT-003 Zoom | Bidirectional | F-001 live lessons | WF-001 enrollment/learning | High | Credentials in config YAML (plaintext, VCS) |
| INT-004 OpenAI | Outbound | F-014 AI features | AI course generation | Medium | API key in config YAML (plaintext, VCS) |

---

## Security Note — Credentials in Config YAML

All third-party credentials (Stripe, PayPal, Zoom, OpenAI) are stored in **plaintext Drupal config YAML** files under `config/sync/` which is tracked by git. The Key module is installed as a dependency (`lms_ai` info.yml) but **no `key.key.*.yml` config files exist** — keys are stored directly in module settings config instead. This is a known critical risk; see `G02-risk-register.md` (planned, layer G — not yet materialized).

---

## Related Docs

- [B04 — Business Workflows](../B04-business-workflows/) — workflows that trigger these integrations
- [B05 — Acceptance Criteria](../B05-acceptance-criteria/) — integration acceptance tests
- [B03-features/04-order-membership.md](../B03-features/04-order-membership.md) — payment features
- [B03-features/14-ai-features.md](../B03-features/14-ai-features.md) — AI features
