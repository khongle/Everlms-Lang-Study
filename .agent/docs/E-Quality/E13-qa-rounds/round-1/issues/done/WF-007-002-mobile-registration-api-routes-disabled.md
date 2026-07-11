---
id: WF-007-002
workflow: WF-007
title: Mobile registration API routes are unavailable because lms_mobile_api is disabled
severity: high
category: functional
status: done
roles: visitor, mobile
source: workflow QA
found: 2026-06-30
---

# WF-007-002 - Mobile registration API routes are unavailable

## Problem
WF-007 requires mobile registration through `/api/v1/user/register`, OTP resend,
OTP verification, and bearer-token onboarding. The route exists in
`web/modules/custom/lms_mobile_api/lms_mobile_api.routing.yml`, but the module is
disabled on the current site and missing from `config/sync/core.extension.yml`.

## Evidence
- `POST /api/v1/user/register` returned Drupal 404 during WF-007 capture.
- `ddev drush pml --filter=lms_mobile_api` showed `LMS Mobile API` as
  `Disabled`.
- `ddev drush config:get core.extension module.lms_mobile_api` returned null.

## Impact
The mobile branch of WF-007 cannot start. The Flutter app and mobile API
acceptance criteria cannot register a user, send/resend OTP, verify the account,
or issue an access token.

## Expected
`lms_mobile_api` should be enabled in active config and config sync so the
documented `/api/v1/user/*` routes are available in every environment.

## Fix Plan
- Add `lms_mobile_api: 0` to `config/sync/core.extension.yml`.
- Enable `lms_mobile_api` on the active site and clear Drupal caches.
- Retest the real mobile registration, resend OTP, verify OTP, and `/me` token
  flow.

## Resolution
- Added `lms_mobile_api: 0` to `config/sync/core.extension.yml`.
- Enabled `lms_mobile_api` on the active Drupal site.
- Cleared caches so the `/api/v1/user/*` routes are available.

## Verification
- `ddev drush pml --filter=lms_mobile_api` reports `Enabled`.
- Final WF-007 capture passed with mobile user uid `105`.
- Screenshots:
  `004-mobile-api-register-request-response.png`,
  `006-mobile-api-resend-otp-request-response.png`,
  `008-mobile-api-verify-otp-after-submit-token-issued.png`,
  `009-mobile-api-authenticated-me-student-role.png`, and
  `010-mobile-api-duplicate-email-validation.png`.
- DB verification: `wf007mobile20260630170211@example.test` is active and has
  the `student` role.
