# Integration + Theme Layer

---

## `lms_zoom` — Zoom Integration

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Create/manage Zoom meetings, sync calendar, attendance tracking |
| **Dependencies** | `lms_base`, `lms_lesson` |
| **Services** | `lms_zoom.api_client`, `lms_zoom.meeting_manager` |

### Key Services
- `lms_zoom.api_client` — Zoom REST API wrapper (create meeting, get details, delete)
- `lms_zoom.meeting_manager` — Meeting lifecycle, link to lesson, attendance recording

### Integration Flow
1. Instructor creates Zoom lesson → API creates Zoom meeting
2. Students see meeting details + "Join" button (with countdown)
3. After meeting → recording available (if enabled)
4. Attendance tracked automatically via Zoom webhook

---

## `lms_analytics` — Analytics & Reporting

| Attribute | Value |
|-----------|-------|
| **Responsibility** | Dashboard KPIs, custom reports, GA4 integration |
| **Dependencies** | `lms_base` |
| **Services** | `lms_analytics.report_manager`, `lms_analytics.dashboard` |

### Report Types
| Report | Metrics |
|--------|---------|
| Course Performance | Enrollments, completion rate, avg score, revenue |
| User Activity | Active users, learning time, last login, dropout risk |
| Revenue | Total, by course, by plan, trends, refund rate |
| Quiz Analytics | Pass/fail rate, avg score, question difficulty analysis |
| Engagement | Forum posts, video watch time, login frequency |

---

## `lms_theme` — Custom Theme

| Attribute | Value |
|-----------|-------|
| **Base theme** | Custom standalone (Bootstrap 5.3) |
| **Responsibility** | Responsive UI, page templates, SCSS design system, mega-menu navigation |

### Structure
```
web/themes/custom/lms_theme/
├── scss/
│   ├── main.scss
│   ├── _variables.scss
│   ├── base/
│   ├── components/
│   ├── layouts/
│   └── pages/
├── js/
│   └── behaviors/
├── templates/
│   ├── page/
│   ├── node/
│   └── field/
├── images/
└── lms_theme.info.yml
```

> [!IMPORTANT]
> Navigation sử dụng **mega-menu dropdown** pattern (top horizontal navbar). **KHÔNG dùng left sidebar navigation** cho admin/management pages.
