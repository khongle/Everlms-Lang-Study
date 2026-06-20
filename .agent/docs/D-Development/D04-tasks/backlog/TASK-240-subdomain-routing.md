# Task: Subdomain Routing per Org

**ID:** TASK-240
**Unit:** — (lms_org)
**Group:** G-MULTITENANCY
**Priority:** P2
**Estimate:** 12h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Route `{org-slug}.everlms.vn` to the corresponding Org's branded site instance. Requires DNS wildcard `*.everlms.vn → server IP` plus a Drupal routing middleware that reads the request hostname, resolves the Org from `field_org_custom_domain` or the subdomain slug, and sets the active Org context. SSL is handled via a wildcard certificate (reference: TASK-096).

## Context
- Requires TASK-238 (Org entity) and TASK-239 (branding, which adds `field_org_custom_domain`).
- Custom domains (e.g., `learn.company.vn`) are also supported via the same middleware (resolved by `field_org_custom_domain` exact match before slug fallback).
- TASK-096 covers SSL/TLS provisioning; this task assumes a wildcard cert for `*.everlms.vn` is already in place or references TASK-096.

## Acceptance Criteria
- [ ] `OrgSubdomainMiddleware` (HTTP Middleware, priority > OrgBrandingMiddleware) resolves the active Org from the request hostname:
  - Exact match on `field_org_custom_domain` → sets that Org as active
  - Hostname matches pattern `{slug}.everlms.vn` → look up Org by slug → set as active
  - No match → use Platform Default Org
- [ ] Middleware sets `OrgContextService::setCurrent(Organization $org)` for the current request
- [ ] All Drupal routes, blocks, and content render correctly under subdomain context (no hardcoded host assumptions)
- [ ] `field_org_custom_domain` validation: must be a valid FQDN; uniqueness enforced at entity level
- [ ] Admin UI at `/admin/lms/orgs/{org_id}/domain` shows: current custom domain, subdomain (`{slug}.everlms.vn`), SSL status indicator (green/red)
- [ ] Org Admin can set a custom domain; platform admin must approve before it becomes active (`field_org_domain_verified` boolean)
- [ ] `drush lms:org-verify-domain {org_id}` Drush command for platform admin to approve a custom domain after DNS check
- [ ] Wildcard cert reference: document that `*.everlms.vn` SSL cert must be provisioned per TASK-096 before subdomain routing works

## Technical Notes
- Module: `lms_org`
- Middleware: `lms_org/src/StackMiddleware/OrgSubdomainMiddleware.php` — registered in `lms_org.services.yml` with tag `http_middleware` and priority 300
- Hostname parsing: `$host = $request->getHost(); $parts = explode('.', $host); $slug = $parts[0];` — compare against `everlms.vn` suffix
- Cache: middleware result should be stored in `$request->attributes` (not static cache) to remain per-request; entity query cached by Drupal entity cache
- Drush command: `lms_org/src/Commands/OrgDomainCommands.php` — sets `field_org_domain_verified = true` after checking DNS A record via `dns_get_record()`
- Conflicts: Drupal's trusted_host_patterns must include `*.everlms.vn` in `settings.php` for subdomains to be accepted

## Dependencies
- TASK-238 (Org entity — slug and custom domain fields)
- TASK-239 (Org branding — OrgContextService and branding middleware)
- TASK-096 (SSL cert — wildcard cert for *.everlms.vn)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: hostname resolution logic for slug vs custom domain vs default; functional: correct Org resolved per hostname in kernel test)
- [ ] No new debt introduced
