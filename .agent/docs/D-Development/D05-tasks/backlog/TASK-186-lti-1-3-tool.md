# Task: LTI 1.3 Tool Provider

**ID:** TASK-186
**Unit:** — (new module: lms_lti)
**Group:** G-INTEGRATION
**Priority:** P2
**Estimate:** 24h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement EverLMS as an LTI 1.3 Tool Provider so courses and quizzes can be embedded inside LMS platforms that act as LTI Platforms (Canvas, Moodle, Blackboard, D2L Brightspace). Supports JWS launch token validation, Deep Linking (LTI-DL 2.0) for content selection, Assignment and Grade Services (AGS) to post grades back to the Platform gradebook, and Names and Role Provisioning Services (NRPS) for roster sync. A new module `lms_lti` owns all LTI logic using the `imsglobal/lti-1p3-tool` PHP library.

## Acceptance Criteria
- [ ] `lms_lti` module scaffolded; `imsglobal/lti-1p3-tool` installed via Composer
- [ ] Platform registration UI: `/admin/config/lms/lti/platform/{id}` stores (client_id, deployment_id, auth_login_url, auth_token_url, key_set_url, public_key_jwk)
- [ ] OIDC login initiation: `GET /lti/login` → redirects to Platform auth endpoint with `nonce` + `state`
- [ ] Launch endpoint: `POST /lti/launch` validates JWS ID token (signature, nonce, iss, aud, exp), extracts claims, JIT provisions Drupal user
- [ ] Deep Linking: `POST /lti/deep-link` returns content item list (course cards) for Platform content picker
- [ ] AGS: when student completes a course or passes a quiz, post grade (`0.0–1.0`) to Platform via `lineitems` URL from launch claim
- [ ] NRPS: `GET /lti/nrps/roster/{contextId}` fetches Platform roster and enrolls users into corresponding EverLMS course
- [ ] JWKS endpoint: `GET /lti/.well-known/jwks.json` serves EverLMS public key for Platform verification
- [ ] Tool configuration URL: `/admin/config/lms/lti/tool-config` provides XML/JSON tool config for Platform admin
- [ ] Behat scenario: LTI 1.3 launch from mock Platform logs user in and displays correct course

## Technical Notes
- New module: `web/modules/custom/lms_lti/`
- Composer: `imsglobal/lti-1p3-tool` (^5.0 or latest stable)
- `src/Controller/LtiLaunchController.php` — OIDC init, launch, deep-link endpoints
- `src/Controller/LtiAgsController.php` — grade passback via AGS REST calls (outbound from EverLMS to Platform)
- `src/Controller/LtiNrpsController.php` — roster fetch + enrollment sync
- `src/Controller/LtiJwksController.php` — serves JWKS public key
- `src/Service/LtiUserProvisioner.php` — JIT create/match user from LTI `sub` + `email` claims
- `src/Service/LtiGradeService.php` — watches `CourseCompletedEvent` + quiz `QuizPassedEvent`, posts grade to AGS
- Config entity: `lms_lti_platform` — per-Platform registration (encrypted client_secret via `key` module)
- Key pair: generate RSA-2048 key pair; store private key in `private://lti/` outside webroot
- State: nonce + state stored in `\Drupal::state()` with 10-min TTL for replay protection
- Routes: `lms_lti.routing.yml` — all under `/lti/`

## Dependencies
- TASK-183 (SAML SSO — JIT provisioner pattern reused)
- TASK-074 (mobile API — OAuth2 + JWT handling reference)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: token validation + JIT provisioner; integration test with mock Platform using `imsglobal/lti-1p3-tool` test utilities)
- [ ] AGS grade passback confirmed working against Canvas sandbox
- [ ] Key pair rotation procedure documented in H02 runbook
