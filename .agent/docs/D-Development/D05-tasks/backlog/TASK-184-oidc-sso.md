# Task: OpenID Connect SSO

**ID:** TASK-184
**Unit:** — (new: G-INTEGRATION, B2B auth)
**Group:** G-INTEGRATION
**Priority:** P1
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Implement OpenID Connect (OIDC) SSO supporting three providers out of the box: Google Workspace, Microsoft Entra ID (Azure AD), and Okta. Uses the `openid_connect` contrib module with a custom claims handler for LMS-specific field mapping (organization, role, cohort). JIT user provisioning creates Drupal accounts on first login; existing users matched by email. Per-org configuration stored in config entities. Authorization Code Flow with PKCE required.

## Acceptance Criteria
- [ ] `openid_connect` contrib module installed; admin UI at `/admin/config/services/openid-connect`
- [ ] Three pre-configured provider plugins: Google Workspace, Microsoft Entra ID, Okta (client ID/secret + discovery URL per org)
- [ ] Authorization Code Flow with PKCE enforced for all providers; state + nonce validated
- [ ] JIT provisioning: Drupal user created from ID token claims (`email`, `name`, `given_name`, `family_name`); existing user matched by email
- [ ] Custom claims handler maps provider-specific group/role claim → Drupal roles (configurable per provider)
- [ ] LMS-specific claims mapped: `lms_org_id` → user organization field, `lms_cohort` → cohort field
- [ ] Login button per provider on `/user/login` page (show only configured providers)
- [ ] Callback URL: `/openid-connect/{provider}/callback`; registered in provider app settings
- [ ] Token refresh handled automatically on session renewal
- [ ] Behat scenario: Google Workspace OIDC login creates user + assigns `student` role

## Technical Notes
- Contrib: `drupal/openid_connect` (^3.0)
- Custom plugin: `web/modules/custom/lms_oidc/src/Plugin/OpenIDConnectClient/LmsEntraId.php` (extends base for Entra-specific claims)
- Custom plugin: `web/modules/custom/lms_oidc/src/Plugin/OpenIDConnectClient/LmsOkta.php`
- Google Workspace: use built-in `openid_connect.google` plugin; extend via `hook_openid_connect_claims_alter()`
- Claims handler: `src/ClaimsHandler/LmsClaimsHandler.php` — maps `groups` / `roles` claim arrays to Drupal roles
- Config entity: `lms_oidc_provider` — per-org provider config (client_id, client_secret encrypted, discovery_url, claim_mapping JSON)
- Module: `web/modules/custom/lms_oidc/`
- PKCE: confirm `openid_connect` ^3 supports PKCE; patch or override if not
- Secrets: client secrets stored encrypted via `key` module or Drupal state (not plain config)

## Dependencies
- TASK-183 (SAML SSO — auth infrastructure precedent; implement in parallel or after)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (PHPUnit: claims handler mapping; Behat: OIDC login flow for each provider)
- [ ] Client secrets confirmed not exported to config sync (excluded in `.gitignore` or `key` module)
- [ ] Security review: PKCE, state/nonce, token validation confirmed
