---
block: Dashboard KPIs
drupal_concept: block plugin
owner_module: lms_analytics
region: content_top
block_type: plugin
adequacy: NOT_RUN
---

# C11 Block - Dashboard KPIs

## Purpose
Headline metric tiles (enrollments, completions, revenue) for workspace/admin dashboards; learner variant shows personal stats.

## Drupal 11 Contract
| Concern | Contract |
| --- | --- |
| Block ID | dashboard_kpis |
| Drupal primitive | block plugin (aggregate) |
| Owner module | lms_analytics |
| Placed on | P031, P017, P049 |
| Cache | short-TTL; contexts: `user.roles` |

## Configuration / Fields
| Item | Requirement |
| --- | --- |
| Data | aggregate over progress/orders/quiz results (role-scoped) |

## Rendering Responsibilities
| Responsibility | Requirement |
| --- | --- |
| Twig/theme | KPI tiles |
| Access | scope by role (manager=org, instructor=own, learner=self) |
| Responsive | yes |
| Editing | computed |

## Prototype Evidence
| Evidence | Path |
| --- | --- |
| Live | workspace/dashboard |

## Tests / Checks
- Metrics match DB; role scoping enforced; ⚠️ some KPIs partial (F-020).
