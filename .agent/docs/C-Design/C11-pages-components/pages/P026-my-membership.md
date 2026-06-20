---
page: My membership
drupal_concept: controller + cancel form
mode: as-built
owner_module: lms_membership
route_group: App
route_name: lms_membership.my_membership
prototype_type: live-site
adequacy: PASS
---

# C11 Page - My membership

## Purpose
Subscription status at `/my-membership` (detail): plan, validity, cancel.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/my-membership` (cancel `/my-membership/{m}/cancel`) |
| Route name | `lms_membership.my_membership` |
| Primitive | controller + cancel action |
| Access | authenticated (own) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Membership | `lms_user_membership` (own) | own | per-user | subscribe → shows; cancel → status changes |

## Page States
Normal: status · Empty: none → pricing · Access denied: anon → login · Validation: cancel confirm · Loading: action.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Membership status | done | auto-renew flag |
