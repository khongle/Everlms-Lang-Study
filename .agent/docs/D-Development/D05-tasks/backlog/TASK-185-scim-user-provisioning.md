# Task: SCIM 2.0 User Provisioning Endpoint

**ID:** TASK-185
**Unit:** — (new module: lms_scim)
**Group:** G-INTEGRATION
**Priority:** P2
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement a SCIM 2.0 server endpoint so enterprise customers can automatically sync users and groups from their IdP (Okta, Microsoft Entra ID, JumpCloud) into EverLMS without manual CSV imports. Endpoints cover `/scim/v2/Users` (CRUD) and `/scim/v2/Groups` (CRUD, mapped to Drupal roles/cohorts). Authentication uses Bearer tokens issued via `simple_oauth`. A new module `lms_scim` owns all SCIM logic.

## Acceptance Criteria
- [ ] `lms_scim` module scaffolded with correct `.info.yml`, routing, services
- [ ] `GET /scim/v2/Users` — list users with pagination (`startIndex`, `count`), filter by `userName` or `email`
- [ ] `POST /scim/v2/Users` — create Drupal user from SCIM User resource; return `201 Created` with full resource
- [ ] `GET /scim/v2/Users/{id}` — retrieve user by SCIM `id` (mapped to Drupal uid)
- [ ] `PUT /scim/v2/Users/{id}` — full replace; `PATCH /scim/v2/Users/{id}` — partial update (Operations array)
- [ ] `DELETE /scim/v2/Users/{id}` — block (not delete) Drupal user; return `204 No Content`
- [ ] `GET /scim/v2/Groups`, `POST`, `GET /{id}`, `PUT`, `PATCH`, `DELETE` — same pattern; Groups mapped to Drupal roles
- [ ] `GET /scim/v2/ServiceProviderConfig` — returns capabilities document
- [ ] `GET /scim/v2/Schemas` — returns User + Group schema definitions
- [ ] All endpoints require Bearer token (`Authorization: Bearer {token}`) validated via `simple_oauth`
- [ ] SCIM `id` ↔ Drupal uid mapping stored in `lms_scim_user_map` table
- [ ] Idempotent `POST`: if user with same `userName` exists, return existing resource (`200 OK`)
- [ ] PHPUnit: full CRUD cycle for Users + Groups

## Technical Notes
- New module: `web/modules/custom/lms_scim/`
- `src/Controller/ScimUserController.php` — list, create, read, update, delete user endpoints
- `src/Controller/ScimGroupController.php` — list, create, read, update, delete group endpoints
- `src/Normalizer/ScimUserNormalizer.php` — Drupal user entity ↔ SCIM User JSON (RFC 7643)
- `src/Normalizer/ScimGroupNormalizer.php` — Drupal role ↔ SCIM Group JSON
- DB schema: `lms_scim_user_map` (`scim_id` varchar(36) PK, `uid` int, `external_id` varchar(255), `idp_id` varchar(64))
- Auth: use `simple_oauth` resource server; create dedicated SCIM OAuth scope `lms_scim`
- PATCH operations: support `add`, `remove`, `replace` op types per RFC 7644 §3.5.2
- Error responses: SCIM error schema (RFC 7644 §3.12) — `scimType`, `detail`, `status`
- Routes: `lms_scim.routing.yml` — all under `/scim/v2/`; `_access: 'TRUE'` (auth handled by controller via token)

## Dependencies
- TASK-183 (SAML SSO) or TASK-184 (OIDC SSO) — at least one SSO method in place before SCIM is useful in practice
- TASK-074 (mobile API + OAuth pattern — reuse `simple_oauth` setup)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: full CRUD cycle; integration test against mock Okta SCIM client)
- [ ] ServiceProviderConfig endpoint returns correct capabilities
- [ ] Security review: token validation, rate limiting on SCIM endpoints
