---
page: Manage users
drupal_concept: View list + user ops
mode: as-built
owner_module: lms_course
route_group: Manage
route_name: lms_course.workspace_users
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Manage users

## Purpose
User admin at `/workspace/users` (workspace-list): list, create, edit, bulk, roles.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/workspace/users` (create, bulk, `{user}/admin-edit`) |
| Route name | `lms_course.workspace_users` |
| Primitive | user list + forms |
| Access | manager/administrator |
| Cache | tags: user list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| User rows + roles | `user` entities | administer users | user list | create → row |

## Page States
Normal: list · Empty: n/a · Access denied: non-manager → 403 · Validation: dup email → error · Loading: paged.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Manage users | done | bulk + impersonate (P051) |
