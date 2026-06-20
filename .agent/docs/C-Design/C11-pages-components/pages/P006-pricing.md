---
page: Pricing
drupal_concept: controller + entity list
mode: as-built
owner_module: lms_membership
route_group: Public
route_name: lms_membership.pricing
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Pricing

## Purpose
Membership plans/pricing at `/pricing` (marketing archetype): plan cards + subscribe CTA.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/pricing` |
| Route name | `lms_membership.pricing` |
| Primitive | controller listing `lms_membership_plan` |
| Access | anonymous |
| Cache | tags: plan list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Plan cards | `lms_membership_plan` (+`field_features`,`field_recommended`) | manager | plan tag | add plan → card |

## Page States
Normal: plan cards · Empty: none · Access denied: n/a · Validation: n/a · Loading: lazy.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Pricing page | done | subscribe → checkout P030 |
