# B06 — Integration Requirements

> Index of all external integration definitions. Mỗi file mô tả một integration: direction, protocol, auth, data exchanged, rate limits, failure modes.

---

## Integration Index

| ID | File | Provider | Direction | Module | Status |
|----|------|----------|-----------|--------|--------|
| INT-001 | [INT-001-stripe.md](./INT-001-stripe.md) | Stripe | Outbound + Inbound (webhook) | `lms_stripe` | Stub |
| INT-002 | [INT-002-paypal.md](./INT-002-paypal.md) | PayPal | Outbound + Inbound (IPN) | `lms_paypal` | Stub |
| INT-003 | [INT-003-zoom.md](./INT-003-zoom.md) | Zoom | Outbound | `lms_zoom` | Stub |
| INT-004 | [INT-004-openai.md](./INT-004-openai.md) | OpenAI | Outbound | `lms_ai` | Plugin |
| INT-005 | [INT-005-google-gemini.md](./INT-005-google-gemini.md) | Google Gemini | Outbound | `lms_ai` | Plugin |
| INT-006 | [INT-006-pollinations.md](./INT-006-pollinations.md) | Pollinations.ai | Outbound | `lms_ai` | Plugin |
| INT-007 | [INT-007-smtp.md](./INT-007-smtp.md) | SMTP / Email | Outbound | `drupal/smtp` | Config ready |
| INT-008 | [INT-008-drupal-search-api.md](./INT-008-drupal-search-api.md) | Search API | Internal | `search_api` | Config ready |

---

## Integration by Category

### Payment
- [INT-001 — Stripe](./INT-001-stripe.md)
- [INT-002 — PayPal](./INT-002-paypal.md)

### AI / Content Generation
- [INT-004 — OpenAI](./INT-004-openai.md)
- [INT-005 — Google Gemini](./INT-005-google-gemini.md)
- [INT-006 — Pollinations (image)](./INT-006-pollinations.md)

### Communication
- [INT-007 — SMTP / Email](./INT-007-smtp.md)
- [INT-003 — Zoom (live sessions)](./INT-003-zoom.md)

### Search / Discovery
- [INT-008 — Drupal Search API](./INT-008-drupal-search-api.md)

---

## Integration Dependency Matrix

| Integration | Features | Workflows | Risk Level |
|-------------|----------|-----------|-----------|
| INT-001 Stripe | F-004 | WF-004, WF-005 | Critical |
| INT-002 PayPal | F-004 | WF-004 | High |
| INT-003 Zoom | F-001 | WF-001, WF-006 | Medium |
| INT-004 OpenAI | F-014 | WF-006 | Medium |
| INT-005 Gemini | F-014 | WF-006 | Medium |
| INT-006 Pollinations | F-014 | WF-006 | Low |
| INT-007 SMTP | F-008 | WF-003, WF-007 | High |
| INT-008 Search API | F-001 | — | Low |

---

## Related Docs

- [B04 — Business Workflows](../B04-business-workflows/) — workflows that use integrations
- [B05 — Acceptance Criteria](../B05-acceptance-criteria/) — AC-10 (integrations), AC-14 (AI)
- [B03 F-014 AI Features](../B03-features/14-ai-features.md)
