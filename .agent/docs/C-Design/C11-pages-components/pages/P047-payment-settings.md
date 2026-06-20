---
page: Payment settings
drupal_concept: config form
mode: as-built
owner_module: lms_payment
route_group: Admin
route_name: lms_payment.settings
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Payment settings

## Purpose
Gateway config at `/admin/config/everlms/payment` (config): enable gateways, set keys.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/admin/config/everlms/payment` |
| Route name | `lms_payment.settings` |
| Primitive | config form |
| Access | administer (admin) |
| Cache | uncacheable |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Gateway toggles + keys | `lms_payment.settings.yml` | administrator | config | enable → checkout option |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| --- | --- | --- |
| Gateway keys in config | ⚠️ R-001 | BLK-001 |

## Page States
Normal: form · Empty: defaults · Access denied: non-admin → 403 · Validation: bad key → error on use · Loading: save.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Payment config | done | ⚠️ no webhook (R-002), keys in config (R-001) |
