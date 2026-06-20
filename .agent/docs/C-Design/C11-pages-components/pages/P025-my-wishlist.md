---
page: My wishlist
drupal_concept: controller list + AJAX
mode: as-built
owner_module: lms_wishlist
route_group: App
route_name: lms_wishlist.my_wishlist
prototype_type: live-site
adequacy: PASS
---

# C11 Page - My wishlist

## Purpose
Saved courses at `/my-wishlist` (listing); add/remove via AJAX.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/my-wishlist` (AJAX `/api/lms/wishlist/{toggle,status}`) |
| Route name | `lms_wishlist.my_wishlist` |
| Primitive | controller list + AJAX toggle |
| Access | authenticated (own) |
| Cache | per-user |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Wishlist items | `lms_wishlist` (own) | own | per-user | toggle → add/remove |

## Page States
Normal: list · Empty: none → catalog · Access denied: anon → login · Validation: n/a · Loading: AJAX.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Wishlist | done | convert to order |
