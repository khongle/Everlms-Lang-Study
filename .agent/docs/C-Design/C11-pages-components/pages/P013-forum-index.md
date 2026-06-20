---
page: Forum index
drupal_concept: View page / controller list
mode: as-built
owner_module: lms_forum
route_group: Public
route_name: lms_forum.index
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Forum index

## Purpose
Community forum at `/forum`: list topics by category with engagement counters; entry to topic detail and new-topic.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/forum` (new: `/forum/new`) |
| Route name | `lms_forum.index` |
| Drupal page primitive | controller/View list |
| Owner module | lms_forum |
| Access | anonymous read; authenticated post |
| Cache | tags: `lms_forum_topic_list`; contexts: `user.roles`, `url.query_args` |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| course_card (reused) | Category filter | sidebar_first | filter by `forum_category` |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| List | topics sorted by last-reply; pinned first |
| Counters | replies, views, resolved badge |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Topic rows | `lms_forum_topic` | post: authenticated | topic list tag | new topic → row |
| Categories | `forum_category` taxonomy | content_editor | term cache | add category → filter |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | — | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | topic list |
| Empty | "no topics yet" + new-topic CTA |
| Access denied | post hidden for anonymous |
| Validation error | new-topic form validation |
| Loading | paged |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Forum list + post | done | moderation flags supported |
