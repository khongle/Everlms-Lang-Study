# S02 — System Contracts

> Contracts mô tả cách EverLMS gọi ra external systems.
> EverLMS là monolith — không có internal service-to-service calls.
> Tất cả contracts ở đây là **outbound** từ EverLMS đến external APIs.

---

## What "Contract" Means Here

Vì EverLMS là single deployable, không có inter-service contracts theo nghĩa microservices.
"Contract" ở đây = interface mà EverLMS phụ thuộc vào từ external service:
- Endpoints được gọi.
- Auth mechanism.
- Retry / timeout behaviour.
- Failure mode khi external service down.

---

## Active Outbound Integrations

| Contract File | Caller Module | External Service | Protocol | Criticality | Status |
|---------------|--------------|-----------------|---------|------------|--------|
| `01-everlms-to-stripe.md` | `lms_stripe` | Stripe API | HTTPS REST | Tier 1 (payments) | ACTIVE |
| `02-everlms-to-openai.md` | `lms_ai` | OpenAI API | HTTPS REST | Tier 3 (AI features) | ACTIVE |
| `03-everlms-to-gemini.md` | `lms_ai` | Google Gemini API | HTTPS REST | Tier 3 (AI features) | ACTIVE |
| `04-everlms-to-pollinations.md` | `lms_ai` | Pollinations.ai | HTTPS REST | Tier 3 (free images) | ACTIVE |
| `05-everlms-to-paypal.md` | `lms_paypal` | PayPal REST API | HTTPS REST | Tier 2 (payments) | STUB |
| `06-everlms-to-zoom.md` | `lms_zoom` | Zoom API v2 | HTTPS REST | Tier 2 (live sessions) | ACTIVE |
| `07-everlms-to-smtp.md` | `lms_notification` | SMTP server | SMTP/TLS | Tier 2 (notifications) | PARTIAL |

---

## Inbound Contracts

EverLMS cũng *nhận* webhooks từ Stripe và PayPal.
Những inbound contracts đó được document trong `I-External/I03-webhook-contracts/`.

---

## Failure Handling Philosophy

- **Tier 1 (Stripe):** Payment failures must surface to user immediately. No silent swallowing.
- **Tier 2 (Zoom, PayPal, SMTP):** Failures logged; user notified; graceful degradation.
- **Tier 3 (OpenAI, Gemini, Pollinations):** Failures logged; AI features disabled silently; no user-visible error for non-critical paths.

---

## Files in This Folder

| File | Status | Notes |
|------|--------|-------|
| `01-everlms-to-stripe.md` | Documented | ACTIVE |
| `02-everlms-to-openai.md` | Documented | ACTIVE |
| `03-everlms-to-gemini.md` | Documented | ACTIVE — FIND-001: API key in URL query param |
| `04-everlms-to-pollinations.md` | Documented | ACTIVE — free tier, no auth |
| `05-everlms-to-paypal.md` | Documented | STUB — no implementation yet; RISK-004 |
| `06-everlms-to-zoom.md` | Documented | ACTIVE — Server-to-Server OAuth2 + JWT Web SDK |
| `07-everlms-to-smtp.md` | Documented | PARTIAL — drupal/smtp installed, email dispatch not wired |
