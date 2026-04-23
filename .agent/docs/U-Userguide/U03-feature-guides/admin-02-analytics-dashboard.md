# Feature Guide: Admin — Analytics Dashboard

**Feature ID:** FEAT-09 (analytics-reporting)
**Available since:** v0.1.0
**User roles:** Administrator, Manager, Instructor (own data only)
**Prerequisites:** —

## What It Does

Dashboard hiển thị metrics về learners, courses, revenue, quiz performance. Mỗi role thấy scope khác nhau (admin = all, manager = all read-only, instructor = chỉ own courses).

## Dashboard Widgets

### Overview (top row)

| Widget | Description | Admin/Manager | Instructor |
|--------|-------------|---------------|------------|
| Total users | All registered users | ✅ | ❌ |
| Active enrollments | UserCourse status = active | ✅ | Own courses |
| Revenue (this month) | Sum of paid orders | ✅ | Own revenue |
| Completion rate | % UserCourse completed / started | ✅ | Own courses |
| New registrations (7d) | Delta last week | ✅ | ❌ |

### Charts

- **Enrollment trend** (line chart, daily last 30 days).
- **Revenue trend** (bar chart, weekly last 12 weeks).
- **Top 10 courses by enrollments**.
- **Top 10 courses by revenue**.
- **Completion funnel**: Enrolled → Chapter 1 → Mid → Completed (drop-off %).
- **Quiz performance**: Avg score, pass rate per quiz.

### Tables

- **Recent orders** (latest 20).
- **Pending essay grading** (essay attempts awaiting instructor).
- **Stalled learners** (enrolled > 30 days, no activity 14 days).

## How to Use

### Access

- Admin/Manager: `/admin/lms/dashboard` (via admin menu → **Dashboard**).
- Instructor: `/instructor/dashboard`.

### Filter

- **Date range**: preset (Today / 7d / 30d / 90d / All time) hoặc custom.
- **Course**: Multi-select (admin/manager).
- **Category**: Filter theo taxonomy.

### Drill-down

- Click widget → detail page (e.g., click revenue → full orders list).
- Click course in top-10 → course-specific analytics.

### Export

- **Export to CSV** button cho mỗi table.
- Date range filter apply to export.

### Scheduled reports (admin)

- `/admin/lms/reports/schedule`:
  - Daily / Weekly / Monthly email digest.
  - Recipients (comma-separated email).
  - Include: overview + top courses + stalled learners.

## Configuration Options (admin)

| Option | Default | Description |
|--------|---------|-------------|
| `dashboard.refresh_interval_sec` | 300 | Cache TTL cho metrics |
| `dashboard.timezone` | UTC | Server timezone cho date grouping |
| `dashboard.currency_display` | VND | Revenue hiển thị đơn vị |
| `dashboard.stalled_threshold_days` | 14 | Không activity bao nhiêu ngày = stalled |

## Tips & Best Practices

- **Cache:** Dashboard queries tốn cost → cache 5 min mặc định. Hard refresh (Ctrl+F5) chỉ khi debug.
- **Drill-down trước khi quyết định**: Top-10 có thể bias bởi outlier (1 corporate order lớn).
- **Stalled learners** — gửi **lesson_reminder** notification hàng loạt qua admin action.
- **Retention watch:** Completion rate < 30% là red flag — review course content / onboarding.

## Known Limitations

- Chart library Chart.js, không real-time (poll 5 min).
- Không compare period vs period (previous week vs this week) trong MVP — roadmap.
- Export giới hạn 10k rows / request; dataset lớn cần chunk.
- Cohort analysis (retention by month registered) chưa có — roadmap v0.3.0.

## Related

- Feature spec: `B-Requirements/B03-features/09-analytics-reporting.md`
- AC: `B-Requirements/B05-acceptance-criteria/AC-09-analytics-reporting.md`
- Wireframe: `C-Design/C08-wireframes/11-admin-dashboard.md`
- Module: `web/modules/custom/lms_analytics/`
- Full admin guide: `U08-admin-guide.md`
