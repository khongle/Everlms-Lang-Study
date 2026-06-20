---
page: Manage ratings
drupal_concept: list + moderation actions
mode: as-built
owner_module: lms_rating
route_group: Manage
route_name: lms_rating.workspace
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Manage ratings

## Purpose
Review moderation at `/workspace/ratings` (workspace-list): approve/feature/remove reviews.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/ratings` (`{rating}/{action}`) |
| Route name | `lms_rating.workspace` |
| Primitive | list + action callbacks |
| Access | content_editor/manager |
| Cache | tags: rating list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Review rows | `lms_rating` | moderate ratings | rating tag | approve → shows on course |

## Page States
Normal: list · Empty: none · Access denied: non-staff → 403 · Validation: n/a · Loading: paged.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Moderation | done | approve/feature flags |
