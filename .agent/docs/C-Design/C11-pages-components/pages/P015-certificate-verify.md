---
page: Certificate verify
drupal_concept: controller + form
mode: as-built
owner_module: lms_certificate
route_group: Public
route_name: lms_certificate.verify
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Certificate verify

## Purpose
Public verification at `/certificate/verify`: enter a certificate number to confirm authenticity (auth/util).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/certificate/verify` |
| Route name | `lms_certificate.verify` |
| Primitive | controller + lookup form |
| Access | anonymous |
| Cache | uncacheable (lookup) |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Verify result | `lms_user_certificate.field_certificate_number` | n/a | — | valid number → verified |

## Page States
Normal: form · Empty: no input · Access denied: n/a · Validation: invalid number → "not found" · Loading: lookup.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Verify | done | revoked certs flagged |
