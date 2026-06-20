---
page: Manage orders
drupal_concept: View list + edit
mode: as-built
owner_module: lms_order
route_group: Manage
route_name: lms_course.workspace_orders
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Manage orders

## Purpose
Order list at `/workspace/orders` (workspace-list): view/edit orders, confirm bank transfers.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/orders` (`{order}/edit`) |
| Route name | `lms_course.workspace_orders` |
| Primitive | order list + edit |
| Access | manager |
| Cache | tags: order list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Order rows | `lms_order` | manage orders | order list | order → row; confirm → status |

## Page States
Normal: list · Empty: none · Access denied: non-manager → 403 · Validation: n/a · Loading: paged.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Manage orders | done | ⚠️ no webhook reconcile (R-002) |
