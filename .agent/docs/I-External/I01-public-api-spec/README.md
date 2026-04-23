# I01 — Public API Spec

**Project:** EverLMS
**Current state:** Không có versioned public JSON API nào được expose cho external consumers.
Bề mặt public duy nhất hiện tại là:
1. Certificate verification endpoint (`GET /certificate/verify`) — server-rendered form, không phải JSON API thuần.
2. Payment webhook receivers (Stripe, PayPal) — inbound only, không phải public API.

**Stability:** No stable public API as of 2026-04-16.

---

## Current Public Surface

| Endpoint | Method | Format | Auth | Status |
|----------|--------|--------|------|--------|
| `/certificate/verify` | GET/POST | HTML (Drupal Form) | None | Shipped (HTML form) |
| `/payment/webhook/stripe` | POST | JSON (inbound) | Signature (NOT YET) | Partial |
| `/payment/webhook/paypal` | POST | JSON (inbound) | Signature (NOT YET) | Partial |

> Note: `/certificate/verify` hiện là Drupal Form (HTML). A dedicated `GET /certificate/verify/{code}`
> JSON endpoint is **PROPOSED** — see `01-certificate-verify.md`.

---

## Planned Endpoints (PROPOSED)

**Status: PROPOSED** — none of the following are built yet.

| Endpoint | Method | Summary | Priority | Spec file |
|----------|--------|---------|----------|-----------|
| `GET /api/v1/certificate/verify/{code}` | GET | Verify certificate by code (JSON response) | High | `01-certificate-verify.md` |
| `GET /api/v1/courses` | GET | Public course catalog with pagination/filters | Medium | `02-catalog-browse.md` |
| `GET /api/v1/courses/{slug}` | GET | Single course details (auth optional) | Medium | `03-course-detail.md` |
| `GET /api/v1/users/me/enrollments` | GET | Learner enrollment list + progress (auth required) | Medium | `04-enrollment-status.md` |
| `POST /api/v1/enroll` | POST | Enroll in a course (authenticated) | Low | Not yet specced |
| `GET /api/v1/me/progress` | GET | Learner progress overview | Low | Not yet specced |
| `GET /api/v1/me/certificates` | GET | Learner certificate list | Low | Not yet specced |

---

## Versioning Strategy (PROPOSED)

**Status: PROPOSED**

- URL prefix: `/api/v{N}/`
- Major version bump required for any breaking change.
- Deprecation window: minimum 90 days.
- See `I05-api-changelog.md` for entries.

---

## Files in This Folder

| File | Description | Status |
|------|-------------|--------|
| `01-certificate-verify.md` | Contract for `GET /api/v1/certificate/verify/{code}` | PROPOSED |
| `02-catalog-browse.md` | Contract for `GET /api/v1/courses` — pagination, filters, response schema | PROPOSED |
| `03-course-detail.md` | Contract for `GET /api/v1/courses/{slug}` — auth optional, full schema | PROPOSED |
| `04-enrollment-status.md` | Contract for `GET /api/v1/users/me/enrollments` — auth required, progress + cert links | PROPOSED |

---

*See also: I03-webhook-contracts/ for inbound Stripe/PayPal webhook specs.*
*See also: C-Design/C06-apis/ for full API design (authored separately).*
