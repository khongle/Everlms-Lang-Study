# Wireframe: Admin Dashboard

> Navigation: Top mega-menu dropdown. **KHÔNG left sidebar.**

## Admin Dashboard (`/manage`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ MANAGEMENT NAVBAR (mega-menu)                                        │
│ [Logo] Dashboard│Courses▼│Assessments▼│Users▼│Commerce▼│Reports▼│⚙️│
│                                                    [🔔] [👤 Admin ▼]│
├─────────────────────────────────────────────────────────────────────┤
│ KPI CARDS (4 columns)                                                │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐                │
│ │ 📚 248   │ │ 👥 12.5K │ │ 💰 $45K  │ │ 📝 1,234 │                │
│ │ Courses  │ │ Students │ │ Revenue  │ │ Orders   │                │
│ │ +12 ↑    │ │ +234 ↑   │ │ +15% ↑   │ │ +45 ↑    │                │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘                │
├──────────────────────────────────┬──────────────────────────────────┤
│ REVENUE CHART (col-8)            │ RECENT ENROLLMENTS (col-4)       │
│                                  │                                  │
│ [Line chart: Revenue over time]  │ • John → Web Dev Boot.. (2h)    │
│ Filter: [This Month ▼]          │ • Sarah → React Master.. (5h)   │
│                                  │ • Mike → CSS Basics (1d)        │
│ ┌────────────────────────────┐  │ • Anna → Node.js (1d)           │
│ │     _____                  │  │ [View All →]                     │
│ │    /     \    /\           │  │                                  │
│ │___/       \__/  \___       │  │                                  │
│ │ Jan Feb Mar Apr May Jun    │  │                                  │
│ └────────────────────────────┘  │                                  │
├──────────────────────────────────┼──────────────────────────────────┤
│ TOP COURSES (col-6)              │ PENDING ACTIONS (col-6)          │
│                                  │                                  │
│ # │ Course      │ Enrolled│ Rev │ ⚠️ 5 essays pending grading      │
│ 1 │ Web Dev..   │ 1,234   │ $12K│ ⚠️ 3 orders pending review       │
│ 2 │ React Ma..  │ 567     │ $8K │ ⚠️ 2 refund requests             │
│ 3 │ CSS Bas..   │ 345     │ $5K │ ⚠️ 12 new forum posts            │
│ [View All →]                     │ [View All →]                     │
├──────────────────────────────────┴──────────────────────────────────┤
│ FOOTER                                                               │
└─────────────────────────────────────────────────────────────────────┘
```

## Instructor Dashboard (`/manage/instructor`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ MANAGEMENT NAVBAR                                                    │
├─────────────────────────────────────────────────────────────────────┤
│ "Welcome back, John Doe" (Instructor)                                │
│                                                                      │
│ MY KPIs                                                              │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐                │
│ │ 📚 8     │ │ 👥 1,234 │ │ ★ 4.7    │ │ 📝 23    │                │
│ │ My Course│ │ Students │ │ Avg Rate │ │ Pending  │                │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘                │
│                                                                      │
│ MY COURSES                          │ GRADING QUEUE                  │
│ • Web Dev Bootcamp (1234 students)  │ • Essay Q1: John D. (2h ago)  │
│ • React Masterclass (567 students)  │ • Essay Q2: Sarah L. (5h ago) │
│ [+ Create New Course]               │ [Grade Now →]                  │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Implementation status (as of 2026-06-20)

**as-built.**

- Workspace dashboard: `/workspace` → `AdminController::dashboard` → `lms-admin-dashboard.html.twig`. KPI stat cards (courses, enrollments, revenue, completion rate) are rendered from live data.
- Reports overview: `/workspace/reports` → `AdminController::reports` → `lms-admin-reports.html.twig`.
- Detailed analytics: `/admin/lms/reports/courses`, `/admin/lms/reports/users`, `/admin/lms/reports/quizzes` (lms_analytics module — all fully implemented with CSV export).
- AI Course Builder: `/manage/courses/ai-builder` → `CourseBuilderForm` (AJAX, calls OpenAI/Gemini via plugin). Advanced AI engine wizard at `/admin/everlms-ai-engine`.
- **Instructor dashboard sub-panel** (second wireframe panel with "My Courses" + "Grading Queue") is **target-state** as a distinct role view — currently all instructors use the same `/workspace` dashboard as admins; no separate instructor-role-scoped dashboard route exists.

Related: [../C05-modules/lms_course.md](../C05-modules/lms_course.md) · [../C07-sitemap.md](../C07-sitemap.md)
