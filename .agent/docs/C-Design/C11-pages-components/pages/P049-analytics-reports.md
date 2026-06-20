---
page: Analytics reports
drupal_concept: controller + export
mode: as-built
owner_module: lms_analytics
route_group: Admin
route_name: lms_analytics.courses
prototype_type: live-site
adequacy: NOT_RUN
---

# C11 Page - Analytics reports

## Purpose
Manager/admin reports at `/admin/lms/reports/{courses,quizzes,users}` with CSV export: enrollments, completion, pass rates, revenue (F-020).

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/admin/lms/reports/courses` (quizzes, users; `+/export`) |
| Route name | `lms_analytics.courses` |
| Drupal page primitive | controller (aggregate queries) + CSV export |
| Owner module | lms_analytics |
| Access | manager / administrator (scoped) |
| Cache | short-TTL or uncacheable (live metrics) |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| [dashboard_kpis](../blocks/dashboard_kpis.md) | KPI strip | content_top | headline metrics |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Reports | courses, quizzes, users; export CSV |
| Scope | manager = org; instructor = own (verify) |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| Metrics | aggregate over progress/orders/quiz results | manager/admin | live | enroll → count rises |
| Export | CSV controller | same | — | export → file |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| None | metrics computed from data | — |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | charts/tables + export |
| Empty | no data → zeros |
| Access denied | non-manager → 403 |
| Validation error | bad date range → ignored |
| Loading | query spinner |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Reports + export | partial | ⚠️ some KPIs basic (F-020); adequacy NOT_RUN |
