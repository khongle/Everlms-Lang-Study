# C07 — Sitemap

> ~84 pages, 3 groups (Public / Student Portal / Management).

---

## URL Structure

### Public Pages (21)

| URL | Page | Access |
|-----|------|--------|
| `/` | Homepage | Anonymous |
| `/courses` | Course catalog | Anonymous |
| `/courses/{slug}` | Course detail | Anonymous |
| `/categories` | Category listing | Anonymous |
| `/categories/{slug}` | Category detail | Anonymous |
| `/pricing` | Pricing / Membership plans | Anonymous |
| `/about` | About page | Anonymous |
| `/blog` | Blog listing | Anonymous |
| `/blog/{slug}` | Blog detail | Anonymous |
| `/faq` | FAQ page | Anonymous |
| `/forum` | Forum listing | Anonymous |
| `/forum/{id}` | Forum topic detail | Anonymous |
| `/certificate/verify/{code}` | Certificate verification | Anonymous |
| `/login` | Login page | Anonymous |
| `/register` | Registration page | Anonymous |
| `/forgot-password` | Password reset | Anonymous |
| `/learning-paths` | Learning path listing | Anonymous |
| `/learning-paths/{slug}` | Path detail | Anonymous |
| `/instructors` | Instructor listing | Anonymous |
| `/instructors/{id}` | Instructor profile | Anonymous |
| `/search` | Search results | Anonymous |

### Student Portal (23)

| URL | Page | Access |
|-----|------|--------|
| `/my-learning` | Dashboard (progress overview) | Student+ |
| `/my-courses` | Enrolled courses | Student+ |
| `/lesson/{id}` | Lesson view (video/text/h5p...) | Student+ |
| `/quiz/{id}` | Quiz taking | Student+ |
| `/quiz/{id}/result` | Quiz result | Student+ |
| `/quiz/{id}/result/{result_id}` | Quiz result detail | Student+ |
| `/my-quiz-results` | All quiz results | Student+ |
| `/orders` | Order history | Student+ |
| `/orders/{id}` | Order detail | Student+ |
| `/certificates` | My certificates | Student+ |
| `/certificates/{id}` | Certificate detail | Student+ |
| `/profile` | Profile page | Authenticated |
| `/profile/edit` | Edit profile | Authenticated |
| `/wishlist` | Wishlist | Student+ |
| `/notes` | My notes | Student+ |
| `/notifications` | Notifications | Student+ |
| `/membership` | My membership | Student+ |
| `/checkout` | Checkout page | Student+ |
| `/checkout/success` | Payment success | Student+ |
| `/checkout/cancel` | Payment cancelled | Student+ |
| `/forum/new` | Create forum topic | Student+ |
| `/badges` | My badges | Student+ |
| `/learning-paths/my` | My learning paths | Student+ |

### Management (40+)

| URL | Page | Access |
|-----|------|--------|
| `/manage` | Management dashboard | Instructor+ |
| `/manage/courses` | Course management list | Instructor+ |
| `/manage/courses/add` | Create course | Instructor+ |
| `/manage/courses/{id}/edit` | Edit course | Instructor+ |
| `/manage/courses/{id}/chapters` | Manage chapters | Instructor+ |
| `/manage/courses/{id}/lessons` | Manage lessons | Instructor+ |
| `/manage/quizzes` | Quiz management | Instructor+ |
| `/manage/quizzes/add` | Create quiz | Instructor+ |
| `/manage/quizzes/{id}/edit` | Edit quiz | Instructor+ |
| `/manage/quizzes/{id}/questions` | Manage questions | Instructor+ |
| `/manage/grading` | Pending essay grading | Instructor+ |
| `/manage/students` | Student list (own courses) | Instructor+ |
| `/manage/reports/courses` | Course reports | Instructor+ |
| `/manage/reports/students` | Student reports | Manager+ |
| `/manage/reports/quizzes` | Quiz reports | Manager+ |
| `/manage/reports/revenue` | Revenue reports | Manager+ |
| `/manage/users` | User management | Manager+ |
| `/manage/users/add` | Create user | Manager+ |
| `/manage/users/{id}/edit` | Edit user | Manager+ |
| `/manage/orders` | Order management | Manager+ |
| `/manage/orders/{id}` | Order detail (admin) | Manager+ |
| `/manage/memberships` | Membership management | Manager+ |
| `/manage/coupons` | Coupon management | Manager+ |
| `/manage/categories` | Category management | Manager+ |
| `/manage/certificate-templates` | Certificate templates | Manager+ |
| `/manage/certificate-templates/add` | Create template | Manager+ |
| `/manage/badges` | Badge management | Manager+ |
| `/manage/forum` | Forum moderation | Manager+ |
| `/manage/notifications/send` | Send notification | Manager+ |
| `/manage/settings` | System settings | Admin |
| `/manage/settings/payment` | Payment config | Admin |
| `/manage/settings/zoom` | Zoom config | Admin |
| `/manage/settings/ai` | AI provider config | Admin |
| `/manage/settings/email` | Email config | Admin |
| `/manage/analytics` | System analytics | Admin |

## Navigation Rules

- Menu hiển thị theo role matrix.
- Breadcrumb nhất quán theo parent route.
- Deep link hỗ trợ cho learner journey.
- API routes tách riêng với page routes: `/api/v1/...`

## SEO

- Public pages có metadata + canonical URL (via `drupal/metatag`).
- Course detail có schema markup (Course schema).
- URL alias via `drupal/pathauto`.

---

*End of C07-sitemap.md*
