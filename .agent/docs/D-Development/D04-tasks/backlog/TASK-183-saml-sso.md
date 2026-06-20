# Task: SAML 2.0 SSO for B2B

**ID:** TASK-183
**Unit:** — (new: G-INTEGRATION, B2B auth)
**Group:** G-INTEGRATION
**Priority:** P1
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement SAML 2.0 Single Sign-On for B2B customers. Supports both IdP-initiated and SP-initiated flows. Just-in-time (JIT) user provisioning auto-creates Drupal user accounts on first SAML assertion. Role mapping translates SAML attribute groups/roles to Drupal roles. Configuration is per-organization via a custom adapter on top of the `simplesamlphp_auth` contrib module. Multiple IdP configurations can coexist (one per org), routed by `entityID` or subdomain.

## Acceptance Criteria
- [ ] `simplesamlphp_auth` contrib module installed and enabled; SimpleSAMLphp library configured under `private://simplesamlphp/`
- [ ] SP metadata accessible at `/saml/metadata` and downloadable for IdP registration
- [ ] SP-initiated login flow: `/saml/login?idp={entityId}` redirects to IdP, processes assertion, logs user in
- [ ] IdP-initiated login flow: assertion POST to `/saml/acs` processes correctly without prior SP request
- [ ] JIT provisioning: new Drupal user created from SAML attributes (`mail`, `displayName`, `uid`); existing user matched by email
- [ ] Role mapping: SAML attribute `groups` (configurable attribute name) mapped to Drupal roles via config UI
- [ ] Multi-IdP: org admin can register IdP metadata URL or XML in `/admin/config/lms/saml/idp/{orgId}`
- [ ] SLO (Single Logout) supported: `/saml/logout` triggers IdP logout redirect
- [ ] SAML assertions validated: signature, NotBefore/NotOnOrAfter, audience restriction
- [ ] Behat scenario: IdP-initiated login creates user + assigns correct role

## Technical Notes
- Contrib: `drupal/simplesamlphp_auth` (^4.0); SimpleSAMLphp library via Composer (`simplesamlphp/simplesamlphp`)
- Custom adapter: `web/modules/custom/lms_saml/` — thin wrapper for multi-IdP routing and JIT logic
- `src/SamlIdpRouter.php` — resolves IdP entityID from URL param or subdomain
- `src/SamlUserProvisioner.php` — JIT create/update user; maps attributes to user fields
- Config entity: `lms_saml_idp` — stores entityId, metadata XML/URL, attribute mappings per org
- Routes: defined in `lms_saml.routing.yml`; `/saml/metadata`, `/saml/login`, `/saml/acs`, `/saml/logout`
- SimpleSAMLphp config files: `private://simplesamlphp/config/config.php`, `metadata/saml20-idp-remote.php` — generated dynamically from config entities
- Security: enable `wantSignedAssertions: true`, validate certificate against stored fingerprint
- Runbook: document cert rotation procedure in H02

## Dependencies
- TASK-074 (mobile API hardening — auth pattern reference for token validation approach)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: attribute mapping + JIT provisioner; Behat: SP-initiated login flow)
- [ ] SAML cert rotation procedure documented in H02 runbook
- [ ] Security review: assertion validation + replay attack prevention confirmed
