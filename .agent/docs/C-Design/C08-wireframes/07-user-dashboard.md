# Wireframe: User Dashboard

## My Courses Page

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER (mega-menu, logged in)                                        │
├─────────────────────────────────────────────────────────────────────┤
│ PAGE HEADER                                                          │
│ "My Learning"                                                        │
│ [All] [In Progress (5)] [Completed (12)] [Wishlist (3)]             │
├─────────────────────────────────────────────────────────────────────┤
│ CONTINUE LEARNING (hero card)                                        │
│ ┌───────────────────────────────────────────────────────────────┐   │
│ │ [Thumbnail]  "Web Development Bootcamp"                       │   │
│ │              Chapter 3: CSS Flexbox │ Lesson 3.2              │   │
│ │              Progress: ████████░░ 65%                         │   │
│ │              [Continue Learning →]                             │   │
│ └───────────────────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────────────────┤
│ ENROLLED COURSES GRID                                                │
│                                                                      │
│ ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐        │
│ │ Thumbnail  │ │ Thumbnail  │ │ Thumbnail  │ │ Thumbnail  │        │
│ │ Title      │ │ Title      │ │ Title      │ │ Title      │        │
│ │ Progress:  │ │ Progress:  │ │ COMPLETED  │ │ Progress:  │        │
│ │ ████░░ 40% │ │ ██░░░░ 20% │ │ ██████ 100%│ │ ███░░░ 30% │        │
│ │ [Continue] │ │ [Continue] │ │ [Review]   │ │ [Continue] │        │
│ └────────────┘ └────────────┘ └────────────┘ └────────────┘        │
├─────────────────────────────────────────────────────────────────────┤
│ FOOTER                                                               │
└─────────────────────────────────────────────────────────────────────┘
```

## Notifications Page

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER                                                               │
├─────────────────────────────────────────────────────────────────────┤
│ "Notifications"                        [Mark all as Read]            │
│ [All (8)] [Unread (3)] [Courses] [Quizzes] [System]                 │
├─────────────────────────────────────────────────────────────────────┤
│ ┌───────────────────────────────────────────────────────────────┐   │
│ │ 🏆 You passed Quiz: HTML Basics with 90%       2 hours ago  ●│   │
│ ├───────────────────────────────────────────────────────────────┤   │
│ │ 📚 New lesson "JS Closures" added              Yesterday     │   │
│ ├───────────────────────────────────────────────────────────────┤   │
│ │ 📜 Your certificate is ready for download     2 days ago     │   │
│ ├───────────────────────────────────────────────────────────────┤   │
│ │ ⚠️ Membership expires in 7 days              3 days ago     │   │
│ └───────────────────────────────────────────────────────────────┘   │
│ Pagination                                                           │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Implementation status (as of 2026-06-20)

**as-built.**

- Dashboard: `/dashboard` → `UserPortalController::dashboard` → `lms-dashboard.html.twig`. Renders stats (enrolled/in_progress/completed/certificates) and "continue learning" link to next incomplete lesson.
- My Courses: `/my-courses` → `UserPortalController::myCourses` → `lms-my-courses.html.twig`. Tabs (All / In Progress / Completed) and per-course progress bars are rendered.
- Notifications page: `/notifications` → `NotificationController::listNotifications` → `lms-notifications.html.twig`. Pager(15), type filter, mark-read on visit. In-app notifications are fully implemented. **Email and push notification channels are stubs** — only in-app delivery works.
- Notification bell: `lms_notification_bell_block` block plugin (rendered in header); AJAX unread count from `/api/lms/notifications/unread-count`.

Related: [../C04-entities/03-progress-entities.md](../C04-entities/03-progress-entities.md) · [../C05-modules/lms_progress.md](../C05-modules/lms_progress.md)
