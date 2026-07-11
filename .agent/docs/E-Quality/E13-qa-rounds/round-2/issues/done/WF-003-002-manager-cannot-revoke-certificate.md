---
id: WF-003-002
workflow: WF-003
title: Manager cannot revoke certificates — revoke route uses an inconsistent permission/path
severity: medium
category: access-control
status: done
roles: manager, admin
source: round-2 real-session QA
found: 2026-07-11
---

# WF-003-002 - Revoke route gated by a permission managers don't hold

## Problem
`lms_certificate.revoke` required `_permission: 'revoke any certificate'` and
lived at `/admin/lms/certificates/{id}/revoke`, while the rest of certificate
administration (`admin_dashboard`, `regenerate`) uses `administer lms_course`
under `/workspace/certificates/...`. Managers hold `administer lms_course` but
not `revoke any certificate`, so the revoke link from the dashboard was denied —
contradicting C12 WF-003 ("manager can revoke/regenerate").

## Expected
Managers/admins who manage certificates can revoke them; the route is consistent
with the other certificate-admin routes.

## Evidence
- manager (uid 91) at the old revoke URL → "Access denied".

## Fix (implemented)
- Route moved to `/workspace/certificates/{id}/revoke` and gated by
  `administer lms_course` (link is generated via the route name, so no other
  references break).

## Verification
- manager (uid 91) opens the revoke form, submits, cert 53 → revoked; public
  verifier shows it invalid. (Restored afterwards for continuity.)

## Resolution (2026-07-11)
Revoke route moved to /workspace/certificates/{id}/revoke with 'administer lms_course'; manager can revoke.
