# EverLMS — Wireframes

> Text-based wireframes describing the layout for ~80+ pages.
> For the live interactive HTML prototype see [../C10-prototypes/html/index.html](../C10-prototypes/html/index.html).

## Design Rules

> [!IMPORTANT]
> - **Navigation:** Top horizontal navbar + mega-menu dropdown. No left sidebar.
> - **Theme:** Dark premium, blue/purple gradient accents, glassmorphism elements
> - **Responsive:** Desktop-first, breakpoints: 1200px / 992px / 768px / 576px
> - **Grid:** Bootstrap 5.3.3 (12-column), loaded via CDN

## Related canonical documents

| Doc | Purpose |
|-----|---------|
| [../C04-entities/01-content-entities.md](../C04-entities/01-content-entities.md) | Course, Chapter, Lesson entity schemas |
| [../C04-entities/03-progress-entities.md](../C04-entities/03-progress-entities.md) | UserCourse, UserLesson progress entities |
| [../C04-entities/04-commerce-entities.md](../C04-entities/04-commerce-entities.md) | Order, Membership entities |
| [../C04-entities/05-certificate-entities.md](../C04-entities/05-certificate-entities.md) | CertificateTemplate, UserCertificate entities |
| [../C04-entities/07-communication-entities.md](../C04-entities/07-communication-entities.md) | Forum, Rating, Notification entities |
| [../C05-modules/lms_course.md](../C05-modules/lms_course.md) | Course module — public pages, workspace |
| [../C05-modules/lms_progress.md](../C05-modules/lms_progress.md) | Progress tracking module |
| [../C05-modules/lms_quiz.md](../C05-modules/lms_quiz.md) | Quiz module |
| [../C05-modules/lms_order.md](../C05-modules/lms_order.md) | Order / checkout module |
| [../C07-sitemap.md](../C07-sitemap.md) | Full route/URL map (137 routes) |

## Wireframe Index

### Public Pages
| # | File | Pages | Routes |
|---|------|-------|--------|
| 01 | [Homepage](./01-homepage.md) | Landing page | `/homepage` |
| 02 | [Course Listing](./02-course-listing.md) | Course catalog + filters | `/courses`, `/categories` |
| 03 | [Course Detail](./03-course-detail.md) | Course detail + enrollment | `/courses/{id}` |
| 19 | [Public Misc](./19-public-misc.md) | Pricing, About, Blog, FAQ, Contact | `/pricing`, `/about`, `/news`, `/contact` |

### Learning Experience
| # | File | Pages | Routes |
|---|------|-------|--------|
| 04 | [Lesson Views](./04-lesson-views.md) | Video, Text, H5P, SCORM, Zoom | `/learn/{course}/{lesson}` |
| 05 | [Quiz Flow](./05-quiz-flow.md) | Quiz taking + results | `/quiz/{id}/take`, `/quiz-result/{id}` |

### Auth & Account
| # | File | Pages | Routes |
|---|------|-------|--------|
| 06 | [Auth Pages](./06-auth-pages.md) | Login, Register, Forgot PW | Drupal core `/user/login`, `/user/register` |
| 07 | [User Dashboard](./07-user-dashboard.md) | My Courses, Notifications | `/dashboard`, `/my-courses`, `/notifications` |
| 08 | [User Account](./08-user-account.md) | Profile, Orders, Certificates | `/profile`, `/my-certificates` |
| 09 | [Checkout](./09-checkout.md) | Cart → Payment → Success | `/checkout/{course_id}`, `/checkout/bank-transfer/{order}` |

### Community & Learning
| # | File | Pages | Routes |
|---|------|-------|--------|
| 10 | [Community](./10-community.md) | Forum | `/forum`, `/forum/{topic}` |

### Management
| # | File | Pages | Routes |
|---|------|-------|--------|
| 11 | [Admin Dashboard](./11-admin-dashboard.md) | Admin + Instructor dashboards | `/workspace` |
| 12 | [Admin Courses](./12-admin-courses.md) | Course CRUD, Categories | `/workspace/courses`, `/workspace/courses/create` |
| 13 | [Admin Assessments](./13-admin-assessments.md) | Quiz CRUD, Question Bank, Grading | `/workspace/quizzes` |
| 14 | [Admin Users/Commerce/Reports/Settings](./14-admin-users-commerce-reports-settings.md) | Users, Orders, Certs, Reports, Settings | `/workspace/users`, `/workspace/orders`, `/workspace/certificates`, `/workspace/reports` |

### Enhancements
| # | File | Pages | Routes |
|---|------|-------|--------|
| 15 | [Essential Enhancements](./15-enhancements.md) | Wishlist, Notes | `/my-wishlist`, `/my-notes` |

### Mobile App
| # | File | Pages | Routes |
|---|------|-------|--------|
| 16 | [Mobile App](./16-mobile-app.md) | 33 Flutter screens: Auth, Home, Explore, Course, Lesson, Quiz, Profile, Checkout | `/api/v1/*` (backend) |
