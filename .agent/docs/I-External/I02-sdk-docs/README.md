# I02-sdk-docs - SDK / Client

The official client is the **Flutter app** (`app/`, `everlms` v1.0.10+33). No standalone published SDK.

## Client integration
- Base URL: `<host>/api/v1`.
- Auth: obtain bearer token (`/oauth/token` or OTP flow), send `Authorization: Bearer <token>`.
- Parse the `{data,error,meta}` envelope; handle 401/403 (re-auth).
- Firebase configured (`app/firebase_options.dart`) for push (INT-009).

## App structure (reference)
`app/lib/` → `core/` (~49 dart), `feature/` (~329 dart), `l10n/`. Feature-first architecture.

> A typed Dart client lives in the app's `core/`; no external SDK is distributed. For third-party integrators, the [I01 spec](../I01-public-api-spec/README.md) is the contract.
