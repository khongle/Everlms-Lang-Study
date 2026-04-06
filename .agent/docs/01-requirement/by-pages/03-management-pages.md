# Management Pages (~40)

> Admin / Manager / Instructor dashboards and CRUD pages.

## Dashboard
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-01 | Admin Dashboard | `/manage` | KPIs, recent activities, charts |
| MG-02 | Instructor Dashboard | `/manage/instructor` | My course stats, grading queue |

## Course Management
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-10 | Course List | `/manage/courses` | Filterable course table |
| MG-11 | Create Course | `/manage/courses/create` | Multi-step course creation form |
| MG-12 | Edit Course | `/manage/courses/{id}/edit` | Course edit + chapter/lesson management |
| MG-13 | Course Analytics | `/manage/courses/{id}/analytics` | Course-level enrollment & revenue stats |
| MG-14 | Category List | `/manage/categories` | Category management table |
| MG-15 | Category Create/Edit | `/manage/categories/create` | Category form |

## Assessment Management
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-20 | Quiz List | `/manage/quizzes` | All quizzes table |
| MG-21 | Create Quiz | `/manage/quizzes/create` | Quiz creation form |
| MG-22 | Edit Quiz | `/manage/quizzes/{id}/edit` | Quiz edit + question management |
| MG-23 | Question Bank | `/manage/questions` | All questions searchable |
| MG-24 | Create Question | `/manage/questions/create/{type}` | Question creation per type |
| MG-25 | Quiz Results | `/manage/quiz-results` | All results listing |
| MG-26 | Result Detail | `/manage/quiz-results/{id}` | Detailed result + manual grading |
| MG-27 | Grading Queue | `/manage/grading` | Essay questions pending review |

## User Management
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-30 | User List | `/manage/users` | All users table |
| MG-31 | Create User | `/manage/users/create` | User creation form |
| MG-32 | Edit User | `/manage/users/{uid}/edit` | User edit |
| MG-33 | User Detail | `/manage/users/{uid}` | User profile + activity log |
| MG-34 | Enrollment List | `/manage/enrollments` | All enrollments table |

## Commerce Management
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-40 | Order List | `/manage/orders` | All orders table |
| MG-41 | Order Detail | `/manage/orders/{id}` | Order detail + actions (refund, cancel) |
| MG-42 | Membership Plans | `/manage/membership-plans` | Plan management |
| MG-43 | Create Plan | `/manage/membership-plans/create` | Plan form |
| MG-44 | Active Memberships | `/manage/memberships` | User membership table |
| MG-45 | Coupon List | `/manage/coupons` | Coupon management |
| MG-46 | Create Coupon | `/manage/coupons/create` | Coupon form |
| MG-47 | Payment History | `/manage/payments` | Transaction history |

## Certificate Management
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-50 | Certificate Templates | `/manage/certificate-templates` | Template listing |
| MG-51 | Create Template | `/manage/certificate-templates/create` | Template builder |
| MG-52 | Edit Template | `/manage/certificate-templates/{id}/edit` | Template edit |
| MG-53 | Issued Certificates | `/manage/certificates` | All issued certs |

## Reports
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-60 | Report Dashboard | `/manage/reports` | Overview charts + KPIs |
| MG-61 | Course Report | `/manage/reports/courses` | Per-course enrollment & revenue |
| MG-62 | User Report | `/manage/reports/users` | Per-user activity & progress |
| MG-63 | Revenue Report | `/manage/reports/revenue` | Financial reports with date range |
| MG-64 | Quiz Report | `/manage/reports/quizzes` | Assessment analytics |

## Settings (Admin only)
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-70 | General Settings | `/manage/settings` | Site name, logo, currency, timezone |
| MG-71 | Payment Settings | `/manage/settings/payment` | Stripe/PayPal API keys, test mode |
| MG-72 | Email Settings | `/manage/settings/emails` | Email templates (SMTP config) |
| MG-73 | Notification Settings | `/manage/settings/notifications` | Enable/disable notification types |
| MG-74 | Integration Settings | `/manage/settings/integrations` | Zoom API, GA4, external services |

## Bundle Management
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-80 | Bundle List | `/manage/bundles` | All bundles table |
| MG-81 | Create Bundle | `/manage/bundles/create` | Bundle form (select courses, set price) |
| MG-82 | Edit Bundle | `/manage/bundles/{id}/edit` | Bundle edit |

## Email Campaigns
| # | Page | Route | Mô tả |
|---|------|-------|-------|
| MG-85 | Email Campaigns | `/manage/email-campaigns` | Campaign listing + stats |
| MG-86 | Create Campaign | `/manage/email-campaigns/create` | WYSIWYG email template editor |
| MG-87 | Campaign Stats | `/manage/email-campaigns/{id}` | Sent/opened/clicked stats |
| MG-88 | Cart Recovery | `/manage/cart-recovery` | Abandoned cart stats + settings |
