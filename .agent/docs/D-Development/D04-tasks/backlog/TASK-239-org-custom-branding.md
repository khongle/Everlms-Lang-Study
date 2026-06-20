# Task: Per-Org Custom Branding

**ID:** TASK-239
**Unit:** — (lms_org + lms_theme)
**Group:** G-MULTITENANCY
**Priority:** P2
**Estimate:** 16h
**Assigned:** unassigned
**Status:** BACKLOG

## Description
Allow each Organization to configure its own branding: logo, primary color theme override, custom domain, email sender name/address, footer text, and login page background image. An Org Admin can configure all of these from the Org settings UI. The site applies the active Org's branding on every page request within that Org's context.

## Context
- Requires TASK-238 (Org entity) to be complete.
- Existing branding: F15 customization mentioned in project notes but not yet implemented as a task.
- TASK-240 (subdomain routing) handles DNS/middleware; this task handles the branding data model and CSS injection.

## Acceptance Criteria
- [ ] Org entity (from TASK-238) gains new branding fields: `field_org_logo` (image), `field_org_primary_color` (color picker, hex), `field_org_custom_domain` (string, URL), `field_org_email_sender_name` (string), `field_org_email_sender_address` (email), `field_org_footer_text` (text), `field_org_login_bg` (image)
- [ ] Org Admin UI at `/admin/lms/orgs/{org_id}/branding` exposes all branding fields with live preview panel
- [ ] On every page request, `OrgBrandingMiddleware` (Drupal HTTP Middleware) resolves active Org via `OrgContextService` and injects CSS custom property overrides into `<head>`: `--lms-color-primary`, `--lms-logo-url`
- [ ] Logo override replaces the default `lms_theme` logo in the header block
- [ ] Footer text override replaces the default footer text block for Org context
- [ ] Login page background image applied when the active Org has `field_org_login_bg` set
- [ ] Email sender name/address used by `lms_notification` when sending emails within the Org context
- [ ] Platform Default Org branding = site-wide defaults; no override applied if Org has no branding configured
- [ ] CSS injected as an inline `<style>` tag (not a separate file) to avoid cache invalidation complexity

## Technical Notes
- Modules: `lms_org` (branding fields + middleware), `lms_theme` (CSS vars usage), `lms_notification` (sender override)
- Middleware: `OrgBrandingMiddleware` — implements `HttpKernelInterface`; calls `OrgContextService::getCurrent()`, fetches branding fields, injects `<style>` via response content string replacement (or response subscriber)
- Alternative to middleware: `hook_preprocess_html()` in `lms_org` + `drupalSettings` injection; simpler but less clean
- Color picker: use Drupal core's `color` element or a custom `OrgColorPickerElement` plugin
- Email sender override: `lms_org` subscribes to `MailEvents::PRE_SEND`; overrides `From` header if Org has `field_org_email_sender_address`
- Caching: branding config should be cached per Org (cache tag `org:{org_id}`); invalidate on Org entity save

## Dependencies
- TASK-238 (Org entity — required foundation)

## Definition of Done
- [ ] Code complete
- [ ] G1-G4 gates passed
- [ ] Tests written + passing (unit: middleware branding injection; functional: Org A branding not visible on Org B pages)
- [ ] No new debt introduced
