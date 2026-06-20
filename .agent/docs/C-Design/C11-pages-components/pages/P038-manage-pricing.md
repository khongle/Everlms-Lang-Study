---
page: Manage pricing / plans
drupal_concept: entity list + form
mode: as-built
owner_module: lms_membership
route_group: Manage
route_name: lms_membership.workspace_pricing
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Manage pricing / plans

## Purpose
Membership plan admin at `/workspace/pricing` (workspace-list): create/edit/toggle plans + user memberships.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/pricing` (add, `{plan}/edit/toggle`); `/workspace/user-memberships` |
| Route name | `lms_membership.workspace_pricing` |
| Primitive | entity list + forms |
| Access | manager |
| Cache | tags: plan list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Plans + memberships | `lms_membership_plan`/`lms_user_membership` | manage memberships | plan tag | add plan → P006 card |

## Page States
Normal: list · Empty: none → add · Access denied: non-manager → 403 · Validation: required → errors · Loading: paged.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Manage plans | done | toggle active |
