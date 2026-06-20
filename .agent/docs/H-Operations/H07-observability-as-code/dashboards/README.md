# H07 Dashboards

Dashboard definitions (target).

| Dashboard | Panels | Source |
| --------- | ------ | ------ |
| Business | enrollments, completions, revenue, certificates issued | lms_analytics / DB |
| Reliability | API 5xx, payment success rate, cron health, cert render success | logs |
| Engagement | active learners, forum activity, quiz pass rate | DB |

> Business KPIs also surface in-app via [lms_analytics](../../../C-Design/C05-modules/lms_analytics.md) (P049).
