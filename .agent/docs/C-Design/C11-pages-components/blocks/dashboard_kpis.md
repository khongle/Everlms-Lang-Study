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
Các tile chỉ số chính (enrollments, completions, revenue) cho dashboard workspace/admin; biến thể dành cho learner hiển thị số liệu cá nhân.

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
- Số liệu khớp với DB; role scoping được áp dụng đúng; ⚠️ một số KPI mới triển khai một phần (partial) (F-020).
