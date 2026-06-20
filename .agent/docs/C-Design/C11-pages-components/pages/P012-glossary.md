---
page: Glossary
drupal_concept: View page
mode: as-built
owner_module: lms_course
route_group: Public
route_name: lms_course.glossary
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Glossary

## Purpose
Term glossary at `/glossary` (listing): A–Z definitions (`node.type` glossary + `field_synonyms`).

## Drupal 11 Page Definition
| Concern | Contract |
| --- | --- |
| Route path | `/glossary` |
| Route name | `lms_course.glossary` |
| Primitive | View (glossary view exists) |
| Access | anonymous |
| Cache | tags: node list |

## Visible content inventory
| Visible content | Source | Role | Cache | Smoke |
| --- | --- | --- | --- | --- |
| Glossary terms | `node.type` glossary (+`field_synonyms`) | content_editor | node list | add term → entry |

## Page States
Normal: A–Z list · Empty: none · Access denied: n/a · Validation: n/a · Loading: paged.

## Implementation status
| Item | Status | Notes |
| --- | --- | --- |
| Glossary | done | core `views.view.glossary` |
