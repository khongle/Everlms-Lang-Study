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
