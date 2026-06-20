---
page: Learner dashboard
drupal_concept: controller (KPIs + lists)
mode: as-built
owner_module: lms_progress
route_group: App
route_name: lms_progress.dashboard
prototype_type: live-site
adequacy: PASS
---

# C11 Page - Learner dashboard

## Purpose
The authenticated learner's home at `/dashboard`: continue-learning, progress, recent certificates, notifications.

## Drupal 11 Page Definition
| Concern | Contract |
| ------- | -------- |
| Route path | `/dashboard` |
| Route name | `lms_progress.dashboard` |
| Drupal page primitive | controller render array |
| Owner module | lms_progress |
| Access | authenticated |
| Cache | contexts: `user`; tags: `lms_user_course_list:{uid}` |

## Placed Blocks
| Block | Label | Region | Why |
| ----- | ----- | ------ | --- |
| [progress_bar](../blocks/progress_bar.md) | Continue learning | content_top | resume courses |
| [dashboard_kpis](../blocks/dashboard_kpis.md) | My stats | content_top | counts |
| [notification_bell](../blocks/notification_bell.md) | Notifications | header | unread badge |

## Main Content Contract
| Aspect | Definition |
| ------ | ---------- |
| Sections | in-progress courses, completed, certificates, recommendations |

## Visible content inventory
| Visible content | Source | Role/permission | Cache invalidation | Smoke evidence |
| --------------- | ------ | --------------- | ------------------ | -------------- |
| My courses + progress | `lms_user_course` (own) | authenticated (own) | `lms_user_course_list:{uid}` | complete lesson → % updates |
| Certificates | `lms_user_certificate` (own) | own | cert tag | pass quiz → cert appears |
| Notifications | `lms_notification` (own) | own | notif tag | event → badge |

## Hardcoded content exceptions
| Value | Reason | Revisit |
| ----- | ------ | ------- |
| Section headings | UI chrome (i18n strings) | translate via interface i18n |

## Page States
| State | Expected behavior |
| ----- | ----------------- |
| Normal | populated dashboard |
| Empty | no enrollments → browse-catalog CTA |
| Access denied | anonymous → redirect to login |
| Validation error | n/a |
| Loading | per-widget load |

## Implementation status
| Item | Status | Notes |
| ---- | ------ | ----- |
| Dashboard widgets | done | own-data scoped |
