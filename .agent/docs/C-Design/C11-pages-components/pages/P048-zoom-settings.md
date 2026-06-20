---
page: Zoom settings
drupal_concept: config form
mode: as-built
owner_module: lms_zoom
route_group: Admin
route_name: lms_zoom.settings
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Zoom settings

## Purpose
Zoom config at `/admin/config/lms/zoom` (config): API credentials, defaults.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/admin/config/lms/zoom` |
| Route name | `lms_zoom.settings` |
| Primitive | config form |
| Access | administer (admin) |
| Cache | uncacheable |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Zoom creds | `lms_zoom.settings.yml` | administrator | config | set → meetings create |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| --- | --- | --- |
| Zoom creds in config | ⚠️ R-001 | BLK-001 |

## Page States
Normal: form · Empty: defaults · Access denied: non-admin → 403 · Validation: bad creds → error on use · Loading: save.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Zoom config | done | ⚠️ secrets in config; F-022 partial |
