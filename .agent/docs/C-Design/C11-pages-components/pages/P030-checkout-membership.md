---
page: Checkout (membership)
drupal_concept: Form API
mode: as-built
owner_module: lms_membership
route_group: App
route_name: lms_membership.checkout
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Checkout (membership)

## Purpose
Subscribe to a plan at `/checkout/membership/{plan_id}` (form): review plan, pay, create `lms_user_membership`.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/checkout/membership/{plan_id}` |
| Route name | `lms_membership.checkout` |
| Primitive | Form API (order + payment) |
| Access | authenticated |
| Cache | uncacheable |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Plan + price | `lms_membership_plan` | — | plan tag | pay → membership active |

## Page States
Normal: summary + pay · Empty: invalid plan → 404 · Access denied: anon → login · Validation: declined → retry · Loading: gateway.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Membership checkout | done | same gateways (R-002 caveat) |
