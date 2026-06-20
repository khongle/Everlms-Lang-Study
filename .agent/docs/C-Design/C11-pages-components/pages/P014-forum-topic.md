---
page: Forum topic
drupal_concept: entity view + reply form
mode: as-built
owner_module: lms_forum
route_group: Public
route_name: lms_forum.topic
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Forum topic

## Purpose
Topic + threaded replies at `/forum/{lms_forum_topic}` (detail); post reply, mark best answer, upvote.

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/forum/{lms_forum_topic}` (reply `/forum/{topic}/reply`) |
| Route name | `lms_forum.topic` |
| Primitive | entity view + Form API reply |
| Access | anonymous read; authenticated reply |
| Cache | tags: `lms_forum_topic:{id}`, replies |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Topic + replies | `lms_forum_topic`/`lms_forum_reply` | authenticated post | topic tag | reply → appears |

## Page States
Normal: thread · Empty: no replies · Access denied: reply hidden anon · Validation: empty reply → error · Loading: paged.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Topic + replies | done | best-answer/upvote |
