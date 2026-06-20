---
page: My certificates
drupal_concept: controller list
mode: as-built
owner_module: lms_certificate
route_group: App
route_name: lms_progress.my_certificates
prototype_type: live-site
adequacy: PASS
---

# C11 Page - My certificates

## Purpose
Earned certificates at `/my-certificates` (listing): view/download PDFs.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/my-certificates` |
| Route name | `lms_progress.my_certificates` |
| Primitive | controller list (own `lms_user_certificate`) |
| Access | authenticated (own) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Certificate rows + PDF | `lms_user_certificate` (own) | own | per-user | pass quiz → cert |

## Page States
Normal: list · Empty: none yet · Access denied: anon → login · Validation: n/a · Loading: lazy.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| My certificates | done | download `field_pdf_file` |
