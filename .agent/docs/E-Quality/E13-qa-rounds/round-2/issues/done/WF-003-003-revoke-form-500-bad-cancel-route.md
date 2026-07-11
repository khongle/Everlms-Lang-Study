---
id: WF-003-003
workflow: WF-003
title: Revoke certificate form crashes with 500 (getCancelUrl references non-existent route)
severity: high
category: functionality
status: done
roles: manager, admin
source: round-2 real-session QA
found: 2026-07-11
---

# WF-003-003 - RevokeCertificateForm 500s on a missing route

## Problem
`RevokeCertificateForm::getCancelUrl()` returned
`new Url('entity.lms_user_certificate.collection')`, but the `lms_user_certificate`
entity defines no `collection` route. Rendering the confirm form (and the
post-submit redirect) threw `RouteNotFoundException` → **HTTP 500**, so revoking
a certificate was impossible for anyone who reached the page.

## Expected
The revoke confirm form renders and, after submit, returns to the certificate
management dashboard.

## Evidence
- `/workspace/certificates/53/revoke` returned 500:
  `Route "entity.lms_user_certificate.collection" does not exist`.

## Fix (implemented)
- `getCancelUrl()` now returns `Url::fromRoute('lms_certificate.admin_dashboard')`.

## Verification
- Revoke form renders; submit revokes cert 53 and redirects to
  `/workspace/certificates`.

## Resolution (2026-07-11)
getCancelUrl() -> lms_certificate.admin_dashboard; revoke form renders and redirects (no 500).
