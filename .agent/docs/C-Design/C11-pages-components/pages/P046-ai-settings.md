---
page: AI settings
drupal_concept: config form
mode: as-built
owner_module: lms_ai
route_group: Admin
route_name: lms_ai.settings
prototype_type: live-site
adequacy: PASS
---

# C11 Page - AI settings

## Purpose
AI provider config at `/admin/config/lms/ai` (+ `/admin/everlms-ai-engine`) (config): choose provider, set keys, defaults.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/admin/config/lms/ai`, `/admin/everlms-ai-engine` |
| Route name | `lms_ai.settings` |
| Primitive | config form |
| Access | administer (admin) |
| Cache | uncacheable (form) |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Provider + keys | `lms_ai.settings.yml`/`lms_ai.engine.settings.yml` | administrator | config | set provider → AI builder uses it |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| --- | --- | --- |
| API keys in config | ⚠️ R-001 — should move to Key/env | BLK-001 |

## Page States
Normal: form · Empty: unset → defaults · Access denied: non-admin → 403 · Validation: bad key → error on use · Loading: save.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| AI config | done | ⚠️ secrets in config (R-001) |
