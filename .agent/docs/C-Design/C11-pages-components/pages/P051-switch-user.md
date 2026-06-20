---
page: Switch user
drupal_concept: controller action
mode: as-built
owner_module: open_switcher
route_group: Admin
route_name: open_switcher.switch
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Switch user

## Purpose
Impersonation at `/admin/switch-user` (+ `/app/switch-user`) (auth/util): switch to a user, switch back (F-021).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/admin/switch-user` (`/{user}`), `/admin/switch-back`, `/app/switch-user` |
| Route name | `open_switcher.switch` |
| Primitive | controller action (session switch) |
| Access | dedicated switch permission (support/admin) |
| Cache | uncacheable |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Switch UI + banner | session state | switch permission | — | switch → browse as user; back → restored |

## Page States
Normal: switch list/banner · Empty: n/a · Access denied: no switch perm → 403 · Validation: invalid user → error · Loading: redirect.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Impersonation | done | switch-back restores original |
