# I02 — SDK Docs

**Status: PROPOSED** — Không có SDK nào được build hay publish tính đến 2026-04-16.
EverLMS hiện không có public API đủ ổn định để wrap thành SDK.

---

## Current State

EverLMS không expose versioned public API cho external consumers.
SDK chỉ có ý nghĩa khi ít nhất một public API (I01) được stabilize.

---

## SDK Plan (PROPOSED)

Khi public API v1 được ship, SDK sẽ được build theo thứ tự ưu tiên:

| Language | Package name (proposed) | Priority | Rationale |
|----------|------------------------|----------|-----------|
| JavaScript / TypeScript | `@everlms/sdk` | High | Frontend embedding, third-party portals |
| Python | `everlms-sdk` | Medium | Automation, data import/export scripts |
| PHP | `everlms/php-sdk` | Low | Admin tooling, Drupal integrations |

---

## Planned SDK Features

- Certificate verification helper
- Course catalog read
- Webhook event type definitions + signature verification utility

---

## Prerequisites Before Building SDK

1. Public API v1 stabilized (see I01-public-api-spec/).
2. Authentication mechanism decided (API key vs OAuth2).
3. API versioning policy enforced (see I05).
4. Rate-limit headers standardized (see I06).

---

## Interim Workaround

External integrators có thể gọi trực tiếp vào REST endpoints khi I01 được ship.
Xem `I01-public-api-spec/01-certificate-verify.md` cho ví dụ curl.

---

## Files in this folder

| File | Scope | Status |
|------|-------|--------|
| `01-typescript-sdk-plan.md` | TypeScript/JavaScript client SDK (wraps I01 read endpoints + webhook helper) | PLANNED |
| `02-webhook-verify-utility.md` | Cross-language signature verify helper blueprint (Stripe-compatible HMAC) | BLUEPRINT |

Additional SDKs (`python.md`, `php.md`) will be added per the priority table above once TypeScript ships v1 and we have integrator demand signal.

---

*End of I02 README.*
