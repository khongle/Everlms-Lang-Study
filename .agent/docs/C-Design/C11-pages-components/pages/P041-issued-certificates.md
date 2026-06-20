---
page: Issued certificates
drupal_concept: list + revoke/regenerate
mode: as-built
owner_module: lms_certificate
route_group: Manage
route_name: lms_certificate.issued
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Issued certificates

## Purpose
Certificate admin at `/workspace/certificates` (workspace-list): list issued certs, regenerate, revoke.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/certificates` (`{cert}/regenerate`; revoke `/admin/lms/certificates/{cert}/revoke`) |
| Route name | `lms_certificate.issued` |
| Primitive | list + actions |
| Access | manager |
| Cache | tags: cert list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Cert rows | `lms_user_certificate` | manage certificates | cert tag | issue → row; revoke → flagged |

## Page States
Normal: list · Empty: none · Access denied: non-manager → 403 · Validation: revoke reason required · Loading: regen.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Cert admin | done | bulk regen via script |
