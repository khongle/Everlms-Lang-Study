# Wireframe: Course Listing

## Layout

```
┌─────────────────────────────────────────────────────────────────────┐
│ HEADER (mega-menu)                                                   │
├─────────────────────────────────────────────────────────────────────┤
│ PAGE HEADER (py-40)                                                  │
│  "Courses"                                                           │
│  Breadcrumb: Home > Courses                                          │
│                                                                      │
│  [Search bar _________________________ 🔍]   Total: 248 courses     │
├──────────────────┬──────────────────────────────────────────────────┤
│ FILTERS (col-3)  │ COURSE GRID (col-9)                              │
│                  │                                                   │
│ Category         │ Sort: [Newest ▼] [Popular] [Price ↑↓] [Rating]   │
│ ☑ Programming   │ View: [▦ Grid] [≡ List]                          │
│ ☑ Design        │                                                   │
│ ☐ Business      │ ┌──────────┐ ┌──────────┐ ┌──────────┐           │
│ ☐ Marketing     │ │Thumbnail │ │Thumbnail │ │Thumbnail │           │
│                  │ │BESTSELLER│ │          │ │   FREE   │           │
│ Level            │ │          │ │          │ │          │           │
│ ☑ Beginner      │ │Title     │ │Title     │ │Title     │           │
│ ☐ Intermediate  │ │👤 Author │ │👤 Author │ │👤 Author │           │
│ ☐ Advanced      │ │★★★★☆ 4.5│ │★★★★★ 4.9│ │★★★★☆ 4.2│           │
│                  │ │⏱12h │👥234│ │⏱8h │👥567│ │⏱6h │👥123│         │
│ Price            │ │$49.99    │ │$29.99    │ │FREE      │           │
│ ○ All           │ └──────────┘ └──────────┘ └──────────┘           │
│ ○ Free          │                                                   │
│ ○ Paid          │ ┌──────────┐ ┌──────────┐ ┌──────────┐           │
│ [$0 ──●── $200] │ │ ...      │ │ ...      │ │ ...      │           │
│                  │ └──────────┘ └──────────┘ └──────────┘           │
│ Language         │                                                   │
│ ☑ English       │ Pagination: [< 1 2 3 ... 12 >]                   │
│ ☐ Vietnamese    │                                                   │
│                  │                                                   │
│ Rating           │                                                   │
│ ☐ ★★★★☆ & up   │                                                   │
│ ☐ ★★★☆☆ & up   │                                                   │
│                  │                                                   │
│ [Clear Filters]  │                                                   │
├──────────────────┴──────────────────────────────────────────────────┤
│ FOOTER                                                               │
└─────────────────────────────────────────────────────────────────────┘
```

## Course Card Component

```
┌──────────────────────┐
│ [Thumbnail Image]    │ 280x180px
│ [BESTSELLER] badge   │ top-left overlay (optional)
├──────────────────────┤
│ Course Title         │ max 2 lines, truncate
│ 👤 Instructor Name  │ 
│ ★★★★☆ 4.5 (234)    │ star rating + count
│ ⏱ 12 hours │ 📚 24  │ duration + lesson count
├──────────────────────┤
│ $49.99  ~~$99.99~~  │ price + original price
└──────────────────────┘
```

## Responsive

| Breakpoint | Filters | Grid |
|-----------|---------|------|
| ≥1200px | Left sidebar (col-3) | 3 columns |
| 992-1199px | Left sidebar (col-3) | 2 columns |
| 768-991px | Collapsible top panel | 2 columns |
| <768px | Full-width modal filter | 1 column |

---

## Implementation status (as of 2026-06-20)

**as-built.** Route `/courses` → `CoursePublicController::catalog` → `lms-catalog.html.twig`. Route `/categories` → `CoursePublicController::categories` → `lms-categories.html.twig`. Category detail page at `/categories/{taxonomy_term}`. AJAX search API at `/api/courses/search` (`CoursePublicController::searchApi`). The depicted filter sidebar, course card grid, and search are implemented. Course cards use the reusable `lms-course-card.html.twig` sub-component. Cart/wishlist heart button toggles via `/api/lms/wishlist/toggle` (lms_wishlist module).

Related: [../C04-entities/01-content-entities.md](../C04-entities/01-content-entities.md) · [../C05-modules/lms_course.md](../C05-modules/lms_course.md)
