---
page: My notes
drupal_concept: controller list + AJAX
mode: as-built
owner_module: lms_note
route_group: App
route_name: lms_note.my_notes
prototype_type: live-site
adequacy: PASS
---

# C11 Page - My notes

## Purpose
Personal notes at `/my-notes` (listing); private to the user.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/my-notes` (AJAX `/api/lms/note/{save,get,delete}`) |
| Route name | `lms_note.my_notes` |
| Primitive | controller list + AJAX CRUD |
| Access | authenticated (own only) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Notes | `lms_note` (own) | own | per-user | save note → appears; other user cannot read |

## Page States
Normal: notes · Empty: none · Access denied: others' notes hidden · Validation: empty → ignored · Loading: AJAX.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Notes | done | video-timestamp linked |
