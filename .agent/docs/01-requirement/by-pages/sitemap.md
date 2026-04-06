# EverLMS — Page Sitemap

## 1. Public Pages

| # | Page | Route | Template | Mô tả |
|---|------|-------|----------|-------|
| PU-01 | Homepage | `/` | `page--front` | Hero, featured courses, categories, testimonials, progress (logged in) |
| PU-02 | Course Listing | `/courses` | `page--courses` | Grid/List, faceted search, filters, pagination |
| PU-03 | Course Detail | `/courses/{slug}` | `page--course-detail` | Banner, description, curriculum, instructor, reviews, pricing |
| PU-04 | Category Listing | `/categories` | `page--categories` | Category grid |
| PU-05 | Category Detail | `/categories/{slug}` | `page--category` | Courses in category |
| PU-06 | Instructor Profile | `/instructors/{slug}` | `page--instructor` | Bio, courses, rating |
| PU-07 | Pricing / Plans | `/pricing` | `page--pricing` | Membership plan comparison |
| PU-08 | Blog Listing | `/blog` | `page--blog` | Blog articles |
| PU-09 | Blog Detail | `/blog/{slug}` | `page--blog-detail` | Blog article |
| PU-10 | Contact | `/contact` | `page--contact` | Webform contact form |
| PU-11 | FAQ | `/faq` | `page--faq` | Accordion FAQ |
| PU-12 | About Us | `/about` | `page--about` | Static content |
| PU-13 | Terms of Service | `/terms` | `page--terms` | Legal page |
| PU-14 | Privacy Policy | `/privacy` | `page--privacy` | Legal page |
| PU-15 | Certificate Verify | `/verify/{certificate_id}` | `page--verify` | Public certificate verification |
| PU-16 | Search Results | `/search` | `page--search` | Global search results |
| PU-17 | Login | `/user/login` | `page--user-login` | Login form |
| PU-18 | Register | `/user/register` | `page--user-register` | Registration form |
| PU-19 | Forgot Password | `/user/password` | `page--user-password` | Password reset form |

---

## 2. User Portal Pages

| # | Page | Route | Template | Mô tả |
|---|------|-------|----------|-------|
| UP-01 | My Courses | `/my-courses` | `page--my-courses` | Enrolled courses + progress |
| UP-02 | Course Learning | `/learn/{course_slug}` | `page--learn` | Course content with sidebar nav |
| UP-03 | Lesson View | `/learn/{course}/{lesson}` | `page--lesson` | Lesson content (video/text/H5P/SCORM) |
| UP-04 | Take Quiz | `/take-quiz/{quiz_id}` | `page--take-quiz` | Quiz taking interface |
| UP-05 | Quiz Result | `/take-quiz/result/{result_id}` | `page--quiz-result` | Score, answers review |
| UP-06 | My Profile | `/user/{uid}` | `page--user-profile` | Profile view |
| UP-07 | Edit Profile | `/user/{uid}/edit` | `page--user-edit` | Edit profile form |
| UP-08 | My Certificates | `/my-certificates` | `page--my-certificates` | Certificate listing |
| UP-09 | Certificate View | `/my-certificates/{id}` | `page--certificate` | Certificate detail + download |
| UP-10 | My Orders | `/my-orders` | `page--my-orders` | Order history |
| UP-11 | Order Detail | `/my-orders/{order_id}` | `page--order-detail` | Order detail |
| UP-12 | My Membership | `/my-membership` | `page--my-membership` | Current membership + manage |
| UP-13 | Checkout | `/checkout` | `page--checkout` | Cart → Payment flow |
| UP-14 | Payment Success | `/checkout/success` | `page--payment-success` | Confirmation |
| UP-15 | Notifications | `/notifications` | `page--notifications` | Notification listing |
| UP-16 | My Wishlist | `/my-wishlist` | `page--wishlist` | Saved courses |
| UP-17 | Forum | `/forum` | `page--forum` | Forum listing |
| UP-18 | Forum Topic | `/forum/{topic_id}` | `page--forum-topic` | Topic detail + replies |
| UP-19 | Zoom Sessions | `/my-sessions` | `page--sessions` | Upcoming live sessions |

