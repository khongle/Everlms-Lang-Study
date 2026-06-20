# I05-api-changelog - API Changelog

Versioned at `/api/v1`. Newest first.

## v1 (as-built 2026-06-21)
- Auth (OTP register/verify, oauth/token, forgot/reset/change password).
- Catalog (categories, course search/detail, free enroll, lesson content).
- Learning (dashboard, my-courses, mark-complete).
- Quiz (get, submit, my-results, result detail).

## Planned
- Device-token register + push (E09S01T02).
- Payment webhook endpoints (E06S02T01).
- Rate limiting (R-007).

> Breaking changes require a new version namespace (`/api/v2`), never silent changes to `v1`.