---

## 3. Management Pages

### Dashboard
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-01 | Admin Dashboard | `/manage` | KPIs, recent activities, charts |
| MG-02 | Instructor Dashboard | `/manage/instructor` | My course stats, grading queue |

### Course Management
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-10 | Course List | `/manage/courses` | Filterable course list |
| MG-11 | Create Course | `/manage/courses/create` | Course creation form |
| MG-12 | Edit Course | `/manage/courses/{id}/edit` | Course edit + chapter/lesson management |
| MG-13 | Course Analytics | `/manage/courses/{id}/analytics` | Course-level stats |
| MG-14 | Category List | `/manage/categories` | Category management |
| MG-15 | Category Create/Edit | `/manage/categories/create` | Category form |

### Assessment Management
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-20 | Quiz List | `/manage/quizzes` | All quizzes |
| MG-21 | Create Quiz | `/manage/quizzes/create` | Quiz creation form |
| MG-22 | Edit Quiz | `/manage/quizzes/{id}/edit` | Quiz edit + questions |
| MG-23 | Question Bank | `/manage/questions` | All questions |
| MG-24 | Create Question | `/manage/questions/create/{type}` | Question creation per type |
| MG-25 | Quiz Results | `/manage/quiz-results` | All results listing |
| MG-26 | Result Detail | `/manage/quiz-results/{id}` | Detailed result + grading |
| MG-27 | Grading Queue | `/manage/grading` | Essay questions pending review |

### User Management
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-30 | User List | `/manage/users` | All users |
| MG-31 | Create User | `/manage/users/create` | User creation form |
| MG-32 | Edit User | `/manage/users/{uid}/edit` | User edit |
| MG-33 | User Detail | `/manage/users/{uid}` | User profile + activity |
| MG-34 | Enrollment List | `/manage/enrollments` | All enrollments |

### Commerce Management
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-40 | Order List | `/manage/orders` | All orders |
| MG-41 | Order Detail | `/manage/orders/{id}` | Order detail + actions |
| MG-42 | Membership Plans | `/manage/membership-plans` | Plan management |
| MG-43 | Create Plan | `/manage/membership-plans/create` | Plan form |
| MG-44 | Active Memberships | `/manage/memberships` | User memberships |
| MG-45 | Coupon List | `/manage/coupons` | Coupon management |
| MG-46 | Create Coupon | `/manage/coupons/create` | Coupon form |
| MG-47 | Payment List | `/manage/payments` | Transaction history |

### Certificate Management
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-50 | Certificate Templates | `/manage/certificate-templates` | Template listing |
| MG-51 | Create Template | `/manage/certificate-templates/create` | Template builder |
| MG-52 | Edit Template | `/manage/certificate-templates/{id}/edit` | Template edit |
| MG-53 | Issued Certificates | `/manage/certificates` | All issued certs |

### Reports
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-60 | Report Dashboard | `/manage/reports` | Overview charts |
| MG-61 | Course Report | `/manage/reports/courses` | Per-course stats |
| MG-62 | User Report | `/manage/reports/users` | Per-user stats |
| MG-63 | Revenue Report | `/manage/reports/revenue` | Financial reports |
| MG-64 | Quiz Report | `/manage/reports/quizzes` | Assessment analytics |

### Settings (Admin only)
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-70 | General Settings | `/manage/settings` | Site config |
| MG-71 | Payment Settings | `/manage/settings/payment` | Gateway config |
| MG-72 | Email Settings | `/manage/settings/emails` | Email templates |
| MG-73 | Notification Settings | `/manage/settings/notifications` | Notification config |
| MG-74 | Integration Settings | `/manage/settings/integrations` | Zoom, GA, etc. |

---

## 4. Page Count Summary

| Section | Count |
|---------|-------|
| Public Pages | 19 |
| User Portal Pages | 19 |
| Management Pages | ~35 |
| **Total** | **~73** |
